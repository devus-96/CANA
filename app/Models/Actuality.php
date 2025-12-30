<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actuality extends Model
{
      use HasFactory, SoftDeletes;

     protected $fillable = [
        'title',
        'content',
        'actuality_image',
        'status',
        'slug',
        'excerpt',
        'published_at',
        'likes_count',
        'shares_count',
        'views_count',
        // foreign key
        'author_id',
        'category_id',
        'activity_id',
    ];

    public function author (): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function category (): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function activity (): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
