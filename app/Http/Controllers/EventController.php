<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Event;
use App\Models\Location;
use App\Models\RecurrenceRule;

use App\Services\EventOccurrenceGenerator;
use Carbon\Carbon;

//Activité : "Retraites spirituelles" (permanent)
//Événements : "Retraite de Carême 2025" (15-17 mars), "Retraite de Pentecôte 2025" (8-10 juin)

class EventController extends Controller
{
    public function index(Request $request, EventOccurrenceGenerator $generator)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'event_type' => 'nullable|in:retreat,conference,prayer_group',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        // Dates par défaut: aujourd'hui → +3 mois
        $startDate = $validated['start_date']
            ? Carbon::parse($validated['start_date'])
            : now();

        $endDate = $validated['end_date']
            ? Carbon::parse($validated['end_date'])
            : $startDate->copy()->addMonths(3);

        // Générer les occurrences
        $occurrences = $generator->generateAllEvents($startDate, $endDate);

        // Filtrer par type si demandé
        if ($request->has('event_type')) {
            $occurrences = $occurrences->where('event_type', $request->event_type);
        }

        // Limiter si demandé
        if ($request->has('limit')) {
            $occurrences = $occurrences->take($request->limit);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'occurrences' => $occurrences->values(),
                'count' => $occurrences->count(),
                'generated_at' => now()->toDateTimeString(),
            ]
        ]);
    }

    public function store ()
    {
        $admin = auth()->guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'objectif'      => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'type'          => 'required|in:retreat,conference,prayer_group,mission,formation',
            'max_capacity'  => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'is_free'       => 'required|boolean',
            'is_recurrent'  => 'required|boolean',

            'city'          => 'required|string|max:100',
            'street'        => 'required|string|max:255',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'latitude'      => 'nullable|numeric|between:-90,90',

            // Règles récurrence (seulement si is_recurrent = true)
            'recurrence_type' => 'required_if:is_recurrent,true|in:daily,weekly,monthly,yearly',
            'interval'        => 'required_if:is_recurrent,true|integer|min:1',
            'start_date'      => 'required_if:is_recurrent,true|date|after_or_equal:today',
            'end_date'        => 'nullable|date|after:start_date',
            'days_of_week'    => 'nullable|array',
            'days_of_week.*'  => 'integer|between:0,6', // 0=Sunday
            'day_of_month'    => 'nullable|integer|between:1,31',
            'weeks_of_month'  => 'nullable|array',
            'weeks_of_month.*'=> 'integer|between:1,5',
            'exceptions'      => 'nullable|array', // Dates à exclure
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $event = $admin->events()->create([
                'name' => $request->name,
                'description' => $request->description,
                'objectif' => $request->objectif,
                'type' => $request->type,
                'max_capacity' => $request->max_capacity,
                'price' => $request->is_free ? 0 : $request->price, // Forcer à 0 si gratuit
                'is_free' => $request->is_free,
                'is_recurrent' => $request->is_recurrent,
                'status' => 'draft'
            ]);

            Controller::uploadImages(['event_image' => $request->image], $event, 'event_image');

             if ($request->is_recurrent) {
                $event->recurrence_rule()->create([
                    'recurrence_type' => $request->recurrence_type,
                    'interval' => $request->interval,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'days_of_week' => $request->days_of_week ? json_encode($request->days_of_week) : null,
                    'day_of_month' => $request->day_of_month,
                    'weeks_of_month' => $request->weeks_of_month ? json_encode($request->weeks_of_month) : null,
                    'exceptions' => $request->exceptions ? json_encode($request->exceptions) : null,
                ]);
            }

            $event->location()->create([
                'city' => $request->city,
                'street' => $request->street,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude
            ]);

            DB::commit();

            // Charger les relations pour le retour
            $event->load(['recurrence_rule', 'location']);

            return response()->json([
                'statut' => 'success',
                'message' => 'Événement créé avec succès',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function update (Request $request, Event $event) {

        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return response()->json(['statut' => 'error', 'message' => 'Authentication required'], 401);
        }

        $roleNames = $admin->roles()->pluck('name')->toArray();
        $isSuperAdmin = in_array(Controller::USER_ROLE_SUPER_ADMIN, $roleNames);
        $isCreator = $activity->admin_id === $admin->id;

        if (!$isSuperAdmin && !$isCreator) {
            return response()->json([
                'statut' => 'error',
                'message' => 'You are not authorized to delete this event'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'description'   => 'nullable|string|max:2000',
            'objectif'      => 'nullable|string|max:1000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'type'          => 'sometimes|in:retreat,conference,prayer_group,mission,formation',
            'max_capacity'  => 'sometimes|integer|min:1|max:10000',
            'price'         => 'sometimes|numeric|min:0',
            'is_free'       => 'sometimes|boolean',
            'is_recurrent'  => 'sometimes|boolean',
            'status'        => 'sometimes|in:0,1,2',

            'city'          => 'sometimes|string|max:100',
            'street'        => 'sometimes|string|max:255',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'latitude'      => 'nullable|numeric|between:-90,90',

            'recurrence_type' => 'required_if:is_recurrent,true|in:daily,weekly,monthly,yearly',
            'interval'        => 'required_if:is_recurrent,true|integer|min:1|max:365',
            'start_date'      => 'required_if:is_recurrent,true|date|after_or_equal:today',
            'end_date'        => 'nullable|date|after:start_date',
            'days_of_week'    => 'nullable|array|min:1|max:7',
            'days_of_week.*'  => 'integer|between:0,6',
            'day_of_month'    => 'nullable|integer|between:1,31',
            'weeks_of_month'  => 'nullable|array|min:1|max:5',
            'weeks_of_month.*'=> 'integer|between:1,5',
            'exceptions'      => 'nullable|array',
            'exceptions.*'    => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        DB::beginTransaction();

        try {
            $eventData = $request->only([
                'name',
                'description',
                'objectif',
                'type',
                'max_capacity',
                'price',
                'is_free',
                'is_recurrent',
                'status'
            ]);

            if (!empty($eventData)) {
                $event->update($eventData);
            }

            if ($request->hasAny(['city', 'street', 'longitude', 'latitude'])) {
                $locationData = $request->only(['city', 'street', 'longitude', 'latitude']);
                $event->location()->updateOrCreate(
                    ['event_id' => $event->id],
                    $locationData
                );
            }

            if ($request->boolean('is_recurrent')) {
                $recurrenceData = $request->only([
                    'recurrence_type',
                    'interval',
                    'start_date',
                    'end_date',
                    'days_of_week',
                    'weeks_of_month',
                    'exceptions'
                ]);

                // Convertir les arrays en JSON si nécessaire
                if (isset($recurrenceData['days_of_week'])) {
                    $recurrenceData['days_of_week'] = json_encode($recurrenceData['days_of_week']);
                }
                if (isset($recurrenceData['weeks_of_month'])) {
                    $recurrenceData['weeks_of_month'] = json_encode($recurrenceData['weeks_of_month']);
                }
                if (isset($recurrenceData['exceptions'])) {
                    $recurrenceData['exceptions'] = json_encode($recurrenceData['exceptions']);
                }

                $event->recurrenceRule()->updateOrCreate(
                    ['event_id' => $event->id],
                    $recurrenceData
                );
            } else {
                $event->recurrenceRule()->delete();
            }
            if ($request->hasFile('profile')) {
                Controller::uploadImages(['event_image' => $request->image], $event, 'event_image');
            }

             DB::commit();

            $event->load(['location', 'recurrenceRule']);

            return response()->json([
                'statut' => 'success',
                'message' => 'Événement mis à jour avec succès',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function delete (Request $request, Event $event)
    {
        $admin = auth()->guard('admin')->user();

        $roleNames = $admin->roles()->pluck('name')->toArray();
        $isSuperAdmin = in_array(Controller::USER_ROLE_SUPER_ADMIN, $roleNames);
        $isCreator = $activity->admin_id === $admin->id;

        if (!$isSuperAdmin && !$isCreator) {
            return response()->json([
                'statut' => 'error',
                'message' => 'You are not authorized to delete this event'
            ], 403);
        }

        $event->delete();

        return response()->json([
            'statut' => 'success',
            'message' => "Activity has been deleted",
        ], 200);
    }

}
