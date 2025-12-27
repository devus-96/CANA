<?php

use App\Models\Admin;

class DeleteController
{
    public function delete(Admin $selected_admin)
    {
        $admin = auth()->guard('admin')->user();

        if ($admin->id !== $selected_admin->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $selected_admin->delete();

        return response()->json(['message' => 'Administrateur supprimé avec succès'], 200);
    }

    public function forceDelete(Admin $selected_admin)
    {
        $admin = auth()->guard('admin')->user();

        if ($admin->id !== $selected_admin->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $selected_admin->forceDelete();

        return response()->json(['message' => 'Administrateur supprimé définitivement avec succès'], 200);
    }

    public function restore(Admin $selected_admin)
    {
        /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        if ($admin->isSuperAdmin() === false) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $selected_admin->restore();

        return response()->json(['message' => 'Administrateur restauré avec succès'], 200);
    }
}
