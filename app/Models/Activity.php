<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activitiy extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "image",
        "description",
        "objectif",
    ];
}
