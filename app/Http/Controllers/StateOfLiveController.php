<?php

namespace App\Http\Controllers;

use App\Models\StateOfLive;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StateOfLiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StateOfLive::with(['responsable', 'author']);

        // Filtrage par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtrage par statut actif
        if ($request->has('active')) {
            $query->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        // Recherche par nom ou description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'ordre');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination ou récupération de tous
        if ($request->has('paginate') && $request->paginate === 'false') {
            $states = $query->get();
        } else {
            $perPage = $request->get('per_page', 15);
            $states = $query->paginate($perPage);
        }

        return response()->json([
            'data' => $states
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:state_of_lives,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'stateoflive_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'membership_criteria' => 'nullable|string',
            'values' => 'nullable|string',
            'type' => 'nullable|in:AGE_GROUP,MARITAL_STATUS,VOCATION,CONSECRATION,COMMITMENT,FRATERNITY,RELATED_COMMUNITY',
            'members_count' => 'nullable|integer|min:0',
            'contents_count' => 'nullable|integer|min:0',
            'page_views' => 'nullable|integer|min:0',
            'responsable_id' => 'nullable|exists:admins,id',
            'author' => 'nullable|exists:admins,id',
            'active' => 'nullable|boolean',
            'ordre' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Gestion de l'image
        if ($request->hasFile('stateoflive_image')) {
            $imagePath = $request->file('stateoflive_image')->store('state_of_lives', 'public');
            $validated['stateoflive_image'] = $imagePath;
        }

        $stateOfLive = StateOfLive::create($validated);

        return response()->json([
            'message' => 'État de vie créé avec succès',
            'data' => $stateOfLive->load(['responsable', 'author'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Recherche par ID ou slug
        if (is_numeric($id)) {
            $state = StateOfLive::with(['responsable', 'author'])->find($id);
        } else {
            $state = StateOfLive::with(['responsable', 'author'])->where('slug', $id)->first();
        }

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        // Incrémenter le compteur de vues
        $state->increment('page_views');

        return response()->json([
            'data' => $state
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $state = StateOfLive::find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('state_of_lives', 'slug')->ignore($state->id),
            ],
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'stateoflive_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'membership_criteria' => 'nullable|string',
            'values' => 'nullable|string',
            'type' => 'nullable|in:AGE_GROUP,MARITAL_STATUS,VOCATION,CONSECRATION,COMMITMENT,FRATERNITY,RELATED_COMMUNITY',
            'members_count' => 'nullable|integer|min:0',
            'contents_count' => 'nullable|integer|min:0',
            'page_views' => 'nullable|integer|min:0',
            'responsable_id' => 'nullable|exists:admins,id',
            'author' => 'nullable|exists:admins,id',
            'active' => 'nullable|boolean',
            'ordre' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Gestion de l'image
        if ($request->hasFile('stateoflive_image')) {
            // Supprimer l'ancienne image
            if ($state->stateoflive_image) {
                Storage::disk('public')->delete($state->stateoflive_image);
            }

            $imagePath = $request->file('stateoflive_image')->store('state_of_lives', 'public');
            $validated['stateoflive_image'] = $imagePath;
        }

        $state->update($validated);

        return response()->json([
            'message' => 'État de vie mis à jour avec succès',
            'data' => $state->fresh()->load(['responsable', 'author'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $state = StateOfLive::withTrashed()->find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        if ($state->trashed()) {
            // Suppression définitive
            // Supprimer l'image associée
            if ($state->stateoflive_image) {
                Storage::disk('public')->delete($state->stateoflive_image);
            }

            $state->forceDelete();

            return response()->json([
                'message' => 'État de vie supprimé définitivement'
            ]);
        } else {
            // Soft delete
            $state->delete();

            return response()->json([
                'message' => 'État de vie archivé avec succès'
            ]);
        }
    }

    /**
     * Restore a soft deleted state of live.
     */
    public function restore($id)
    {
        $state = StateOfLive::onlyTrashed()->find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie archivé non trouvé'
            ], 404);
        }

        $state->restore();

        return response()->json([
            'message' => 'État de vie restauré avec succès',
            'data' => $state->load(['responsable', 'author'])
        ]);
    }

    /**
     * Get trashed states of live.
     */
    public function trashed()
    {
        $states = StateOfLive::onlyTrashed()
            ->with(['responsable', 'author'])
            ->paginate(15);

        return response()->json([
            'data' => $states
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive($id)
    {
        $state = StateOfLive::find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        $state->update(['active' => !$state->active]);

        return response()->json([
            'message' => $state->active ? 'État de vie activé' : 'État de vie désactivé',
            'data' => $state
        ]);
    }

    /**
     * Update order of states.
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'states' => 'required|array',
            'states.*.id' => 'required|exists:state_of_lives,id',
            'states.*.ordre' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->states as $item) {
            StateOfLive::where('id', $item['id'])->update(['ordre' => $item['ordre']]);
        }

        return response()->json([
            'message' => 'Ordre mis à jour avec succès'
        ]);
    }

    /**
     * Increment members count.
     */
    public function incrementMembers($id)
    {
        $state = StateOfLive::find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        $state->increment('members_count');

        return response()->json([
            'message' => 'Compteur de membres incrémenté',
            'members_count' => $state->fresh()->members_count
        ]);
    }

    /**
     * Decrement members count.
     */
    public function decrementMembers($id)
    {
        $state = StateOfLive::find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        $state->decrement('members_count');

        return response()->json([
            'message' => 'Compteur de membres décrémenté',
            'members_count' => $state->fresh()->members_count
        ]);
    }

    /**
     * Update members count.
     */
    public function updateMembersCount(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'members_count' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $state = StateOfLive::find($id);

        if (!$state) {
            return response()->json([
                'message' => 'État de vie non trouvé'
            ], 404);
        }

        $state->update(['members_count' => $request->members_count]);

        return response()->json([
            'message' => 'Compteur de membres mis à jour',
            'data' => $state
        ]);
    }

    /**
     * Get states by type.
     */
    public function byType($type)
    {
        $states = StateOfLive::with(['responsable', 'author'])
            ->where('type', $type)
            ->where('active', true)
            ->orderBy('ordre', 'asc')
            ->get();

        return response()->json([
            'data' => $states
        ]);
    }

    /**
     * Get active states.
     */
    public function active()
    {
        $states = StateOfLive::with(['responsable', 'author'])
            ->where('active', true)
            ->orderBy('ordre', 'asc')
            ->get();

        return response()->json([
            'data' => $states
        ]);
    }

    /**
     * Get statistics.
     */
    public function statistics()
    {
        $totalStates = StateOfLive::count();
        $activeStates = StateOfLive::where('active', true)->count();
        $totalMembers = StateOfLive::sum('members_count');
        $totalViews = StateOfLive::sum('page_views');

        $byType = StateOfLive::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        return response()->json([
            'data' => [
                'total_states' => $totalStates,
                'active_states' => $activeStates,
                'total_members' => $totalMembers,
                'total_views' => $totalViews,
                'states_by_type' => $byType
            ]
        ]);
    }
}
