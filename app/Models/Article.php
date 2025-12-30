<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'article_image',
        'content',
        'excerpt',
        'slug',
        'status',
        'tags',
        'is_featured',
        'shares_count',
        'views_count',
        'likes_count',
        'published_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }
}
