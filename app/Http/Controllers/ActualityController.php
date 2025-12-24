<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ActualityResource;
use App\Http\Model\Actuality;
use App\Http\Model\Category;

class ActualityController extends Controller
{
    public function view (Request $request) {

        try {
            $categorie_name = $request->input('categorie');

            $categorie_id = Category::where('name',$categorie_name);

            $actuality = Actuality::with(['admin', 'category'])
                                ->where('category_id', $categorie_id)
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
            $admin = auth()->guard('admin')->user();

            Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'actuality_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'slug' => 'nullable|string|unique:actualities,slug',
                'category_id' => 'required|exists:categories,id',
                'status' => 'in:draft,published,archived'
            ]);

            if ($validator->fails()) {
                return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
            }

            $actuality  = $admin->actuality()->create([
                'title' => $request->title,
                'content' => $request->content,
                'actuality_image' => $request->actuality_image,
                'slug' => $request->slug,
                'category_id' => $request->category_id,
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => "actuality has been created",
                "data" => new ActualityResource($actuality->load(['admin', 'category']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'something went wrong'], 500);
        }
    }

    public function update (Request $request, Actuality $actualite) {
        try {
            Validator::make($request->all(), [
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

            $actuality = $validator->validated();

            $recurrenceData = $request->only([
                    'title',
                    'content',
                    'image',
                    'slug',
                    'category_id',
                    'status'
            ]);

            if (!empty($actuality)) {
                $actuality = $dailingReading->update($recurrenceData);
            }

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
