<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlySuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'statut' => 'error',
                'message' => 'Authentication required'
            ], 401);
        }

        if (!$admin->isSuperAdmin()) {
             return response()->json([
                'statut' => 'error',
                'message' => 'Accès non autorisé'
            ], 403);
        }

        return $next($request);
    }
}
