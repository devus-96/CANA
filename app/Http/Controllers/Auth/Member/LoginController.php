<?php

namespace App\Http\Controllers\Auth\Menber;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Cookie\CookieJar;
use Illuminate\Validation\Factory;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Controller;
use App\Models\Menber;
use App\Models\RefreshToken;
use App\Services\JWTService;


class LoginController extends Controller
{
    public function store(Request $request, Validator $validator, CookieJar $cookie, ResponseFactory $response): JsonResponse
    {
        $validator = $validator->make($request->all(), [
            'email' => 'required|string|lowercase|email|max:255|unique:'.Menber::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $response->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // get Menber from phone
        $menber = Menber::where("email", "=", $request->email)->first();

        if ($menber) {

            if ($menber->verify_at) {

                if (! $menber || ! Hash::check($request->password, $menber->password)) {
                    return response()->json(['message' => 'Invalid credentials'], 401);
                }

                 // 2. Générer le JWT
                $token = JWTService::generate([
                    "id" => $menber->id,
                ], 3600);

                // 3. Générer le refresh token
                [$secret, $tokenHash] = Controller::generateOpaqueToken();

                $refreshToken = RefreshToken::query()->create([
                    'Menber_id' => $menber->id,
                    'role' => Controller::Menber_ROLE_MEMBERS,
                    'token' => $tokenHash,
                    'expires_at' => now()->addDays(30)
                ]);

                $refreshCookie = $cookie->make(
                    'refresh_token',
                    $refreshToken->id . '.' . $secret,
                    60 * 24 * 30, // Durée de 30 jours
                    '/',
                    null,
                    true, // Secure (nécessite HTTPS)
                    true  // HttpOnly (empêche JS d'y accéder)
                );

                // 4. Retourner JSON (pas de redirection)
                return $response->json([
                    'status' => 'success',
                    'Menber' => $menber,
                    'token' => $token
                ])->withCookie($refreshCookie);

            } else {

                $emailToken = TokenService::generate([
                     'id' => $menber->id
                ]);

                $menber->link = url('/verify/email?token='.$emailToken);

                Mail::to($menber->email)->send(new AccountCreated($menber));

                return response()->json(['message' => 'Verification email resent.']);

            }

        } else {
            return response()->json(["data" => "0"], 404);
        }
    }
}
