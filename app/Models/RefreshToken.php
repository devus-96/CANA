<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RefreshToken extends Model
{
    protected $fillable = [
        "user_id",
        "token",
        "expired_at"
    ];

    public function categoryable(): MorphTo
    {
        return $this->morphTo();
    }
}
