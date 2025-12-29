<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "image",
        "description",
        "objectif",
        "responsable_id",
        "author",
        "category_id"
    ];

    public function resource_activity()
    {
        return $this->hasMany(ResourceActivity::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Admin::class, 'responsable_id');
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author');
    }

    public function event ()
    {
        return $this->belongsToMany(Event::class, 'event_id');
    }
}
