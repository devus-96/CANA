<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventSubscriptions extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'event_id'
    ];
}
