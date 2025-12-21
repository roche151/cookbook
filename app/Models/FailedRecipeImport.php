<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedRecipeImport extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'error_message',
        'http_status',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
