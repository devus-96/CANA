<?php

namespace App\Http\Controllers\Auth\Member;


use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\JWTService;
use Illuminate\Support\Facades\Cookie;

use App\Models\Member;
use App\Models\RefreshToken;

class CheckVerification extends Controller
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

        $member = Member::find($data->id); // Fixed typo: it was "tokenabled_id"

        if (!$member) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/auth/login?t=e&m=User not found");
        }

        $member->is_verified = true;
        $member->save();

        // 2. Générer le JWT
        $token = JWTService::generate([
            "id" => $member->id,
        ], 60*60);

        // 3. Générer le refresh token
        [$secret, $tokenHash] = Controller::generateOpaqueToken();

        $refreshToken = $member->refresh_token()->create([
            'token' => $tokenHash,
            'expired_at' => now()->addDays(7)
        ]);

        $refreshCookie = Cookie::make(
            'refresh_token',
            $refreshToken->id . '.' . $secret,
            60 * 24 * 30, // Durée de 30 jours
            '/',
            null,
            false, // Secure (nécessite HTTPS)
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

    }
}
