<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategorieController extends Controller
{
    public function index (Request $request) {
        try {
            $request->validate([
                'page' => 'nullable|string|in:activity,actuality,media,article',
            ]);

            $query = Category::orderBy('name', 'desc');

            switch ($request->input('page')) {
                case 'activity':
                    $query->where('categoryable_type', 'App\Models\Activity');
                    break;
                case 'actuality':
                    $query->where('categoryable_type', 'App\Models\Actuality');
                    break;
                case 'media':
                    $query->where('categoryable_type', 'App\Models\Media');
                    break;
                case 'article':
                    $query->where('categoryable_type', 'App\Models\Article');
                    break;
            }

            return response()->json($query->get());
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
}
