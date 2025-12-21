<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function store(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'objectif'      => 'nullable|string',
            'image'         => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        $activity = $admin->activity()->create([
            "name" => $request->name,
            "description" => $request->description,
            "objectif" => $request->objectif,
        ]);

        Controller::uploadImages(['image_activity' => $request->image], $activity, 'image_activity');

        return response()->json(['message' => "activity has been created"], 200);

    }

    public function update(Request $request, Activity $activity)
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return response()->json(['statut' => 'error', 'message' => 'Authentication required'], 401);
        }

        $roleNames = $admin->roles()->pluck('name')->toArray();
        $isSuperAdmin = in_array(Controller::USER_ROLE_SUPER_ADMIN, $roleNames);
        $isCreator = $activity->admin_id === $admin->id;

        if (!$isSuperAdmin && !$isCreator) {
            return response()->json([
                'statut' => 'error',
                'message' => 'You are not authorized to delete this activity'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'description'   => 'sometimes|string',
            'objectif'      => 'sometimes|nullable|string',
            'image'         => 'sometimes|file|mimes:jpg,jpeg,png,webp|max:5120',
            'active'        => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $updateData = Arr::except($validatedData, ['image']);

        if (!empty($validatedData)) {
            $activity->update($updateData);
        }

        Controller::uploadImages(['image_activity' => $validatedData['image']], $activity, 'image_activity');

        return response()->json([
            'statut' => 'success',
            'message' => "Activity has been updated",
            'data' => $activity->fresh() // Recharger depuis la base
        ], 200);
    }

    public function delete (Request $request, Activity $activity) {

        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return response()->json(['statut' => 'error', 'message' => 'Authentication required'], 401);
        }

        $roleNames = $admin->roles()->pluck('name')->toArray();
        $isSuperAdmin = in_array(Controller::USER_ROLE_SUPER_ADMIN, $roleNames);
        $isCreator = $activity->admin_id === $admin->id;

        if (!$isSuperAdmin && !$isCreator) {
            return response()->json([
                'statut' => 'error',
                'message' => 'You are not authorized to delete this activity'
            ], 403);
        }

        $activity->delete();

        return response()->json([
            'statut' => 'success',
            'message' => "Activity has been deleted",
        ], 200);

    }
}
