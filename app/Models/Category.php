<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function categoryable(): MorphTo
    {
        return $this->morphTo();
    }
    public function activities(): MorphToMany
    {
        return $this->morphedByMany(Activity::class, 'categoryable');
    }

    /**
     * Relation polymorphique avec les actualités.
     */
    public function actualities(): MorphToMany
    {
        return $this->morphedByMany(Actuality::class, 'categoryable');
    }

    /**
     * Relation polymorphique avec les médias.
     */
    public function media(): MorphToMany
    {
        return $this->morphedByMany(Media::class, 'categoryable');
    }

    /**
     * Relation polymorphique avec les articles.
     */
    public function articles(): MorphToMany
    {
        return $this->morphedByMany(Article::class, 'categoryable');
    }
}
