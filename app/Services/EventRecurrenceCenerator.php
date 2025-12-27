<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventOccurrenceGenerator
{
    /**
     * Génère les occurrences d'un événement dans un intervalle
     */
    public function generateForEvent(
        Event $event,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $occurrences = collect();

        // Si événement non récurrent, une seule occurrence
        if (!$event->is_recurrent || !$event->recurrenceRule) {
            if ($event->start_date && $event->start_date->between($startDate, $endDate)) {
                $occurrences->push($this->createOccurrenceData($event, $event->start_date));
            }
            return $occurrences;
        }

        $rule = $event->recurrenceRule;
        $current = Carbon::parse($rule->start_date);
        $ruleEnd = $rule->end_date ? Carbon::parse($rule->end_date) : $endDate;

        // Commencer au plus tard entre startDate et start_date de la règle
        if ($current < $startDate) {
            $current = $startDate->copy();
        }

        $count = 0;
        $maxIterations = 1000; // Sécurité anti-boucle infinie

        while ($current <= $endDate && $current <= $ruleEnd && $count < $maxIterations) {
            if ($this->isValidOccurrence($current, $rule)) {
                $occurrences->push($this->createOccurrenceData($event, $current));
            }

            $current = $this->nextDate($current, $rule);
            $count++;
        }

        return $occurrences;
    }

    /**
     * Génère les occurrences pour TOUS les événements
     */
    public function generateAllEvents(Carbon $startDate, Carbon $endDate): Collection
    {
        $allOccurrences = collect();
        $events = Event::with('recurrenceRule')
            ->where('status', 'active')
            ->get();

        foreach ($events as $event) {
            $occurrences = $this->generateForEvent($event, $startDate, $endDate);
            $allOccurrences = $allOccurrences->merge($occurrences);
        }

        return $allOccurrences->sortBy('date');
    }

    /**
     * Vérifie si une date est valide selon la règle
     */
    private function isValidOccurrence(Carbon $date, $rule): bool
    {
        // Vérifier les exceptions
        if ($this->isExcludedDate($date, $rule)) {
            return false;
        }

        // Vérifier les jours de la semaine pour les règles hebdo
        if ($rule->type === 'weekly' && $rule->days_of_week) {
            $days = json_decode($rule->days_of_week, true);
            if (!in_array($date->dayOfWeek, $days)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcule la prochaine date
     */
    private function nextDate(Carbon $current, $rule): Carbon
    {
        return match($rule->type) {
            'daily' => $current->copy()->addDays($rule->interval),
            'weekly' => $current->copy()->addWeeks($rule->interval),
            'monthly' => $current->copy()->addMonths($rule->interval),
            'yearly' => $current->copy()->addYears($rule->interval),
            default => $current->copy()->addDay(),
        };
    }

    /**
     * Crée la structure de données d'une occurrence
     */
    private function createOccurrenceData(Event $event, Carbon $date): array
    {
        return [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'event_type' => $event->type,
            'date' => $date->toDateString(),
            'time' => $event->default_time,
            'available_spots' => $event->max_capacity - $this->countReservations($event->id, $date),
            'max_capacity' => $event->max_capacity,
            'location' => $event->location?->city,
            'description' => $event->description,
            'is_virtual' => true, // Important: généré à la volée
        ];
    }

    /**
     * Compte les réservations existantes pour cette date
     */
    private function countReservations(int $eventId, Carbon $date): int
    {
        return \App\Models\Reservation::where('event_id', $eventId)
            ->whereDate('event_date', $date)
            ->count();
    }

    private function isExcludedDate(Carbon $date, $rule): bool
    {
        if (empty($rule->exceptions)) {
            return false;
        }

        $exceptions = json_decode($rule->exceptions, true);

        if (empty($exceptions)) {
            return false;
        }

        $dateStr = $date->toDateString();

        // Vérifier les dates spécifiques
        if (in_array($dateStr, $exceptions)) {
            return true;
        }

        // Vérifier les plages de dates (format: "2024-12-24:2024-12-26")
        foreach ($exceptions as $exception) {
            if (strpos($exception, ':') !== false) {
                list($start, $end) = explode(':', $exception);
                if ($date->between(Carbon::parse($start), Carbon::parse($end))) {
                    return true;
                }
            }
        }

        return false;
    }
}
