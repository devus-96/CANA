<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginConfirmation extends Model
{
    use HasFactory;

     protected $fillable = [
        "device",
        "email",
        "telephone",
        "code",
    ];
}
