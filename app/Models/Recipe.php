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
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_recipe')
                    ->withTimestamps();
    }
}
