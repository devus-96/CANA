<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    public function update(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'phone'         => ['sometimes', 'string', 'unique:admins,phone,' . $admin->id, new PhoneNumber],
            'profile'       => 'sometimes|mimes:bmp,jpeg,jpg,png,webp|max:2048',
            'biography'     => 'sometimes|nullable|string',
            'role'          => 'sometimes|string',
            'status'        => 'sometimes|string|in:active,inactive,suspended',
            'parish'        => 'sometimes|string',
            'user_id'       => 'required_with:status,role|exists:admins,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Vérifier si l'utilisateur est super admin
        $roleNames = $admin->roles()->pluck('name')->toArray();
        $isSuperAdmin = in_array(Controller::USER_ROLE_SUPER_ADMIN, $roleNames);

        // Gestion du changement de statut (réservé aux super admins)
        if ($request->has('status')) {
            if (!$isSuperAdmin) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            $selected_admin = Admin::find($request->admin_id);

            if (!$selected_admin) {
                return response()->json(['message' => 'Administrateur non trouvé'], 404);
            }

            // Empêcher de modifier son propre statut
            if ($selected_admin->id === $admin->id) {
                return response()->json(['message' => 'Vous ne pouvez pas modifier votre propre statut'], 403);
            }

            $selected_admin->status = $request->status;
            $selected_admin->manager_id = $admin->id;
            $selected_admin->save();
        }

        // Gestion de l'attribution de rôle (réservé aux super admins)
        if ($request->has('role')) {
            if (!$isSuperAdmin) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            $selected_admin = Admin::find($request->user_id);

            if (!$selected_admin) {
                return response()->json(['message' => 'Administrateur non trouvé'], 404);
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
            $selected_admin->roles()->sync([$role->id]);
        }

        // Mise à jour du profil personnel
        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['phone'])) {
            $updateData['phone'] = $validated['phone'];
        }

        if (isset($validated['biography'])) {
            $updateData['biography'] = $validated['biography'];
        }

        if (isset($validated['parish'])) {
            $updateData['parish'] = $validated['parish'];
        }

        // Gestion de l'upload de la photo de profil
        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profiles', $filename, 'public');
            $updateData['profile'] = $path;

            // Supprimer l'ancienne photo si elle existe
            if ($admin->profile && \Storage::disk('public')->exists($admin->profile)) {
                \Storage::disk('public')->delete($admin->profile);
            }
        }

        // Mettre à jour le profil de l'admin connecté si des données existent
        if (!empty($updateData)) {
            $admin->update($updateData);
        }

        return response()->json([
            'message' => 'Mise à jour effectuée avec succès',
            'data' => $admin->fresh()
        ], 200);
    }
}
