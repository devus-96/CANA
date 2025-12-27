<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ActualityResource;
use App\Models\Actuality;
use App\Models\Category;

class ActualityController extends Controller
{
    public function view (Request $request) {

        try {
            $categorie = $request->input('categorie');
            $activity = $request->input('activity');

            if ($activity) {
                $actuality = Actuality::with(['admin', 'category'])
                                    ->where('activity_id', $activity)
                                    ->where('status', 'published')
                                    ->orderBy('created_at', 'desc');

                return response()->json([
                    'message' => "actuality has been created",
                    "data" => new ActualityResource($actuality::paginate(10))
                ], 200);
            }
            $actuality = Actuality::with(['admin', 'category'])
                                ->where('category_id', $categorie)
                                ->where('status', 'published')
                                ->orderBy('created_at', 'desc');

            return response()->json([
                'message' => "actuality has been created",
                "data" => new ActualityResource($actuality::paginate(10))
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'something went wrong'], 500);
        }
    }


    public function store (Request $request) {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'actuality_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => 'nullable|string|unique:actualities,slug',
                'category_id' => 'required|exists:categories,id',
                'actuality_id' => 'nullable|exists:actualities,id',
                'status' => 'in:draft,published,archived'
            ]);

            if ($validator->fails()) {
                return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
            }

            $actuality  = $admin->actuality()->create([
                'title' => $request->title,
                'content' => $request->content,
                'slug' => $request->slug,
                'category_id' => $request->category_id,
                'actuality_id' => $request->actuality_id,
                'status' => $request->status,
            ]);

            Controller::uploadImages(['actuality_image' => $request->actuality_image], $actuality, 'actuality_image');

            return response()->json([
                'message' => "actuality has been created",
                "data" => new ActualityResource($actuality->load(['admin', 'category']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'something went wrong'], 500);
        }
    }

    public function update (Request $request, Actuality $actuality) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'actuality_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => 'sometimes|nullable|string|unique:actualities,slug',
                'category_id' => 'sometimes|required|exists:categories,id',
                'status' => 'sometimes|required|in:draft,published,archived'
            ]);

            if ($validator->fails()) {
                return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
            }

            $actuality->update([
                'title' => $request->input('title', $actuality->title),
                'content' => $request->input('content', $actuality->content),
                'actuality_image' => $request->input('actuality_image', $actuality->actuality_image),
                'slug' => $request->input('slug', $actuality->slug),
                'category_id' => $request->input('category_id', $actuality->category_id),
                'status' => $request->input('status', $actuality->status),
            ]);

            Controller::uploadImages(['actuality_image' => $request->actuality_image], $actuality, 'actuality_image');

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
            $actuality->delete();

            return response()->json([
                'statut' => 'success',
                'message' => "reading has been deleted",
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'something went wrong'], 500);
        }
    }

    public function forceDestroy(Actuality $actuality)
    {
        // Supprime physiquement la ligne de la table
        $actuality->forceDelete();

        return response()->json(['message' => 'Suppression définitive réussie']);
    }

    public function restore(Actuality $actuality)
    {
        $actuality->restore();

        return response()->json(['message' => 'Élément restauré']);
    }
}
