<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RefreshToken extends Model
{
    protected $fillable = [
        "user_id",
        "token",
        "expired_at"
    ];
}
