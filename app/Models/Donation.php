<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "email",
        "phone",
        "is_anonymous",
        "dedication",
        "method",
        "amount",
        "transaction_id",
        "status",
        "member_id",
        "transaction_id"
    ];

    public function member () {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
