<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "image",
        "description",
        "objectif",
    ];

    public function resource_activity()
    {
        return $this->hasMany(ResourceActivity::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Admin::class, 'responsable_id');
    }

    public function event ()
    {
        return $this->belongsToMany(Event::class, 'event_id');
    }
}
