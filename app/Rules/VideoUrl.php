<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class VideoUrl implements Rule
{
    public function passes($attribute, $value)
    {
        if (empty($value)) return true;
        $value = trim($value);
        // Only YouTube
        if (preg_match('#^(https?://)?(www\.)?(youtube\.com/watch\?v=|youtu\.be/)[\w-]{11}#i', $value)) {
            return true;
        }
        return false;
    }

    public function message()
    {
        return 'The :attribute must be a valid YouTube video URL.';
    }
}
