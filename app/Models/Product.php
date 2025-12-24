<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
     use HasFactory, SoftDeletes;

     protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_at_price',
        'cost_price',
        'stock_quantity',
        'low_stock_threshold',
        'stock_status',
        'manage_stock',
        'category_id',
        'main_image',
        'images',
        'is_featured',
        'is_active',
        'is_digital',
        'weight',
        'requires_shipping',
        'meta_title',
        'meta_description',
        'views_count',
        'sales_count'
    ];

}
