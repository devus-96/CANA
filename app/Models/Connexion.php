<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Connexion extends Model
{
    use HasFactory;

     protected $fillable = [
        'device',
        'ip_address',
        'city',
        'navigator',
        'expires_at',
        'token',
    ];


}
