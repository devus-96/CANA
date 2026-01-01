<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "name",
        "email",
        "phone",
        "code",
        "event_id",
        "ticket_type",
        "quantity",
        "participants",
        "price",
        "status",
        "event_date",
        "payment_id"
    ];

    public function transaction(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function member () {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function event () {
        return $this->belongsTo(EventInstance::class, 'event_ocurrence_id');
    }
}
