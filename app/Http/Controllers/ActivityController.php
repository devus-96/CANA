<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use App\Models\Activity;
use App\Http\Resources\ActivityResource;

use Illuminate\Http\Request;

class ActivityController extends Controller
{

    public function index (Request $request, Activity $activity) {
        // Charger l'activité avec ses ressources associées
        $ressource = $activity->load('resource_activity');

        return response()->json([
            'message' => "activity details",
            'data' => new ActivityResource($ressource)
        ], 200);
    }

    public function view (Request $request) {
        $event = $request->input('event');
        $category = $request->input('category');
        // Récupérer toutes les activités avec leurs ressources associées

        if ($event) {
            $activities = Activity::whereHas('events', function ($query) use ($event) {
                $query->where('events.id', $event);
            })->with('resource_activity')
              ->orderBy('created_at', 'desc')
              ->paginate(20);
        } elseif ($category) {
            $activities = Activity::where('category_id', $category)
                    ->with('resource_activity')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
        } else {
            $activities = Activity::with('resource_activity')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);
        }

        return response()->json([
            'message' => "list of activities",
            'data' => ActivityResource::collection($activities)
        ], 200);
    }

    public function store(Request $request)
    {
       /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'objectif'       => 'nullable|string',
            'category'       => 'nullable|exists:categories,id',
            'activity_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statut' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $activity = Activity::create([
            'name'           => $request->name,
            'description'    => $request->description,
            'objectif'       => $request->objectif,
            'author'         => $admin->id,
            'responsable_id' => $admin->id,
            'category_id'    => $request->category,
        ]);

        Controller::uploadImages(['image_activity' => $request->image], $activity, 'image_activity');

       // Charger les relations si nécessaire
        $activity->load('resource_activity', 'category', 'responsable');

        return response()->json([
            'message' => 'Activity has been created successfully',
            'data'    => new ActivityResource($activity) // Un seul objet = new, pas collection()
        ], 201); // 201 = Created

    }

    public function update(Request $request, Activity $activity)
    {
        if (!$activity) {
            return response()->json(['statut' => 'error', 'message' => 'Activity not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'           => 'sometimes|required|string|max:255',
            'description'    => 'nullable|string',
            'objectif'       => 'nullable|string',
            'category'       => 'nullable|exists:categories,id',
            'responsable_id' => 'nullable|exists:admins,id',
            'activity_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'active'         => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        // Mise à jour des champs
        $activity->update([
            'name'           => $request->input('name', $activity->name),
            'description'    => $request->input('description', $activity->description),
            'objectif'       => $request->input('objectif', $activity->objectif),
            'category_id'    => $request->input('category', $activity->category_id),
            'responsable_id' => $request->input('responsable_id', $activity->responsable_id),
        ]);


        if ($request->activity_image) {
            Controller::uploadImages(['image_activity' => $request->image_activity], $activity, 'image_activity');
        }

        $activity->load('resource_activity', 'category', 'responsable');

        return response()->json([
            'message' => 'Activity has been updated successfully',
            'data'    => new ActivityResource($activity)
        ], 200);
    }

    public function delete (Activity $activity) {

        $activity->delete();

        return response()->json([
            'statut' => 'success',
            'message' => "Activity has been deleted",
        ], 200);

    }
}
