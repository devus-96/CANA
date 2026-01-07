<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\Response;
use App\Services\JWTService;
use Firebase\JWT\ExpiredException;

class Session
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->input('token');

        if (!$token) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        try {
            $user = JWTService::verify($request);

            if (!$user) {
                Auth::logout();
                return response()->json(['error' => 'something went wrong'], 500);
            }

        } catch (ExpiredException $e) {
              return response()->json(['error' => 'Expired token'], 401);
        }catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Signature du token invalide'
            ], 401);
        }
        return $next($request);
    }
}
