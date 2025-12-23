<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class VerifyAdminSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $score = 0;

        $token = $request->bearerToken() ?? $request->cookie('admin_token');

        if (!$token) {
            return $this->unauthorized('Token manquant');
        }

        $connexion = DB::table('connexions')->where('token', $token)->first();

        if (!$connexion) {
            return $this->unauthorized('Token invalide');
        }

        if ($connexion->status !== 'ACTIVE') {
            return $this->unauthorized("Connexion {$connexion->status}", [
                'status' => $connexion->status,
                'revoked_at' => $connexion->revoked_at
            ]);
        }

        // 3. Vérifier l'expiration
        if (Carbon::parse($connexion->expired_at)->isPast()) {
            $this->markAsExpired($connexion->id);
            return $this->unauthorized('Token expiré');
        }

        if ($connexion->ip_address && $connexion->ip_address !== $request->ip()) $sore = 10;
        if ($connexion->navigator && $connexion->navigator !== $currentNavigator) $score = 50;
        if ($connexion->device && $connexion->device !== $currentDevice) $score = 50;

        if ($connexion->device && $connexion->device !== $currentDevice) {
            $this->markAsSuspicious($connexion->id, 'Device différent');
            return $this->unauthorized('Appareil non autorisé', [
                'expected' => $connexion->device,
                'received' => $currentDevice
            ]);
        }

        return $next($request);
    }

    private function unauthorized(string $message, array $data = []): Response
    {
        return response()->json([ 'success' => false, 'message' => $message, 'data' => $data], 401);
    }

     /**
     * Marquer une connexion comme expirée
     */
    private function markAsExpired(int $connexionId): void
    {
        DB::table('connexions')->where('id', $connexionId)
            ->update([
                'status' => 'EXPIRED',
                'updated_at' => now()
            ]);
    }
}
