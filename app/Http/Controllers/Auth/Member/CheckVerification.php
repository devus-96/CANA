<?php

namespace App\Http\Controllers\Auth\Menber;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\JWTService;
use Illuminate\Support\Facades\Cookie;

use App\Models\Menber;
use App\Models\RefreshToken;
use App\Models\Role;

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

        $menber = Menber::find($data->id); // Fixed typo: it was "tokenabled_id"

        if (!$menber) {
            return redirect()->to(env('WEB_CLIENT_URL') . "/auth/login?t=e&m=User not found");
        }

        $admin->is_verified = true;
        $admin->save();

        // 2. Générer le JWT
        $token = JWTService::generate([
            "id" => $menber->id,
        ], 60*60);

        // 3. Générer le refresh token
        [$secret, $tokenHash] = Controller::generateOpaqueToken();

        $refreshToken = RefreshToken::create([
            'menber_id' => $menber->id,
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
        return response()->json([
            'status' => 'success',
            'user' => $menber,
            'token' => $token
        ], 200 )->withCookie($refreshCookie);

    }
}
