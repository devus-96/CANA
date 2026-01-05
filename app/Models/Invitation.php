<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        "email",
        "token",
        "invited_by",
        "role_id",
        "expires_at",
        'accepted_at',
        "revoked_at",
    ];

    public function invited_by () {
        return $this->belongsTo(Admin::class, 'invited_by');
    }
}
