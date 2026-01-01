<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        "name",
        "description",
        "objectif",
        "type",
        "max_capacity",
        "price",
        "event_image",
        "is_free",
        "is_recurrent",
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'activity_id',
        'author_id',
        'location_id'
    ];

    public function recurrence_rules () {
        return $this->hasOne(RecurrenceRule::class);
    }

    public function occurrences()
    {
        return $this->hasMany(EventInstance::class);
    }

    public function location () {
        return $this->belongsTo(Location::class);
    }

    public function activity () {
        return $this->belongsTo(Activity::class);
    }

    public function author () {
        return $this->belongsTo(Admin::class, 'author_id');
    }
}
