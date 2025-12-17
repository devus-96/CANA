<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Models\RefreshToken;
use App\Http\Controllers\Controller;
use App\Models\VoteValidationAdmin;

use Illuminate\Cookie\CookieJar;

class AdminRepository
{
    public function __construct(Admin $model)
    {
        $this->model = $model;
    }

    public function connectAdmint (CookieJar $cookie)
    {
        // create token
        // 3. Générer le refresh token
        [$secret, $tokenHash] = Controller::generateOpaqueToken();

        $refreshToken = RefreshToken::query()->create([
            'user_id' => $user->id,
            'role' => Controller::USER_ROLE_ADMIN,
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
    }

        public function CheckAdminBootstrapLimit ($limit) {
            $count = Admin::count();
            if ($count < $limit) {
                $this->model->status = 'BOOTSTRAP';
            } else {
                $this->model->status = 'EN_ATTENTE_VALIDATION';
            }
        }
}

?>
