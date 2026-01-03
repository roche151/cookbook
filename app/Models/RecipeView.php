<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeView extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'ip_address',
        'user_id',
        'viewed_at',
    ];

    public $timestamps = true;

    protected $dates = ['viewed_at'];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}