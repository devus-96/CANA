<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Models\Member;
use App\Models\Event;
use App\Models\EventSubscriptions;
use App\Rules\PhoneNumber;

class EventSubscriptionController extends Controller
{
    public function store (Request $request, Event $event)
    {
        $member = auth()->guard('menber')->user();

        if (!$member) {
            $subscription = EventSubscriptions::where('member_id', $menber->id);

            $validator = Validator::make($request->all(), [
                'name'    => 'string|required',
                'phone'   => ['required', 'string', 'unique:'.Member::class, new PhoneNumber],
                'email'   => 'required|string|lowercase|email|max:255|unique:'.Member::class
            ]);

            if ($validator->fails()) {
                return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
            }

            $subscription = EventSubscriptions::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'event_id' => $event->id
            ]);

            return response()->json(['message' => 'la souscription a ete envoyee'], 200);
        } else {
            $subscription = EventSubscriptions::where('member_id', $menber->id);

            if ($subscription) {
                return response()->json(['message' => 'vous avez deja envoyer une souscription pour cette evenement'], 400);
            } else {
                 $menber->event_subscription()->create([
                    'event_id' => $event->id
                 ]);

                 return response()->json(['message' => 'la souscription a ete envoyee'], 200);
            }
        }

    }

    public function update (Request $request, EventSubscriptions $subscription)
    {
        $admin = auth()->guard('admin')->user();

        if ($admin->isStateLiveManager() && !$admin->isAdmin()) {
            return response()->json(['statut' => 'error', 'message' => 'Accès non autorisé'], 403);
        }

        $subscription->update([
            'status' => $request->status
        ]);

        return response()->json(['statut'  => 'success'], 200);
    }

    public function delete (Request $request, EventSubscriptions $subscription)
    {
        $admin = auth()->guard('admin')->user();

        if ($admin->isStateLiveManager() && !$admin->isAdmin()) {
            return response()->json(['statut' => 'error', 'message' => 'Accès non autorisé'], 403);
        }

        $subscription->delete();

        return response()->json(['statut'  => 'success'], 200);
    }
}
