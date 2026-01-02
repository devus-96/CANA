<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventSubscriptions extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'event_id',
        'status',
        ''
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function event()
    {
        return $this->belongsTo(EventInstance::class, 'event_instances_id');
    }

    public function admin ()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
