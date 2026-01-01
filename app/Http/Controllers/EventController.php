<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Event;
use App\Models\Location;
use Carbon\Carbon;
use App\Http\Resources\EventResource;
use App\Services\EventOccurrenceService;
use App\Models\EventInstance;

//Activité : "Retraites spirituelles" (permanent)
//Événements : "Retraite de Carême 2025" (15-17 mars), "Retraite de Pentecôte 2025" (8-10 juin)

class EventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'activity' => 'nullable|exists:activities,id',
                'status' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            // 1. On prépare la requête sur la table des instances (la réalité)
            // On charge la relation 'event' pour avoir le titre, description, etc.
            $query = EventInstance::with(['event.activity', 'event.location']);

            // 2. Filtre par dates (Obligatoire pour la performance)
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : now();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : $startDate->copy()->addMonths(3);

            $query->whereBetween('start_at', [$startDate, $endDate]);

            // 3. Filtre sur les propriétés de l'instance
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 4. Filtre sur les propriétés du "Master" Event (Prix, Activité)
            // On utilise whereHas pour filtrer selon la table parente 'events'
            if ($request->filled('activity')) {
                $query->whereHas('event', function($q) use ($request) {
                    $q->where('activity_id', $request->activity);
                });
            }

            if ($request->filled('min_price')) {
                $query->whereHas('event', function($q) use ($request) {
                    $q->where('price', '>=', $request->min_price);
                });
            }

            // 5. Exécution de la requête avec pagination ou limite
            $limit = $request->get('limit', 50);
            $occurrences = $query->orderBy('start_at', 'asc')->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => ['start' => $startDate->toDateTimeString(), 'end' => $endDate->toDateTimeString()],
                    'occurrences' => $occurrences,
                    'count' => $occurrences->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Event index error: ' . $e->getMessage());
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show (EventInstance $event_instance) {
        try {
            // Réponse JSON avec l'article demandé
            return response()->json([
                'message' => "article retrieved successfully",
                "data" => new EventResource($event_instance->load(['event.activity', 'event.location']))
            ], 200);
        } catch (\Exception $e) {
            Log::error('Event show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve eveent',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store (Request $request, EventOccurrenceService $occurenceGenerator)
    {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            Validator::make($request->all(), [
                'name'          => 'required|string|max:255',
                'description'   => 'nullable|string',
                'objectif'      => 'nullable|string',
                'event_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'type'          => 'required|in:retreat,conference,prayer_group,mission,formation',
                'max_capacity'  => 'required|integer|min:1',
                'price'         => 'required|numeric|min:0',
                'is_free'       => 'required|boolean',
                'is_recurrent'  => 'required|boolean',
                'start_date'    => 'required|date|after_or_equal:today',
                'end_date'      => 'nullable|date|after:start_date',
                'start_time'    => 'required|date_format:H:i',
                'end_time'      => 'nullable|date_format:H:i',

                'city'          => 'required|string|max:100',
                'street'        => 'required|string|max:255',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'latitude'      => 'nullable|numeric|between:-90,90',

                // Règles récurrence (seulement si is_recurrent = true)
                'recurrence_type' => 'required_if:is_recurrent,true|in:daily,weekly,monthly,yearly',
                'interval'        => 'required_if:is_recurrent,true|integer|min:1',
                'days_of_week'    => 'nullable|array',
                'days_of_week.*'  => 'integer|between:0,6', // 0=Sunday
                'day_of_month'    => 'nullable|integer|between:1,31',
                'weeks_of_month'  => 'nullable|array',
                'weeks_of_month.*'=> 'integer|between:1,5',
                'exceptions'      => 'nullable|array', // Dates à exclure
            ]);

            try {
                DB::beginTransaction();
                $location = Location::create([
                    'city' => $request->city,
                    'street' => $request->street,
                    'longitude' => $request->longitude,
                    'latitude' => $request->latitude
                ]);
                // create event
                $event = $admin->events()->create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'objectif' => $request->objectif,
                    'type' => $request->type,
                    'max_capacity' => $request->max_capacity,
                    'price' => $request->is_free ? 0 : $request->price, // Forcer à 0 si gratuit
                    'is_free' => $request->is_free,
                    'is_recurrent' => $request->is_recurrent,
                    'location_id' => $location->id
                ]);
                // upload de la baniere de l'evenement
                Controller::uploadImages(['event_image' => $request->image], $event, 'event_image');
                // si les recurrences sont actives
                if ($request->is_recurrent) {
                    $event->recurrence_rule()->create([
                        'recurrence_type' => $request->recurrence_type,
                        'interval' => $request->interval,
                        'end_date' => $request->end_date,
                        'days_of_week' => $request->days_of_week ? json_encode($request->days_of_week) : null,
                        'day_of_month' => $request->day_of_month,
                        'weeks_of_month' => $request->weeks_of_month ? json_encode($request->weeks_of_month) : null,
                        'exceptions' => $request->exceptions ? json_encode($request->exceptions) : null,
                    ]);
                }
                  // Générer les occurrences futures
                $occurenceGenerator->generateEventOccurrences($event);

                 DB::commit();

                // Charger les relations pour le retour
                $event->load(['recurrence_rule', 'location']);

                return response()->json([
                    'statut' => 'success',
                    'message' => 'Événement créé avec succès',
                    'data' => $event
                ], 201);

            }  catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Failed to create event',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Event store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create event',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update (Request $request, Event $event, EventOccurrenceService $occurenceGenerator) {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            $validator = Validator::make($request->all(), [
                'name'          => 'sometimes|string|max:255',
                'description'   => 'sometimes|nullable|string|max:2000',
                'objectif'      => 'sometimes|nullable|string|max:1000',
                'event_image'   => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'type'          => 'sometimes|in:retreat,conference,prayer_group,mission,formation',
                'max_capacity'  => 'sometimes|integer|min:1|max:10000',
                'price'         => 'sometimes|numeric|min:0',
                'is_free'       => 'sometimes|boolean',
                'is_recurrent'  => 'sometimes|boolean',
                'end_date'      => 'sometimes|nullable|date|after:start_date',
                'start_time'    => 'sometimes|date_format:H:i',
                //
                'city'          => 'sometimes|string|max:100',
                'street'        => 'sometimes|string|max:255',
                'longitude'     => 'sometimes|nullable|numeric|between:-180,180',
                'latitude'      => 'sometimes|nullable|numeric|between:-90,90',
                 // Règles récurrence (seulement si is_recurrent = true)
                'recurrence_type' => 'required_if:is_recurrent,true|in:daily,weekly,monthly,yearly',
                'interval'        => 'required_if:is_recurrent,true|integer|min:1',
                'days_of_week'    => 'nullable|array',
                'days_of_week.*'  => 'integer|between:0,6', // 0=Sunday
                'day_of_month'    => 'nullable|integer|between:1,31',
                'weeks_of_month'  => 'nullable|array',
                'weeks_of_month.*'=> 'integer|between:1,5',
                'exceptions'      => 'nullable|array', // Dates à exclure
            ]);
            //validated data
            $validatedData = $validator->validated();

            // Gestion de l'image
            if ($request->hasFile('event_image')) {
                Controller::uploadImages(['event_image' => $request->image], $event, 'event_image');
            }

            // Mise à jour de la localisation
            if ($request->hasAny(['city', 'street', 'longitude', 'latitude'])) {
                $locationData = $request->only(['city', 'street', 'longitude', 'latitude']);
                // CORRECTION: Vérifier si la relation existe
                if ($event->location) {
                    $event->location()->update($locationData);
                } else {
                    $event->location()->create($locationData);
                }
            }

            // Mise à jour des données de base de l'événement
            $eventData = $request->only([
                'name', 'description', 'objectif', 'type', 'max_capacity',
                'price', 'is_free', 'is_recurrent', 'start_date', 'end_date', 'start_time'
            ]);

            $event->update($eventData);

            $recurrenceData = $request->only([
                'recurrence_type','interval','days_of_week','weeks_of_month','day_of_month','exceptions'
            ]);

            $isRecurrent = $request->is_recurrent ?? $event->is_recurrent;

            $recurrenceData = $request->only([
                'recurrence_type', 'interval', 'days_of_week',
                'weeks_of_month', 'day_of_month', 'exceptions'
            ]);
            // si les dommees de recurrences sont disponibles et is_recurent est false
             if ($isRecurrent && !$event->is_recurrent) {
                // creer la regle de recurrence
               $event->recurrence_rule()->create([
                    'recurrence_type' => $request->recurrence_type,
                    'interval' => $request->interval,
                    'end_date' => $request->end_date,
                    'days_of_week' => $request->days_of_week ? json_encode($request->days_of_week) : null,
                    'day_of_month' => $request->day_of_month,
                    'weeks_of_month' => $request->weeks_of_month ? json_encode($request->weeks_of_month) : null,
                    'exceptions' => $request->exceptions ? json_encode($request->exceptions) : null,
                ]);
                // Générer les occurrences futures
                $occurenceGenerator->generateEventOccurrences($event);

            } else if ($isRecurrent && $event->is_recurrent && !empty(array_filter($recurrenceData))) {
                 EventInstance::where('event_id', $event->id)
                            ->where('start_time', '>', now())
                            ->delete();
                // Mettre à jour la règle
                if ($event->recurrence_rule) {
                    $event->recurrence_rule()->update([
                        'recurrence_type' => $request->recurrence_type,
                        'interval' => $request->interval,
                        'end_date' => $request->end_date,
                        'days_of_week' => $request->days_of_week ? json_encode($request->days_of_week) : null,
                        'day_of_month' => $request->day_of_month,
                        'weeks_of_month' => $request->weeks_of_month ? json_encode($request->weeks_of_month) : null,
                        'exceptions' => $request->exceptions ? json_encode($request->exceptions) : null,
                    ]);
                }

                // Régénérer les occurrences
                $occurenceGenerator->generateEventOccurrences($event);

            } else if (!$isRecurrent && $event->is_recurrent) {
                EventInstance::where('event_id', $event->id)
                            ->where('start_time', '>', now())
                            ->delete();

                $event->recurrence_rule()->delete();
            }
            // Changement de date de fin
            if ($request->has('end_date') && $request->end_date != $event->getOriginal('end_date')) {
                $newEndDate = Carbon::parse($request->end_date);
                $oldEndDate = Carbon::parse($event->getOriginal('end_date'));

                if ($newEndDate->lt($oldEndDate)) {
                    EventInstance::where('event_id', $event->id)
                        ->where('start_date', '>', $newEndDate)
                        ->delete();
                }
            }

            // Changement de date de début - CORRECTION: "start_date" pas "stat_date"
            if ($request->has('start_date') && $request->start_date != $event->getOriginal('start_date')) {
                $newStartDate = Carbon::parse($request->start_date);
                $oldStartDate = Carbon::parse($event->getOriginal('start_date'));

                if ($newStartDate->gt($oldStartDate) && !$oldStartDate->isPast()) {
                    EventInstance::where('event_id', $event->id)
                        ->where('start_date', '<', $newStartDate)
                        ->delete();
                }
            }
            // Rafraîchir l'événement avec ses relations
            $event->load(['location', 'recurrence_rule', 'occurrences']);

            return response()->json([
                'statut' => 'success',
                'message' => 'Événement mis à jour avec succès',
                'data' => $event
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Event store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create event',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy (Request $request, Event $event)
    {
        try {
            if ($event->trashed()) {
                // Suppression définitive
                // Supprimer l'image associée
                if ($event->activity_image) {
                    Storage::disk('public')->delete($event->event_image);
                }

                $event->forceDelete();

                return response()->json(['message' => 'Suppression définitive réussie'], 200);
            } else {
                $event->delete();

                return response()->json([
                    'statut' => 'success',
                    'message' => "Event has been deleted",
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Event force destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to permanently delete event',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function restore(Event $event)
    {
        try {
            if (!$event || !$event->trashed()) {
                return response()->json(['message' => 'L\'élément n\'est pas supprimé'], 400);
            }

            $event->restore();

            return response()->json([
                    'message' => "Activity has been restored",
                    "data" => $event->load(['location', 'recurrence_rule', 'occurrences'])
            ], 200);
        } catch (\Exception $e) {
            Log::error('Event restore error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore event',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

    }

    public function trashed ()
    {
        $event = Event::onlyTrashed()
            ->with(['location', 'recurrence_rule', 'occurrences'])
            ->paginate(15);

        return response()->json([
            'data' => $event
        ]);
    }

}
