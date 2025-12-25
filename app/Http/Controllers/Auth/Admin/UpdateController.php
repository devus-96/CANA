<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Models\Role;
use App\Rules\PhoneNumber;
use App\Http\Resources\AdminResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    public function update(Request $request, Admin $selected_admin)
    {
        $admin = auth()->guard('admin')->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'phone'         => ['sometimes', 'string', 'unique:admins,phone,' . $admin->id, new PhoneNumber],
            'admin_image'   => 'sometimes|nullable|mimes:bmp,jpeg,jpg,png,webp|max:2048',
            'biography'     => 'sometimes|string',
            'role'          => 'sometimes|string',
            'status'        => 'sometimes|string|in:ACTIVE,REJECTED,BLOCKED',
            'parish'        => 'sometimes|string',
            'user_id'       => 'required_with:status,role|exists:admins,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Gestion du changement de statut (réservé aux super admins)
        if ($request->has('status')) {
            if (!$admin->isSuperAdmin) {
                return response()->json(['message' => 'Non autorise'], 403);
            }

            // Empêcher de modifier son propre statut
            if ($selected_admin->id === $admin->id) {
                return response()->json(['message' => 'Vous ne pouvez pas modifier votre propre statut'], 403);
            }

            switch ($request->status) {
                case 'ACTIVE':
                    $selected_admin->activated_at = now();
                    break;
                case 'REJECTED':
                    $selected_admin->rejected_at = now();
                    break;
                case 'BLOCKED':
                    $selected_admin->blocked_at = now();
                    break;
            }

            $selected_admin->status = $request->status;
            $selected_admin->status_updated_by = $admin->id;
            $selected_admin->save();
        }

        // Gestion de l'attribution de rôle (réservé aux super admins)
        if ($request->has('role')) {
            if (!$admin->isSuperAdmin) {
                return response()->json(['message' => 'Non autorise'], 403);
            }

            // Empêcher de modifier ses propres rôles
            if ($selected_admin->id === $admin->id) {
                return response()->json(['message' => 'Vous ne pouvez pas modifier vos propres rôles'], 403);
            }

            $role = Role::where('name', $request->role)->first();

            if (!$role) {
                return response()->json(['message' => 'Rôle non trouvé'], 404);
            }

            // Détacher les anciens rôles et attacher le nouveau
            $selected_admin->role()->attach(
                    Role::where('name', $request->role)->first()->id
            );
            $selected_admin->assigned_by = $admin->id;
            $selected_admin->save();
        }

        // Mise à jour du profil personnel
        $updateData = request()->only(['name', 'phone', 'biography', 'parish']);

        if (!empty($updateData)) {
            $selected_admin->update($updateData);
        }

        Controller::uploadImages(['admin_image' => $request->admin_image], $selected_admin, 'admin_image');

        return response()->json([
            'message' => 'Mise à jour effectuée avec succès',
            'data' => new AdminResource($selected_admin)
        ], 200);
    }
}
