<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                'responsable' => 'nullable|exists:admins,id',
            ]);
            // Construction de la requête avec les relations et filtres
            $query = Activity::with(['media','category', 'responsable', 'author'])
                            ->orderBy('created_at', 'desc');
            // Filtres combinables
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if ($request->filled('responsable')) {
                $query->where('responsable_id', $request->responsable);
            }
            // Filtrage par statut actif
            if ($request->filled('active')) {
                $query->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
            }
            // Recherche par nom ou description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
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
             Log::error('Activity index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show (Activity $activity)
    {
        try {
            if (!$activity) {
                return response()->json(['statut' => 'error', 'message' => 'Activity not found'], 404);
            }
            $activity->load('resource_activity', 'category', 'responsable', 'author');

            return response()->json([
                'message' => 'Activity details',
                'data'    => new ActivityResource($activity)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Activity index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
                'active' => 'nullable|boolean',
                //foreign keys|attributes
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
                'data'    => new ActivityResource( $activity->load('media', 'category', 'responsable')) // Un seul objet = new, pas collection()
            ], 201); // 201 = Created
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Article store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(Request $request, Activity $activity)
    {
        try {
            Validator::make($request->all(), [
                'name'           => 'sometimes|required|string|max:255',
                'description'    => 'nullable|string',
                'objectif'       => 'nullable|string',
                'activity_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
                'active'         => 'nullable|boolean',
                // foreign table validations
                'category'       => 'nullable|exists:categories,id',
                'category_name' => 'sometimes|nullable|string|max:255',
                'responsable' => 'nullable|exists:admins,id',
            ]);
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $request->merge(['category_id' => $category->id]);}
            // Mise à jour des champs
            $activity->update([
                'name'           => $request->input('name', $activity->name),
                'description'    => $request->input('description', $activity->description),
                'objectif'       => $request->input('objectif', $activity->objectif),
                'category_id'    => $request->input('category', $activity->category_id),
                'responsable_id' => $request->input('responsable_id', $activity->responsable_id),
            ]);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['image_activity' => $request->image_activity], $activity, 'image_activity');
            $activity->load('media', 'category', 'responsable');

            return response()->json([
                'message' => 'Activity has been updated successfully',
                'data'    => new ActivityResource($activity)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Article store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy (int $id)
    {
        try {
            $activity = Activity::withTrashed()->findOrFail($id);

            if (!$activity) {
                return response()->json(['message' => 'Activity not found'], 404);
            }

            if ($activity->trashed()) {
                // Suppression définitive
                // Supprimer l'image associée
                if ($activity->activity_image) {
                    Storage::disk('public')->delete($activity->activity_image);
                }

                $activity->forceDelete();

                return response()->json(['message' => 'Suppression définitive réussie'], 200);
            } else {
                $activity->delete();

                return response()->json([
                    'statut' => 'success',
                    'message' => "Activity has been deleted",
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Activity force destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to permanently delete Activity',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore(int $id)
    {
        try {
            $activity = Activity::withTrashed()->findOrFail($id);

            if (!$activity || !$activity->trashed()) {
                return response()->json(['message' => 'L\'élément n\'est pas supprimé'], 400);
            }

            $activity->restore();

            return response()->json([
                    'message' => "Activity has been restored",
                    "data" => new ActivityResource($activity->load(['admin']))
            ], 200);
        } catch (\Exception $e) {
            Log::error('Activity restore error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore Activity',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

    }

    public function trashed ()
    {
        $activity = Activity::onlyTrashed()
            ->with(['media', 'category', 'responsable'])
            ->paginate(15);

        return response()->json([
            'data' => new ActivityResource($activity)
        ]);
    }
}
