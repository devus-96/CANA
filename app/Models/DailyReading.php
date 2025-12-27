<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyReading extends Model
{
     use HasFactory;

     protected $fillable = [
        'display_date',
        'verse',
        'meditation',
        'biblical_reference',
        'liturgical_category',
        'status',
        'author_id'
    ];

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

}
