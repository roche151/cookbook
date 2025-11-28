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

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

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

    public function ratings()
    {
        return $this->hasMany(RecipeRating::class);
    }

    /**
     * Get the average rating for the recipe.
     */
    public function averageRating(): ?float
    {
        $avg = $this->ratings()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get the total number of ratings.
     */
    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }
}
