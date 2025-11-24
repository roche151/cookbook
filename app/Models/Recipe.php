<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'category',
        'image',
        'time',
        'rating',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
