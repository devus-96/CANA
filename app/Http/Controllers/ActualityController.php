<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
                            ->orderBy('created_at', 'desc');
            // Filtres combinables
            if ($request->filled('activity')) {
                $query->where('activity_id', $request->activity);
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if (!$request->filled('status')) {
                $query->where('status', 'published'); // Par défaut: seulement publiés
            }
            if ($request->get('status')) {
                $query->where('status', $request->get('status'));
            }
            if ($request->get('date')) {
                $query->whereDate('published_at', $request->get('date'))
                        ->orderBy('published_at', 'desc');
            }
            if ($request->get('is_pinned')) {
                $query->where('status', $request->get('is_pinned'));
                $query->orderBy('pin_order', 'desc');
            }
            // Recherche par titre ou contenu
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
                });
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
            // Incrémenter le compteur de vues
            $actuality->increment('views_count');

            return response()->json([
                'message' => "Actuality retrieved successfully",
                'data' => new ActualityResource($actuality->load(['author', 'category']))
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
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'excerpt' => 'nullable|string',
                'actuality_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => 'required|string|unique:actualities,slug',
                'status' => 'nullable|in:draft,published,archived,scheduled',
                'published_at' =>  ['nullable', 'date', 'after:now'],
                // mise en avant
                'is_pinned' => ['boolean'],
                'pin_order' => ['integer', 'min:0', 'required_if:is_pinned,true'],
                // foreign table validations
                'category_name' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'activity_id' => 'nullable|exists:activities,id',
            ]);
             $validated = $validator->validated();
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $validated['category_id'] = $category->id;
            }
             // Si le statut est PUBLISHED et published_at n'est pas défini, définir à maintenant
            if (($validated['status'] ?? 'draft') === 'published' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
            // creation de l'actualité
            $actuality  = $admin->actuality()->create($validated);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['actuality_image' => $validated['actuality_image']], $actuality, 'actuality_image');
            // Réponse JSON avec l'article créé
            return response()->json([
                'message' => "actuality has been created",
                "data" => new ActualityResource($actuality->load(['author', 'category']))
            ], 200);
            // echec des validations
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Actuality store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update (Request $request, Actuality $actuality) {
        try {
             // Validation des données entrantes
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'excerpt' => 'sometimes|nullable|string',
                'actuality_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => ['sometimes','required','string',Rule::unique('actualities', 'slug')->ignore($actuality->id),],
                'status' => 'sometimes|required|in:draft,published,archived,scheduled',
                // mise en avant
                'is_pinned' => ['sometimes', 'boolean'],
                'pin_order' => ['sometimes', 'integer', 'min:0', 'required_if:is_pinned,true'],
                // foreign table validations
                'category_id' => 'sometimes|nullable|id',
                'category_name' => 'sometimes|nullable|string|max:255',
                'activity_id' => 'sometimes|nullable|exists:activities,id',

            ]);
            $validated = $validator->validated();
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $validated['category_id'] = $category->id;}
            // Mise à jour de l'actualité
            // Si le statut change en PUBLISHED et published_at n'est pas défini, définir à maintenant
            if (isset($validated['status']) && $validated['status'] === 'scheduled' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
            $actuality->update($validated);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['actuality_image' => $request->actuality_image], $actuality, 'actuality_image');
            // Réponse JSON avec l'actualité mise à jour
            return response()->json([
                'message' => "actuality has been updated",
                "data" => new ActualityResource($actuality->load(['author', 'category']))
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Actuality store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy (Actuality $actuality) {
        try {
            if (!$actuality) {
                return response()->json([
                    'message' => 'Actuality not found'
                ], 404);
            }
            // verifier si l'actuality a deja ete achiver
            if ($actuality->trashed()) {
                // Suppression définitive
                // Supprimer les fichiers associés
                if ($actuality->actuality_image) {
                    Storage::disk('public')->delete($actuality->actuality_image);
                }

                $actuality->forceDelete();

                return response()->json([
                    'message' => 'Contenu supprimé définitivement'
                ]);
            } else {
                $actuality->delete();
                // Réponse JSON de succès
                return response()->json([
                    'statut' => 'success',
                    'message' => "reading has been deleted",
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Actuality destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore(Actuality $actuality)
    {
        try {
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

    public function trashed()
    {
        try {
            $contents = Actuality::onlyTrashed()
            ->with(['author', 'category'])
            ->paginate(15);

            return response()->json([
                'data' => $contents
            ]);
        } catch (\Exception $e) {
            Log::error('Actuality restore error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore actuality',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
