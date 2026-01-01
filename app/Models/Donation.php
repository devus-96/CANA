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
        "method",
        "amount",
        "transaction_id",
        "status",
        "member_id",
        "transaction_id",
        "project_id"
    ];

    public function donor () {
        return $this->belongsTo(Member::class, 'donor_id');
    }

    public function project () {
        return $this->belondsTo(Project::class, 'project_id');
    }
}
