<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StateOfLive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StateOfLiveContentController extends Controller
{
    public function index (Request $request) {
        try {
            $stateOfLive = StateOfLive::orderBy('name', 'desc')->get();

            return response()->json([
                'message' => "list of activities",
                'data' => $stateOfLive,
            ], 200);

        } catch (\Exception $e) {
             Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
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
                'name'  => 'required|string|max:255',
                'slug' => 'nullable|string|unique:articles,slug',
                'description' => 'required|string',
                'short_description' => 'required|string',
                'membership_criteria' => 'requierd|string',
                'values'   => 'requierd|string',
                'type'  => 'required|string|in:AGE_GROUP,MARITAL_STATUS,VOCATION,CONSECRATION,COMMITMENT,FRATERNITY,RELATED_COMMUNITY',
                'active' => 'nullable|boolean',
                'stateoflive_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                // foreign table validations
                'responsable' => 'required|exists:admins,id'
            ]);
            // Création de l'article
            $stateOfLive = StateOfLive::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'membership_criteria' => $request->membership_criteria,
                'values'    => $request->values,
                'type'  => $request->type,
                'active' => $request->input('active'),
                //foriegn key
                'author'    => $admin->id,
                'responsable' => $request->responsable
            ]);
            // Gestion du téléchargement de l'image
            Controller::uploadImages(['stateoflive_image' => $request->stateoflive_image], $stateOfLive, 'stateoflive_image');
             // Réponse JSON avec l'article créé
            return response()->json([
                    'message' => "article has been created",
                    "data" => $stateOfLive
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

    public function update (Request $request, StateOfLive $state_of_live) {
        try {
            $validator = Validator::make($request->all(), [
                'name'  => 'sometimes|required|string|max:255',
                'slug' => 'nullable|string|unique:articles,slug,' . $state_of_live->id,
                'description' => 'sometimes|required|string',
                'short_description' => 'sometimes|required|string',
                'membership_criteria' => 'sometimes|required|string',
                'values'   => 'sometimes|required|string',
                'type'  => 'sometimes|required|string|in:AGE_GROUP,MARITAL_STATUS,VOCATION,CONSECRATION,COMMITMENT,FRATERNITY,RELATED_COMMUNITY',
                'active' => 'nullable|boolean',
                'stateoflive_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                // foreign table validations
                'responsable' => 'sometimes|required|exists:admins,id'
            ]);

           $validated = $validator->validated();

           $state_of_live->update($validated);

            return response()->json([
                'message' => 'State of life updated successfully',
                'data' => $state_of_live->fresh() // Recharge les données fraîches depuis la base
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

    public function destroy (StateOfLive $state_of_live) {

    }

    public function forceDestroy () {

    }

    public function restore () {

    }
}
