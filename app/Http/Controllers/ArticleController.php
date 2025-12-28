<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
                            ->where('status', 'published')
                            ->orderBy('created_at', 'desc');
            // Filtres combinables
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
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
            Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'article_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'content' => 'required|string',
                'slug' => 'nullable|string|unique:articles,slug',
                'status' => 'in:draft,published,archived',
                // foreign table validations
                'category_id' => 'nullable|id',
                'category_name' => 'nullable|string|max:255',
                'author_id' => 'required|exists:admins,id',
            ]);
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $request->merge(['category_id' => $category->id]);
            }
            // Création de l'article
            $acticle = $admin->articles()->create([
                'category_id' => $request->category_id,
                'author_id' => $request->author_id,
                'title' => $request->title,
                'content' => $request->content,
                'slug' => $request->slug,
                'status' => $request->status,
            ]);
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
            Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'article_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
                'content' => 'sometimes|required|string',
                'slug' => 'sometimes|nullable|string|unique:articles,slug,' . $article->id,
                'status' => 'sometimes|required|in:draft,published,archived',
                // foreign table validations
                'category_id' => 'sometimes|nullable|id',
                'category_name' => 'sometimes|nullable|string|max:255',
            ]);
            // ajout/creation de la catégorie si le nom est fourni sans ID
            if ($request->has('category_name') && !$request->has('category_id')) {
                $category = Category::firstOrCreate(['name' => $request->input('category_name')]);
                $request->merge(['category_id' => $category->id]);}
            // Mise à jour de l'article
            $article->update([
                'category_id' => $request->input('category_id', $article->category_id),
                'author_id' => $request->input('author_id', $article->author_id),
                'title' => $request->input('title', $article->title),
                'content' => $request->input('content', $article->content),
                'slug' => $request->input('slug', $article->slug),
                'status' => $request->input('status', $article->status)
            ]);
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
            $article->delete();
        return response()->json([
                'message' => "article has been deleted"
        ], 200);
        } catch (\Exception $e) {
            Log::error('Article destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function forceDestroy (Article $article) {
        try {
            if (!$article) {
                return response()->json([
                    'message' => 'Article not found'
                ], 404);
            }
            $article->forceDelete();
            return response()->json([
                    'message' => "article has been permanently deleted"
            ], 200);
        } catch (\Exception $e) {
            Log::error('Article forceDestroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to permanently delete article',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore (int $id) {
        try{
            $article = Article::withTrashed()->find($id);

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
}
