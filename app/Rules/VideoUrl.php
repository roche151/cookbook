<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class VideoUrl implements Rule
{
    public function passes($attribute, $value)
    {
        if (empty($value)) return true;
        $value = trim($value);
        // YouTube
        if (preg_match('#^(https?://)?(www\.)?(youtube\.com/watch\?v=|youtu\.be/)[\w-]{11}#i', $value)) {
            return true;
        }
        // Instagram (reels or posts)
        if (preg_match('#^(https?://)?(www\.)?instagram\.com/(reel|p)/[\w-]+#i', $value)) {
            return true;
        }
        // TikTok
        if (preg_match('#^(https?://)?(www\.)?tiktok\.com/@[\w.-]+/video/\d+#i', $value)) {
            return true;
        }
        // Facebook (video URLs)
        if (preg_match('#^(https?://)?(www\.)?facebook\.com/.+/videos/(\d+)#i', $value)) {
            return true;
        }
        return false;
    }

    public function message()
    {
        return 'The :attribute must be a valid YouTube, Instagram, TikTok, or Facebook video URL.';
    }
}
