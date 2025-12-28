<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\MediaResource;
use App\Models\Media;

class MediaController extends Controller
{
    public function index (Request $request) {
        try {
            // Validation des paramètres de filtre
            $request->validate([
                'category' => 'nullable|exists:categories,id',
            ]);
            $query = Media::where('activity_id', null)
                        ->where('status', 'published')
                        ->orderBy('created_at', 'desc')->paginate(20);
            // Filtres combinables
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
             // Pagination avec paramètre optionnel
            $perPage = $request->get('per_page', 10);
            $medias = $query->paginate($perPage);

            return response()->json([
            'message' => "list of medias",
            'data' => MediaResource::collection($medias),
            'meta' => [
                    'current_page' => $medias->currentPage(),
                    'total' => $medias->total(),
                    'per_page' => $medias->perPage(),
                ]
        ], 200);

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

    public function store (Request $request)
    {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();
            // Validation des données entrantes
            Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|unique:medias,slug|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'file' => 'required|file|max:102400', // 100MB max
                'duration' => 'nullable|integer|min:0',
                // Relations
                'category_id' => 'nullable|integer|exists:categories,id',
                'activity_id' => 'nullable|integer|exists:activities,id',
                // Visibilité et statut
                'is_public' => 'nullable|boolean',
                'status' => 'in:draft,published,archived'
            ]);

            $media  = Media::create([
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'duration' => $request->input('duration'),
                // Relations
                'category_id' => $request->input('category_id'),
                'author_id' => $admin->id,
                'activity_id' => $request->input('activity_id'),
                // Visibilité et statut
                'is_public' => $request->input('is_public', true),
                'status' => $request->input('status', 'published'),
            ]);
            // Traitement du fichier
            $file = $request->file('file');

            $validated = [
                'file_mame' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
            ];
            $validated['type'] = Controller::determineFileType($validated['mime_type'], $validated['extension']);
            $path = $file->store('media/' . $media->id, 'public');
            $validated['file_path'] = $path;
            // mise à jour du média avec les informations du fichier
            $media->update($validated);
            // Réponse JSON avec le média créé
            return response()->json([
                'message' => 'Média créé avec succès',
                'data' => new MediaResource($media)
            ], 201);
            // an cas de validation échouée
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Média creation failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function delete (Media $media) {
        try {
            if (!$media) {
                return response()->json([
                    'message' => 'Média not found'
                ], 404);
            }
            $media->delete();
            return response()->json([
                'message' => 'Média deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Média deletion failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function forceDelete (Media $media) {
        try {
            if (!$media) {
                return response()->json([
                    'message' => 'Média not found'
                ], 404);
            }
            $media->forceDelete();
            return response()->json([
                'message' => 'Média permanently deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Média permanent deletion failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore (int $id) {
        try{
            $media = Media::withTrashed()->find($id);

            if (!$media || !$media->trashed()) {
                return response()->json([
                        'message' => "Média not found or not deleted"
                ], 404);
            }

            $media->restore();

            return response()->json([
                    'message' => "Média has been restored successfully",
                    'data' => new MediaResource($media)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Média restoration failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
