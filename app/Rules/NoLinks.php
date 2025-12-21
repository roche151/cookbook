<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoLinks implements ValidationRule
{
    /**
     * URLs and link-ish patterns to block.
     */
    private const PATTERN = '/https?:\/\/|www\.|\[[^\]]+\]\([^\)]+\)/i';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (preg_match(self::PATTERN, $value)) {
            $fail('Links are not allowed in :attribute.');
        }
    }
}
