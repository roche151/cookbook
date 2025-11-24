<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'body',
        'sort_order',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
