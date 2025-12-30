<?php

namespace App\Http\Controllers;

use App\Models\StateOfLiveContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StateOfLiveContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StateOfLiveContent::with(['stateOfLife', 'author']);

        // Filtrage par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrage par état de vie
        if ($request->has('state_of_life_id')) {
            $query->where('state_of_life_id', $request->state_of_life_id);
        }

        // Filtrage par auteur
        if ($request->has('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Recherche par titre ou contenu
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $contents = $query->paginate($perPage);

        return response()->json([
            'data' => $contents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_of_life_id' => 'required|exists:states_of_life,id',
            'author_id' => 'required|exists:admins,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'required|string|unique:state_of_live_contents,slug',
            'type' => 'required|in:ENSEIGNEMENT,ACTUALITE,TEMOIGNAGE,ANNONCE',
            'media_url' => 'nullable|url',
            'media_type' => 'nullable|in:AUDIO,VIDEO,DOCUMENT',
            'media_duration' => 'nullable|integer|min:1',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:DRAFT,PENDING,PUBLISHED,ARCHIVED',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,txt|max:5120',
            'comments_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Gestion de l'image de couverture
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('state_of_live_contents/featured_images', 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Gestion des fichiers attachés
        if ($request->hasFile('attachments')) {
            $attachmentsPaths = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('state_of_live_contents/attachments', 'public');
                $attachmentsPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }
            $validated['attachments'] = json_encode($attachmentsPaths);
        } elseif (isset($validated['attachments'])) {
            $validated['attachments'] = json_encode($validated['attachments']);
        }

        // Si le statut est PUBLISHED et published_at n'est pas défini, définir à maintenant
        if (($validated['status'] ?? 'DRAFT') === 'PUBLISHED' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Si is_featured est activé, désactiver is_featured sur les autres contenus
        if (($validated['is_featured'] ?? false) === true) {
            StateOfLiveContent::where('state_of_life_id', $validated['state_of_life_id'])
                ->where('is_featured', true)
                ->update(['is_featured' => false]);
        }

        $content = StateOfLiveContent::create($validated);

        return response()->json([
            'message' => 'Contenu créé avec succès',
            'data' => $content->load(['stateOfLife', 'author'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $content = StateOfLiveContent::with(['stateOfLife', 'author'])->find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu non trouvé'
            ], 404);
        }

        // Incrémenter le compteur de vues
        $content->increment('views_count');

        return response()->json([
            'data' => $content
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $content = StateOfLiveContent::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'state_of_life_id' => 'sometimes|required|exists:states_of_life,id',
            'author_id' => 'sometimes|required|exists:admins,id',
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'slug' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('state_of_live_contents', 'slug')->ignore($content->id),
            ],
            'type' => 'sometimes|required|in:ENSEIGNEMENT,ACTUALITE,TEMOIGNAGE,ANNONCE',
            'media_url' => 'nullable|url',
            'media_type' => 'nullable|in:AUDIO,VIDEO,DOCUMENT',
            'media_duration' => 'nullable|integer|min:1',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:DRAFT,PENDING,PUBLISHED,ARCHIVED',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,txt|max:5120',
            'comments_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Gestion de l'image de couverture
        if ($request->hasFile('featured_image')) {
            // Supprimer l'ancienne image
            if ($content->featured_image) {
                Storage::disk('public')->delete($content->featured_image);
            }

            $imagePath = $request->file('featured_image')->store('state_of_live_contents/featured_images', 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Gestion des fichiers attachés
        if ($request->hasFile('attachments')) {
            // Supprimer les anciens fichiers
            if ($content->attachments) {
                $oldAttachments = json_decode($content->attachments, true);
                foreach ($oldAttachments as $attachment) {
                    Storage::disk('public')->delete($attachment['path'] ?? '');
                }
            }

            $attachmentsPaths = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('state_of_live_contents/attachments', 'public');
                $attachmentsPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }
            $validated['attachments'] = json_encode($attachmentsPaths);
        } elseif (isset($validated['attachments']) && is_array($validated['attachments'])) {
            $validated['attachments'] = json_encode($validated['attachments']);
        }

        // Si le statut change en PUBLISHED et published_at n'est pas défini, définir à maintenant
        if (isset($validated['status']) && $validated['status'] === 'PUBLISHED' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Gestion de is_featured
        if (isset($validated['is_featured']) && $validated['is_featured'] === true) {
            $stateOfLifeId = $validated['state_of_life_id'] ?? $content->state_of_life_id;

            StateOfLiveContent::where('state_of_life_id', $stateOfLifeId)
                ->where('id', '!=', $content->id)
                ->where('is_featured', true)
                ->update(['is_featured' => false]);
        }

        $content->update($validated);

        return response()->json([
            'message' => 'Contenu mis à jour avec succès',
            'data' => $content->fresh()->load(['stateOfLife', 'author'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $content = StateOfLiveContent::withTrashed()->find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu non trouvé'
            ], 404);
        }

        if ($content->trashed()) {
            // Suppression définitive
            // Supprimer les fichiers associés
            if ($content->featured_image) {
                Storage::disk('public')->delete($content->featured_image);
            }

            if ($content->attachments) {
                $attachments = json_decode($content->attachments, true);
                foreach ($attachments as $attachment) {
                    Storage::disk('public')->delete($attachment['path'] ?? '');
                }
            }

            $content->forceDelete();

            return response()->json([
                'message' => 'Contenu supprimé définitivement'
            ]);
        } else {
            // Soft delete
            $content->delete();

            return response()->json([
                'message' => 'Contenu archivé avec succès'
            ]);
        }
    }

    /**
     * Restore a soft deleted content.
     */
    public function restore($id)
    {
        $content = StateOfLiveContent::onlyTrashed()->find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu archivé non trouvé'
            ], 404);
        }

        $content->restore();

        return response()->json([
            'message' => 'Contenu restauré avec succès',
            'data' => $content->load(['stateOfLife', 'author'])
        ]);
    }

    /**
     * Get trashed contents.
     */
    public function trashed()
    {
        $contents = StateOfLiveContent::onlyTrashed()
            ->with(['stateOfLife', 'author'])
            ->paginate(15);

        return response()->json([
            'data' => $contents
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured($id)
    {
        $content = StateOfLiveContent::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu non trouvé'
            ], 404);
        }

        $newStatus = !$content->is_featured;

        // Si on veut mettre en avant, désactiver les autres
        if ($newStatus) {
            StateOfLiveContent::where('state_of_life_id', $content->state_of_life_id)
                ->where('id', '!=', $content->id)
                ->where('is_featured', true)
                ->update(['is_featured' => false]);
        }

        $content->update(['is_featured' => $newStatus]);

        return response()->json([
            'message' => $newStatus ? 'Contenu mis en avant' : 'Contenu retiré de la mise en avant',
            'data' => $content
        ]);
    }

    /**
     * Update status of content.
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:DRAFT,PENDING,PUBLISHED,ARCHIVED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $content = StateOfLiveContent::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu non trouvé'
            ], 404);
        }

        $status = $request->status;
        $updates = ['status' => $status];

        // Si publication, définir published_at
        if ($status === 'PUBLISHED' && !$content->published_at) {
            $updates['published_at'] = now();
        }

        $content->update($updates);

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'data' => $content
        ]);
    }

    /**
     * Increment views count.
     */
    public function incrementViews($id)
    {
        $content = StateOfLiveContent::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenu non trouvé'
            ], 404);
        }

        $content->increment('views_count');

        return response()->json([
            'message' => 'Compteur de vues incrémenté',
            'views_count' => $content->fresh()->views_count
        ]);
    }

    /**
     * Get contents by state of life.
     */
    public function byStateOfLife($stateOfLifeId)
    {
        $contents = StateOfLiveContent::with(['author'])
            ->where('state_of_life_id', $stateOfLifeId)
            ->where('status', 'PUBLISHED')
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $contents
        ]);
    }

    /**
     * Get featured contents.
     */
    public function featured()
    {
        $contents = StateOfLiveContent::with(['stateOfLife', 'author'])
            ->where('is_featured', true)
            ->where('status', 'PUBLISHED')
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json([
            'data' => $contents
        ]);
    }
}
