<?php

namespace App\Http\Controllers\Auth\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use Inertia\Inertia;
use Inertia\Response;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\JWTService;
use App\Mail\AccountCreated;


class LoginMemberController extends Controller
{
     /*
        Show the login page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/login');
    }

    public function store (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|lowercase|email|max:255',
            'password' => ['required', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // get Menber from phone
        $member = Member::where("email", "=", $request->email)->first();

        if ($member) {

            if ($member->is_verified) {

                if (! $member || ! Hash::check($request->password, $member->password)) {
                    return response()->json(['message' => 'Invalid credentials'], 401);
                }

                 // 2. Générer le JWT
                $token = JWTService::generate([
                    "id" => $member->id,
                ], 3600);

                // 3. Générer le refresh token
                [$secret, $tokenHash] = Controller::generateOpaqueToken();

                $refreshToken = $member->refresh_token()->create([
                    'token' => $tokenHash,
                    'expired_at' => now()->addDays(7)
                ]);


                $refreshCookie = Cookie::make(
                    'refresh_token',
                    $refreshToken->id . '.' . $secret,
                    60 * 24 * 7, // Durée de 7 jours
                    '/',
                    null,
                    true, // Secure (nécessite HTTPS)
                    true  // HttpOnly (empêche JS d'y accéder)
                );

                // 4. Retourner JSON (pas de redirection)
                 return redirect()->route('home')
                    ->with('userData', [
                        'status' => 'success',
                        'user' => $member,
                        'token' => $token
                    ])
                    ->withCookie($refreshCookie);

            } else {

                $emailToken = JWTService::generate([
                     'id' => $member->id
                ]);

                $member->link = url(route('member.emailVerify', ['token' => $emailToken]));

                Mail::to($member->email)->send(new AccountCreated($member));

                return response()->json(['message' => 'Verification email resent.']);

            }

        } else {
            return response()->json(["data" => "0"], 404);
        }
    }
}
