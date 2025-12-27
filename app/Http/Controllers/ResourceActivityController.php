<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Activity;
use App\Models\ResourceActivity;
use Illuminate\Support\Facades\Storage;

class ResourceActivityController extends Controller
{
    public function download(Request $request, ResourceActivity $resource)
    {
        if (!$resource) {
            return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
        }

        $filePath = storage_path('app/' . $resource->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['status' => 'error', 'message' => 'File not found'], 404);
        }

        // Increment download count
        $resource->increment('downloads_count');

        return response()->download($filePath, $resource->file_name, [
            'Content-Type' => $resource->mime_type,
        ]);
    }

    public function create (Request $request, Activity $activity)
    {
        $validator = Validator::make($request->all(), [
            'title'   => 'nullable|string|max:255',
            'file'    => 'nullable|array', // 10MB max
            'file.*'  => 'nullable|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        if (!$activity) {
            return response()->json(['status' => 'error', 'message' => 'Activity not found'], 404);
        }

        if ($activity->resource_activity()->count() >= 10) {
            return response()->json(['status' => 'error', 'message' => 'Maximum number of resources reached for this activity'], 400);
        }

        foreach ($request->file as $file) {
            $validated_resource = [];

            if ($file) {
                // Ajouter les données extraites du fichier aux données validées
                $validated_resource['file_name'] = $file->getClientOriginalName();
                $validated_resource['file_size'] = $file->getSize();
                $validated_resource['mime_type'] = $file->getMimeType();
                $validated_resource['extension'] = $file->getClientOriginalExtension();
                $validated_resource['file_type'] = Controller::determineFileType($validated_resource['mime_type'], $validated_resource['extension']);
            }

            $validated_resource = [
                'title' => $request->title,
                'file_type' => $request->file_type,
            ];

            $resource = $activity->resourceActivities()->create($validated_resource);

            // Stockage du fichier
            $path = $file->store('activity/' . $activity->id, 'public');
            $validated_resource['file_path'] = $path;
            $resource->update($validated_resource);

        }
    }

    public function delete(Request $request, ResourceActivity $resource)
    {
        if (!$resource) {
            return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
        }

        // Supprimer le fichier du stockage
        Storage::disk('public')->delete($resource->file_path);

        // Supprimer l'enregistrement de la base de données
        $resource->delete();

        return response()->json(['status' => 'success', 'message' => 'Resource deleted successfully'], 200);
    }
}
