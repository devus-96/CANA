<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurrenceRule extends Model
{
     use HasFactory;

     protected $fillable = [
        "type_recurrence",
        "interval",
        "start_date'",
        "end_date",
        "days_week",
        "day_of_the_month",
        "weeks_of_the_month",
        "exceptions"
    ];
}
