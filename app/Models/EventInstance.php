<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventInstance extends Model
{
    use HasFactory;


    protected $fillable = [
        'event_id',
        'date',
        'start_time',
        'end_time',
        'available_spots'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Relation vers recurrenceRule via event
    public function recurrenceRule()
    {
        return $this->event->recurrence_rules;
    }
}
