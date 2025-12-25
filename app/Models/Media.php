<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
     use SoftDeletes;

     protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'extension',
        'duration',
        'category_id',
        'author_id',
        'is_public',
        'status',
        'downloads_count',
        'views_count'
    ];
}
