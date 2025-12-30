<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Category;

class ArticleController extends Controller
{
     public function index (Request $request) {
        try {
            // Validation des paramètres de filtre
            $request->validate([
                'category' => 'nullable|exists:categories,id',
            ]);
            // Construction de la requête avec les relations et filtres
            $query = Article::with(['author', 'category'])
                            ->orderBy('created_at', 'desc');
            // Filtres combinables
           if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if (!$request->has('status')) {
                $query->where('status', 'published'); // Par défaut: seulement publiés
            }
            if ($request->get('status')) {
                $query->where('status', $request->get('status'));
            }
            if ($request->get('date')) {
                $query->whereDate('published_at', $request->get('date'))
                        ->orderBy('published_at', 'desc');
            }
            if ($request->get('is_featured')) {
                $query->where('is_featured', $request->get('is_featured'));
            }
            // Recherche par titre ou contenu
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
                });
            }
            // Pagination avec paramètre optionnel
            $perPage = $request->get('per_page', 10);
            $articles = $query->paginate($perPage);
            // Réponse JSON avec les articles paginées
            return response()->json([
                'message' => "Articles retrieved successfully",
                'data' => ArticleResource::collection($articles),
                'meta' => [
                    'current_page' => $articles->currentPage(),
                    'total' => $articles->total(),
                    'per_page' => $articles->perPage(),
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
             Log::error('Article index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve activities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
     }

     public function show (Article $article) {
        try {
            // Incrémenter le compteur de vues
            $article->increment('views_count');
            // Réponse JSON avec l'article demandé
            return response()->json([
                'message' => "article retrieved successfully",
                "data" => new ArticleResource($article->load(['author', 'category']))
            ], 200);
            // Gestion des exceptions
        } catch (\Exception $e) {
            Log::error('Article show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store (Request $request)
    {
        try {
             /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();
            // Validation des données entrantes
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string',
                'article_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'content' => 'required|string',
                'slug' => 'required|string|unique:articles,slug',
                'status' => 'nullable|in:draft,published,archived,scheduled',
                'tags' => 'nullable|array',
                'published_at' =>  ['nullable', 'date', 'after:now'],
                'is_featured' => ['nullable', 'boolean'],
                // foreign table validations
                'category_id' => 'nullable|id',
                'category_name' => 'nullable|string|max:255',
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
            if ($request->filled('tags')) {
                $validated['tags'] = json_encode($validated['tags']);
            }
            // Création de l'article
            $acticle = $admin->articles()->create($validated);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['article_image' => $request->article_image], $acticle, 'article_image');
            // Réponse JSON avec l'article créé
            return response()->json([
                    'message' => "article has been created",
                    "data" => new ArticleResource($acticle->load(['admin']))
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

    public function update (Request $request, Article $article) {
        try {
            // Validation des données entrantes
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'excerpt' => 'sometimes|nullable|string',
                'article_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
                'content' => 'sometimes|required|string',
                'slug' => 'sometimes|nullable|string|unique:articles,slug,' . $article->id,
                'status' => 'sometimes|required|in:draft,published,archived',
                'tags' => 'sometimes|nullable|array',
                'published_at' =>  ['sometimes', 'nullable', 'date', 'after:now'],
                'is_featured' => ['sometimes', 'nullable', 'boolean'],
                // foreign table validations
                'category_id' => 'sometimes|nullable|id',
                'category_name' => 'sometimes|nullable|string|max:255',
            ]);
            $validated = $validator->validated();
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $validated['category_id'] = $category->id;
            }
            if (isset($validated['status']) && $validated['status'] === 'scheduled' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
            if ($request->filled('tags')) {
                $validated['tags'] = json_encode($validated['tags']);
            }
            // Création
            // Mise à jour de l'article
            $article->update($validated);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['article_image' => $request->article_image], $article, 'article_image');
            // Réponse JSON avec l'article mis à jour
             return response()->json([
                    'message' => "article has been updated",
                    "data" => new ArticleResource($article->load(['admin']))
             ], 200);
            // echec des validations
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Article update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy (Article $article) {
        try {
            if (!$article) {
                return response()->json([
                    'message' => 'Article not found'
                ], 404);
            }
            // verifier si l'actuality a deja ete achiver
            if ($article->trashed()) {
                // Suppression définitive
                // Supprimer les fichiers associés
                if ($article->article_image) {
                    Storage::disk('public')->delete($article->article_image);
                }

                $article->forceDelete();

                return response()->json([
                    'message' => 'Contenu supprimé définitivement'
                ]);
            } else {
                $article->delete();
                // Réponse JSON de succès
                return response()->json([
                    'statut' => 'success',
                    'message' => "article has been deleted",
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Article destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore (Article $article) {
        try{
            if (!$article || !$article->trashed()) {
                return response()->json([
                        'message' => "article not found or not deleted"
                ], 404);
            }
             $article->restore();

            return response()->json([
                    'message' => "article has been restored",
                    "data" => new ArticleResource($article->load(['admin']))
            ], 200);
        } catch (\Exception $e) {
            Log::error('Article restore error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function trashed()
    {
        try {
            $contents = Article::onlyTrashed()
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
