<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Actuality extends Model
{
      use HasFactory, SoftDeletes;

     protected $fillable = [
        'title',
        'content',
        'image',
        'status',
        'slug',
        'author_id',
        'category_id',
        'activity_id',
        'category_name',
    ];
}
