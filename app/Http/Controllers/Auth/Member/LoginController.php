<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Services\JWTService;
use Illuminate\Cookie\CookieJar;
use Illuminate\Validation\Factory;
use Illuminate\Contracts\Routing\ResponseFactory;

class LoginController extends Controller
{
    public function store(Request $request, Validator $validator, CookieJar $cookie, ResponseFactory $response): JsonResponse
    {
        // get user from phone
        $user = User::where("email", "=", $request->email)->first();

        if ($user) {

            if ($user->verify_at) {

                $validator = $validator->make($request->all(), [
                    'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);

                if ($validator->fails()) {
                    return $response->json([
                        'statut' => 'error',
                        'message' => $validator->errors(),
                    ], 422);
                }

                 // 2. Générer le JWT
                $token = JWTService::generate([
                    "id" => $user->id,
                ], 3600);

                // 3. Générer le refresh token
                [$secret, $tokenHash] = Controller::generateOpaqueToken();

                $refreshToken = RefreshToken::query()->create([
                    'user_id' => $user->id,
                    'token' => $tokenHash,
                    'expires_at' => now()->addDays(30)
                ]);

                $refreshCookie = $cookie->make(
                    'refresh_token',
                    $refresh_token->id . '.' . $secret,
                    60 * 24 * 30, // Durée de 30 jours
                    '/',
                    null,
                    true, // Secure (nécessite HTTPS)
                    true  // HttpOnly (empêche JS d'y accéder)
                );

                // 4. Retourner JSON (pas de redirection)
                return $response->json([
                    'status' => 'success',
                    'user' => $user,
                    'token' => $token
                ])->withCookie($refreshCookie);

            } else {

                $emailToken = TokenService::generate([
                     'id' => $user->id
                ]);

                $user->link = url('/verify/email?token='.$emailToken);

                Mail::to($user->email)->send(new AccountCreated($user));

                return response()->json(['message' => 'Verification email resent.']);


            }

        } else {
            return response()->json(["data" => "0"], 404);
        }
    }
}
