<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
     use HasFactory, Notifiable;

     protected $fillable = [
        'name',
        'password',
        'gender',
        'date_of_birth',
        'city',
        'email',
        'phone',
        'parish',
        'profile'
    ];

    public function reservation ()
    {
        return $this->hasMany(Reservation::class);
    }

    public function event_subscription ()
    {
        return $this->hasMany(EventSubscriptions::class);
    }

    public function refresh_token ()
    {
         return $this->morphMany(RefreshToken::class, 'refreshable');

    }
}
