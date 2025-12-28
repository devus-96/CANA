<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
     use HasFactory, SoftDeletes;

     protected $fillable = [
        'title',
        'slug',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'extension',
        'duration',
        'category_id',
        'author_id',
        'activity_id',
        'is_public',
        'status',
        'downloads_count',
        'views_count',
        'share_count'
    ];
}
