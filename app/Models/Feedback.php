<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'page',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
