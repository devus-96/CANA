<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Actuality extends Model
{
     use HasFactory;

     protected $fillable = [
        'title',
        'content',
        'image',
        'author_id',
        'status',
        'slug',
        'views_count',
        'shares_count',
        'likes_count'
    ];
}
