<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\JWTService;

class VerifyEmail extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/auth/login?t=e&m=Verification link has expired");
        }

        $data = JWTService::decode($token);

        if (!$data) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/auth/login?t=e&m=Verification link has expired");
        }

        $user = User::find($data->id); // Fixed typo: it was "tokenabled_id"

        if (!$user) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/auth/login?t=e&m=User not found");
        }

        $user->verified_at = now();
        $user->email_verified_at = now();
        $user->save();

        // 2. Générer le JWT
        $token = JWTService::generate([
            "id" => $user->id,
        ], 60*60);

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

    }
}
