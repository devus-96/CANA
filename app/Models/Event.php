<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "objectif",
        "type",
        "max_capacity",
        "price",
        "image",
        "free",
        "recupent"
    ];

    public function recurrence_rule () {
        return $this->hasMany(RecurrenceRule::class);
    }

    public function location () {
        return $this->hasMany(Location::class);
    }
}
