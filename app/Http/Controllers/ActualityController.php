<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\ActualityResource;
use App\Models\Actuality;
use App\Models\Category;

class ActualityController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validation des paramètres de filtre
            $request->validate([
                'category' => 'nullable|exists:categories,id',
                'activity' => 'nullable|exists:activities,id',
            ]);
            // Construction de la requête avec les relations et filtres
            $query = Actuality::with(['author', 'category'])
                            ->where('status', 'published')
                            ->orderBy('created_at', 'desc');
            // Filtres combinables
            if ($request->filled('activity')) {
                $query->where('activity_id', $request->activity);
            }

            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            // Pagination avec paramètre optionnel
            $perPage = $request->get('per_page', 10);
            $actualities = $query->paginate($perPage);
            // Réponse JSON avec les actualités paginées
            return response()->json([
                'message' => "Actualities retrieved successfully",
                'data' => ActualityResource::collection($actualities),
                'meta' => [
                    'current_page' => $actualities->currentPage(),
                    'total' => $actualities->total(),
                    'per_page' => $actualities->perPage(),
                ]
            ], 200);
            // Fin du bloc try
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(Actuality $actuality)
    {
        try {
            if (!$actuality) {
                return response()->json(['statut' => 'error', 'message' => 'actuality not found'], 404);
            }
            return response()->json([
                'message' => "Actuality retrieved successfully",
                'data' => new ActualityResource($actuality->load(['admin', 'category']))
            ], 200);

        } catch (\Exception $e) {
            Log::error('Actuality show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function store (Request $request) {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();
            // Validation des données entrantes
            Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'actuality_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => 'nullable|string|unique:actualities,slug',
                'status' => 'in:draft,published,archived',
                // foreign table validations
                'category_name' => 'nullable|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'activity_id' => 'nullable|exists:activities,id',
                'author_id' => 'required|exists:admins,id',
            ]);
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $request->merge(['category_id' => $category->id]);
            }
            // creation de l'actualité
            $actuality  = $admin->actuality()->create([
                'title' => $request->title,
                'content' => $request->content,
                'slug' => $request->slug,
                'category_id' => $request->category_id,
                'activity_id' => $request->activity_id,
                'status' => $request->status,
            ]);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['actuality_image' => $request->actuality_image], $actuality, 'actuality_image');
            // Réponse JSON avec l'article créé
            return response()->json([
                'message' => "actuality has been created",
                "data" => new ActualityResource($actuality->load(['admin', 'category']))
            ], 200);
            // echec des validations
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

    public function update (Request $request, Actuality $actuality) {
        try {
             // Validation des données entrantes
            Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'actuality_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => 'sometimes|nullable|string|unique:actualities,slug',
                'status' => 'sometimes|required|in:draft,published,archived',
                // foreign table validations
                'category_id' => 'sometimes|nullable|id',
                'category_name' => 'sometimes|nullable|string|max:255',
                'activity_id' => 'sometimes|nullable|exists:activities,id',

            ]);
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $request->merge(['category_id' => $category->id]);}
            // Mise à jour de l'actualité
            $actuality->update([
                'title' => $request->input('title', $actuality->title),
                'content' => $request->input('content', $actuality->content),
                'actuality_image' => $request->input('actuality_image', $actuality->actuality_image),
                'slug' => $request->input('slug', $actuality->slug),
                'category_id' => $request->input('category_id', $actuality->category_id),
                'activity_id' => $request->input('activity_id', $actuality->activity_id),
                'status' => $request->input('status', $actuality->status),
            ]);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['actuality_image' => $request->actuality_image], $actuality, 'actuality_image');
            // Réponse JSON avec l'actualité mise à jour
            return response()->json([
                'message' => "actuality has been updated",
                "data" => new ActualityResource($actuality->load(['admin', 'category']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'something went wrong'], 500);
        }
    }

    public function destroy (Actuality $actuality) {
        try {
            if (!$actuality) {
                return response()->json([
                    'message' => 'Actuality not found'
                ], 404);
            }
            $actuality->delete();
            // Réponse JSON de succès
            return response()->json([
                'statut' => 'success',
                'message' => "reading has been deleted",
            ], 200);
        } catch (\Exception $e) {
            Log::error('Article destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function forceDestroy(Actuality $actuality)
    {
        try {
           if (!$actuality) {
                return response()->json([
                    'message' => 'Actuality not found'
                ], 404);
            }
            $actuality->forceDelete();
            // Réponse JSON de succès
            return response()->json(['message' => 'Suppression définitive réussie'], 200);
        } catch (\Exception $e) {
            Log::error('Actuality force destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to permanently delete actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore(int $id)
    {
        try {
            $actuality = Actuality::withTrashed()->findOrFail($id);

            if (!$actuality || !$actuality->trashed()) {
                return response()->json(['message' => 'L\'élément n\'est pas supprimé'], 400);
            }

            $actuality->restore();

            return response()->json([
                    'message' => "actuality has been restored",
                    "data" => new ActualityResource($actuality->load(['admin']))
            ], 200);
        } catch (\Exception $e) {
            Log::error('Actuality restore error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

    }
}
