<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Media;

class MemberActionController extends Controller
{
     public function download(Request $request, Media $resource)
    {
        /** @var \App\Models\Member $member */
        $member = auth()->guard('member')->user();

        if ($member) {
            return response()->json(['status' => 'error', "message" => 'unauthorized'], 403);
        }

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

    public function incrementViews(Request $request, int $id)
    {
        /** @var \App\Models\Member $member */
        $member = auth()->guard('member')->user();
        // verifier si c un utilisateur connecter, si non erreur
        if ($member) {
            return response()->json(['status' => 'error', "message" => 'unauthorized'], 403);
        }
        // validate data
        $request->validate([
            'type' => 'required|in:media,article,actuality'
        ]);
        // chercher la ressource
        $resource = null;

        switch($request->type) {
            case 'article':
                $resource = \App\Models\Article::where('id', $id);
                break;
            case 'media';
                $resource = \App\Models\Media::where('id', $id);
                break;
            case 'actuality':
                $resource = \App\Models\Actuality::where('id', $id);
                break;
        }
        // si elle n'existe pas : erreur
        if (!$resource) {
            return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
        }
        // Increment views count
        $resource->increment('views_count');
        //  envoyer la réponse avec les vues
        return response()->json([
            'status' => 'success',
            'message' => 'View counted',
            'views_count' => $resource->views_count
        ]);
    }

    public function incrementLikes (Request $request, int $id) {
         if ($request->get('type')) return response()->json(['status' => 'error', 'message' => 'type not found'], 400);
        // chercher la ressource
        $resource = null;

        switch($request->get('type')) {
            case 'article':
                $resource = \App\Models\Article::where('id', $id);
                break;
            case 'media';
                $resource = \App\Models\Media::where('id', $id);
                break;
            case 'actuality':
                $resource = \App\Models\Actuality::where('id', $id);
                break;
        }
        if (!$resource) {
            return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
        }
        // Increment shares count
        $resource->increment('likes_count');

        return response()->json([
            'status' => 'success',
            'message' => 'like counted',
            'shares_count' => $resource->like_count
        ]);
    }

    public function incrementShares(Request $request, int $id)
    {
        /** @var \App\Models\Member $member */
        $member = auth()->guard('member')->user();
        // verifier si c un utilisateur connecter, si non erreur
        if ($member) {
            return response()->json(['status' => 'error', "message" => 'unauthorized'], 403);
        }
        if ($request->get('type')) return response()->json(['status' => 'error', 'message' => 'type not found'], 400);
        // chercher la ressource
        $resource = null;

        switch($request->get('type')) {
            case 'article':
                $resource = \App\Models\Article::where('id', $id);
                break;
            case 'media';
                $resource = \App\Models\Media::where('id', $id);
                break;
            case 'actuality':
                $resource = \App\Models\Actuality::where('id', $id);
                break;
        }
        // si elle n'existe pas : erreur
        if (!$resource) {
            return response()->json(['status' => 'error', 'message' => 'Resource not found'], 404);
        }
        // Increment shares count
        $resource->increment('shares_count');

        return response()->json([
            'status' => 'success',
            'message' => 'Share counted',
            'shares_count' => $resource->shares_count
        ]);
    }
}
