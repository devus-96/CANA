<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'phone',
        'biographie',
        'parish',
        'profile'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    public function connexion () {
        return $this->hasMany(Connexion::class);
    }

    public function activity () {
        return $this->hasMany(Activity::class);
    }

    public function event () {
        return $this->hasMany(Event::class);
    }


    public function daily_reading () {
        return $this->hasMany(DailyReading::class);
    }

    public function actuality () {
        return $this->hasMany(Actuality::class);
    }

    public function media () {
        return $this->hasMany(Media::class);
    }

    public function role () {
        return $this->belongsTo(Role::class);
    }

    public function articles () {
        return $this->hasMany(Article::class, 'author_id');
    }
     /**
     * Vérifier si l'admin est Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(\App\Http\Controllers\Controller::USER_ROLE_SUPER_ADMIN);
    }

    public function isStateLiveManager (): bool
    {
         return $this->hasRole(\App\Http\Controllers\Controller::USER_ROLE_STATELIVEMANAGER);
    }

    public function isAdmin (): bool
    {
         return $this->hasRole(\App\Http\Controllers\Controller::USER_ROLE_ADMIN);
    }


}
