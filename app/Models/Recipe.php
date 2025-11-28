<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Direction;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'time',
    ];

    protected $casts = [
        'time' => 'integer',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recipe_tag', 'recipe_id', 'tag_id');
    }

    public function directions()
    {
        return $this->hasMany(Direction::class)->orderBy('sort_order');
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class)->orderBy('sort_order');
    }

    // NOTE: Use `tags()` to access related tags.
}
