<?php

use App\Models\Member;

class DeleteMemberController
{
   public function delete(Member $selected_member)
    {
        $admin = auth()->guard('member')->user();

        if ($admin->id !== $selected_member->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $selected_member->delete();
        return response()->json(['message' => 'menbre supprimé avec succès'], 200);
    }
}
