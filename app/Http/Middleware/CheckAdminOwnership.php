<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckAdminOwnership
{
    public function handle(Request $request, Closure $next, string $modelParam = 'event')
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'statut' => 'error',
                'message' => 'Authentication required'
            ], 401);
        }

        // Récupérer le modèle depuis la route
        $model = $request->route($modelParam);

        // Super Admin peut tout faire
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        if ($admin->isStateLiveManager()) {
             return response()->json([
                'statut' => 'error',
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Vérifier la propriété
        if ($model && $model->admin_id !== $admin->id) {
            return response()->json([
                'statut' => 'error',
                'message' => 'Accès non autorisé'
            ], 403);
        }

        return $next($request);
    }
}
