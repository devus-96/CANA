<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceActivity extends Model
{
    protected $fillable = [
        "title",
        "file_type",
        "file_path",
        "file_name",
        "file_size",
        "mime_type",
        "extension",
        "downloads_count"
    ];
}
