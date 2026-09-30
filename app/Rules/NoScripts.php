<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NoScripts implements ValidationRule
{
     /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if (preg_match(
            '/<script\b[^>]*>(.*?)<\/script>|<\?php\b.*?\?>/is',
            $value
        )) {
            $fail('validation.no_scripts')->translate();
        }
    }
}
