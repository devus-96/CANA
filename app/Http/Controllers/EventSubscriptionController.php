<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Rules\PhoneNumber;
use Carbon\Carbon;

use App\Models\Member;
use App\Models\Event;
use App\Models\EventSubscriptions;
use App\Http\Resources\EventSubscriptionResource;

class EventSubscriptionController extends Controller
{

    public function index (Event $event)
    {
        /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        if ($admin->isStateLiveManager() && !$admin->isAdmin()) {
            return response()->json(['statut' => 'error', 'message' => 'Accès non autorisé'], 403);
        }

        $subscriptions = EventSubscriptions::where('event_id', $event->id)
                        ->with('member')
                        ->with('admin')
                        ->get();

        return response()->json([
            'statut'  => 'success',
            'data' => new EventSubscriptionResource($subscriptions)
        ], 200);
    }

    public function show(Request $request, Event $event)
    {
        /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        // Vérification des droits (Simplifiée si le guard renvoie déjà un Member)
        if ($admin->isStateLiveManager() && !$admin->isAdmin()) {
            return response()->json(['statut' => 'error', 'message' => 'Accès non autorisé'], 403);
        }

        // Construction de la requête avec filtrage dynamique
        $subscriptions = EventSubscriptions::where('event_id', $event->id)
            ->when($request->status, function ($query, $status) {
                // Si ?status= est présent, on filtre. Sinon, on prend tout.
                return $query->where('status', $status);
            })
            ->with(['member', 'admin']) // Eager loading direct pour éviter N+1
            ->orderBy('created_at', 'desc')
            ->paginate(20); // Utiliser la pagination pour l'efficacité

        // On utilise collection() pour transformer une liste d'objets
        return EventSubscriptionResource::collection($subscriptions)
            ->additional(['statut' => 'success']);
    }

    public function store (Request $request, Event $event)
    {
        // Recuperer le membre authentifie s'il existe
        /** @var \App\Models\Member $member */
        $member = auth()->guard('member')->user();
        // s'il n'existe pas, valider les informations fournies
        if (!$member) {

            $validator = Validator::make($request->all(), [
                'name'    => 'string|required',
                'phone'   => ['required', 'string', 'unique:'.Member::class, new PhoneNumber],
                'email'   => 'required|string|lowercase|email|max:255|unique:'.Member::class
            ]);

            if ($validator->fails()) {
                return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
            }
            // Verifier si une souscription existe deja avec le meme phone pour cet evenement
            $subscription = EventSubscriptions::where('phone', $request->phone);

            if ($subscription) {
                return response()->json([
                    'message' => 'vous avez deja envoyer une souscription pour cette evenement'
                ], 400);
            }
            // Creer une nouvelle souscription
            $subscription = EventSubscriptions::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'event_id' => $event->id,
                'requested_at' => Carbon::now()
            ]);

            return response()->json(['message' => 'la souscription a ete envoyee'], 200);
            // Sinon, utiliser les informations du membre authentifie
        } else {
            // Verifier si une souscription existe deja pour cet evenement
            $subscription = EventSubscriptions::where('member_id', $member->id);

            if ($subscription) {
                return response()->json(['message' => 'vous avez deja envoyer une souscription pour cette evenement'], 400);
            } else {
                // si non Creer une nouvelle souscription
                 $member->event_subscription()->create([
                    'event_id' => $event->id,
                    'name'     => $member->first_name.' '.$member->last_name,
                    'phone'    => $member->phone,
                    'requested_at' => Carbon::now()
                 ]);

                 return response()->json(['message' => 'la souscription a ete envoyee'], 200);
            }
        }

    }

    public function update (Request $request, EventSubscriptions $subscription)
    {
        // Valider le statut
         /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        // Verifier les droits
        if ($admin->isStateLiveManager() && !$admin->isAdmin()) {
            return response()->json(['statut' => 'error', 'message' => 'Accès non autorisé'], 403);
        }
        // Valider le statut
        $subscription->update([
            'status' => $request->status,
            'reviewed_by' => $admin->id,
            'reviewed_at' => Carbon::now()
        ]);
        // Charger les relations pour la reponse
        $subscription->load('member');
        $subscription->load('admin');

        return response()->json([
            'statut'  => 'success',
            'data'    => new EventSubscriptionResource($subscription)
        ], 200);
    }

    public function delete (Request $request, EventSubscriptions $subscription)
    {
        /** @var \App\Models\Admin $admin */
        $admin = auth()->guard('admin')->user();

        if ($admin->isStateLiveManager() && !$admin->isAdmin()) {
            return response()->json(['statut' => 'error', 'message' => 'Accès non autorisé'], 403);
        }

        $subscription->delete();

        return response()->json(['statut'  => 'success'], 200);
    }
}
