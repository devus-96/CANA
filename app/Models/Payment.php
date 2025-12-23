<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    protected $fillable = [
        "method",
        "amount",
        "status",
        "phone"
    ];

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class);
    }
}
