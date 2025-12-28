<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use App\Models\Activity;
use App\Http\Resources\ActivityResource;
use App\Models\Category;

use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index (Request $request) {
        try {
            // Validation des paramètres de filtre
            $request->validate([
                'category' => 'nullable|exists:categories,id',
                'event' => 'nullable|exists:events,id',
            ]);
            // Construction de la requête avec les relations et filtres
            $query = Activity::with(['media','category', 'responsable', 'author'])
                            ->orderBy('created_at', 'desc');
            // Filtres combinables
            if ($request->filled('event')) {
                $query->where('activity_id', $request->event);
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            // Pagination avec paramètre optionnel
            $perPage = $request->get('per_page', 10);
            $activity = $query->paginate($perPage);

            return response()->json([
                'message' => "list of activities",
                'data' => ActivityResource::collection($activity),
                'meta' => [
                    'current_page' => $activity->currentPage(),
                    'total' => $activity->total(),
                    'per_page' => $activity->perPage(),
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show (Activity $activity) {
        if (!$activity) {
            return response()->json(['statut' => 'error', 'message' => 'Activity not found'], 404);
        }

        // Charger les relations nécessaires
        $activity->load('resource_activity', 'category', 'responsable', 'author');

        return response()->json([
            'message' => 'Activity details',
            'data'    => new ActivityResource($activity)
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            Validator::make($request->all(), [
                'name'           => 'required|string|max:255',
                'description'    => 'nullable|string',
                'objectif'       => 'nullable|string',
                'activity_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
                'category'       => 'nullable|exists:categories,id',
                'category_name'  => 'nullable|string|max:255',
                'responsable' => 'nullable|exists:admins,id',
            ]);
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $request->merge(['category_id' => $category->id]);
            }
            // Création de l'activité
             $activity = Activity::create([
                'name'           => $request->name,
                'description'    => $request->description,
                'objectif'       => $request->objectif,
                'author'         => $admin->id,
                'responsable_id' => $admin->responsable,
                'category_id'    => $request->category,
            ]);
            // Gestion de l'upload de l'image
            Controller::uploadImages(['image_activity' => $request->image], $activity, 'image_activity');
            //  envoyer la réponse avec les relations chargées
            return response()->json([
                'message' => 'Activity has been created successfully',
                'data'    => new ActivityResource( $activity->load('resource_activity', 'category', 'responsable')) // Un seul objet = new, pas collection()
            ], 201); // 201 = Created
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
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
