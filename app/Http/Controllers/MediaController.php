<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    public function store (Request $request) {

        $admin = auth()->guard('admin')->user();

        Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:medias,slug|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string|max:1000',

            // Type de média
            'type' => ['required','string',
                Rule::in(['image', 'video', 'audio', 'document', 'archive', 'other'])
            ],

            // Fichier (si upload)
            'file' => 'required|file|max:102400', // 100MB max
            'alt_text' => 'nullable|string|max:255',

            // Informations sur le fichier (peuvent être extraites automatiquement)
            //'file_path' => 'nullable|string|max:500',
            //'file_name' => 'nullable|string|max:255',
            //'file_size' => 'nullable|integer|min:0',
            //'mime_type' => 'nullable|string|max:100',
            //'extension' => 'nullable|string|max:10',

            // Pour audio/vidéo
            'duration' => 'nullable|integer|min:0',

            // Métadonnées
            //'metadata' => 'nullable|array',

            // Relations
            'category_id' => 'nullable|integer|exists:categories,id',
            'author_id' => 'nullable|integer|exists:admins,id',

            // Visibilité et statut
            'is_public' => 'nullable|boolean',
            'status' => [
                'nullable',
                'string',
                Rule::in(['draft', 'published', 'private'])
            ],

            // Statistiques (normalement non modifiables manuellement)
            'downloads_count' => 'nullable|integer|min:0',
            'views_count' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Ajouter les données extraites du fichier aux données validées
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $validated['mime_type'] = $file->getMimeType();
            $validated['extension'] = $file->getClientOriginalExtension();
            $validated['type'] = $this->determineFileType($validated['mime_type'], $validated['extension']);

            // Stockage du fichier
            $path = $file->store('media/' . date('Y/m'), 'public');
            $validated['file_path'] = $path;
        }

         // Valeurs par défaut
        $validated['is_public'] = $validated['is_public'] ?? true;
        $validated['status'] = $validated['status'] ?? 'published';
        $validated['downloads_count'] = $validated['downloads_count'] ?? 0;
        $validated['views_count'] = $validated['views_count'] ?? 0;

        // Création du média
        $media = Media::create($validated);

        return response()->json([
            'message' => 'Média créé avec succès',
            'data' => new MediaResource($media)
        ], 201);

    }

    private function determineFileType(string $mimeType, string $extension): string
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $videoMimes = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'];
        $audioMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];

        if (in_array($mimeType, $imageMimes)) return 'image';
        if (in_array($mimeType, $videoMimes)) return 'video';
        if (in_array($mimeType, $audioMimes)) return 'audio';
        if (in_array($extension, $documentExtensions)) return 'document';
        if (in_array($extension, $archiveExtensions)) return 'archive';

        return 'other';
    }
}
