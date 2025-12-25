<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class StateOfLive extends Model
{
    use HasFactory;

     protected $fillable = [
        "name",
        "slug",
        "image",
        "responsable_id",
        "active",
        "ordre"
    ];

}
