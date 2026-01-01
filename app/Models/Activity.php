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
        "active",
        'activity_image',
        "responsable_id",
        "author_id",
        "category_id",
    ];

    public function medias()
    {
        return $this->hasMany(Media::class);
    }

    public function category()
    {
       return $this->morphOne(Category::class, 'categoryable');
    }

    public function responsable()
    {
        return $this->belongsTo(Admin::class, 'responsable_id');
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function events ()
    {
        return $this->hasMany(Event::class);
    }
}
