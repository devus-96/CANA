<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\EventInstance;
use Illuminate\Support\Facades\Log;

class EventOccurrenceService {
    /**
 * Génère toutes les occurrences futures d'un événement récurrent
 */
public function generateEventOccurrences(Event $event): void
{
    if (!$event->is_recurrent || !$event->recurrenceRule) {
        return;
    }

    $occurrences = [];
    $rule = $event->recurrenceRule;
    $start = Carbon::parse($event->start_date);
    $end = $event->end_date
        ? Carbon::parse($rule->event)
        : $start->copy()->addMonths(6); // Par défaut: 6 mois max

    // Calculer le nombre maximal d'occurrences théoriques
    $maxOccurrences = $this->calculateMaxOccurrences($start, $end, $rule);

    // Limite de sécurité pour CANA (éviter boucles infinies)
    $limit = min($maxOccurrences, 200); // Max 200 occurrences

    $count = 0;
    $current = $start;

    // Si événement non récurrent, une seule occurrence
    if (!$event->is_recurrent || !$event->recurrenceRule) {
            $occurrences = $this->createOccurrenceData($event, $event->start_date);
            EventInstance::insert($occurrences);
            return;
    }

    while ($current <= $end && $count < $limit) {
        // Vérifier si cette date n'est pas exclue
        if (!$this->isExcludedDate($current, $rule)) {
            $occurrences[] = $this->createOccurrenceData($event, $current);;
            $count++;
        }

        // Calculer la prochaine date
        $current = $this->calculateNextDate($current, $rule);
    }

    if (!empty($occurrences)) {
        EventInstance::insert($occurrences);

        Log::info('Event occurrences generated', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'occurrences_count' => count($occurrences)
        ]);
    }
}

private function createOccurrenceData (Event $event, Carbon $date) {
    return [
            'event_id' => $event->id,
            'date' => $date->toDateString(),
            'start_time' => $event->start_time ?? '00:00:00',
            'end_time' => $event->end_time ?? null,
            'available_spots' => $event->max_capacity,
            'location_id' => $event->location_id
        ];
}

/**
 * Calcule le nombre maximal théorique d'occurrences
 */
private function calculateMaxOccurrences(Carbon $start, Carbon $end, $rule): int
{
    $daysBetween = $start->diffInDays($end);

    return match($rule->recurrence_type) {
        'daily' => floor($daysBetween / ($rule->interval ?? 1)) + 1,
        'weekly' => floor($daysBetween / (($rule->interval ?? 1) * 7)) + 1,
        'monthly' => $start->diffInMonths($end) + 1,
        'yearly' => $start->diffInYears($end) + 1,
        default => 52, // Par défaut: hebdo = max 52 par an
    };
}

/**
 * Calcule la prochaine date selon la règle de récurrence
 */
private function calculateNextDate(Carbon $current, $rule): Carbon
{
    return match($rule->recurrence_type) {
        'daily' => $current->copy()->addDays($rule->interval ?? 1),
        'weekly' => $this->calculateWeeklyNextDate($current, $rule),
        'monthly' => $this->calculateMonthlyNextDate($current, $rule),
        'yearly' => $current->copy()->addYears($rule->interval ?? 1),
        default => $current->copy()->addWeek(),
    };
}

/**
 * Calcule la prochaine date pour une récurrence hebdomadaire
 */
private function calculateWeeklyNextDate(Carbon $current, $rule): Carbon
{
    $daysOfWeek = json_decode($rule->days_of_week ?? '[]', true);

    // Si pas de jours spécifiques, simple intervalle hebdo
    if (empty($daysOfWeek)) {
        return $current->copy()->addWeeks($rule->interval ?? 1);
    }

    // Chercher le prochain jour spécifié
    $nextDate = $current->copy()->addDay();
    $daysChecked = 0;

    while ($daysChecked < 7) {
        if (in_array($nextDate->dayOfWeek, $daysOfWeek)) {
            return $nextDate;
        }
        $nextDate->addDay();
        $daysChecked++;
    }

    // Si pas trouvé (normalement impossible), avancer d'une semaine
    return $current->copy()->addWeeks($rule->interval ?? 1);
}

/**
 * Calcule la prochaine date pour une récurrence mensuelle
 */
private function calculateMonthlyNextDate(Carbon $current, $rule): Carbon
{
    $nextDate = $current->copy()->addMonths($rule->interval ?? 1);

    // Si jour du mois spécifié (ex: chaque 15 du mois)
    if ($rule->day_of_month) {
        $nextDate->day($rule->day_of_month);
    }

    // Si semaine du mois spécifiée (ex: chaque 3ème mercredi)
    if (!empty($rule->weeks_of_month) && !empty($rule->days_of_week)) {
        $weeks = json_decode($rule->weeks_of_month, true);
        $days = json_decode($rule->days_of_week, true);

        if (!empty($weeks) && !empty($days)) {
            $nextDate = $this->calculateMonthlyByWeekAndDay($nextDate, $weeks, $days);
        }
    }

    return $nextDate;
}

/**
 * Calcule les dates mensuelles par semaine et jour (ex: 3ème mercredi)
 */
private function calculateMonthlyByWeekAndDay(Carbon $date, array $weeks, array $days): Carbon
{
    $year = $date->year;
    $month = $date->month;
    $foundDate = null;

    // Pour chaque semaine demandée
    foreach ($weeks as $week) {
        // Pour chaque jour demandé
        foreach ($days as $day) {
            $candidate = $this->getNthWeekdayOfMonth($year, $month, $week, $day);

            if ($candidate && $candidate->gte($date)) {
                if (!$foundDate || $candidate->lt($foundDate)) {
                    $foundDate = $candidate;
                }
            }
        }
    }

    return $foundDate ?: $date->copy()->addMonths(1);
}

/**
 * Trouve le N-ième jour de la semaine dans un mois
 * Ex: 3ème mercredi (week=3, day=3)
 */
private function getNthWeekdayOfMonth(int $year, int $month, int $nth, int $weekday): ?Carbon
{
    if ($nth < 1 || $nth > 5 || $weekday < 0 || $weekday > 6) {
        return null;
    }

    // Premier jour du mois
    $date = Carbon::create($year, $month, 1);

    // Aller au premier [weekday] du mois
    if ($date->dayOfWeek !== $weekday) {
        $date->next($weekday);
    }

    // Aller à la N-ième occurrence
    $date->addWeeks($nth - 1);

    // Vérifier qu'on est toujours dans le même mois
    return $date->month === $month ? $date : null;
}

/**
 * Vérifie si une date est exclue par les exceptions
 */
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
