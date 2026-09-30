<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('validation.url_validation')->translate();

            return;
        }

        $scheme = strtolower(parse_url($value, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {
            $fail('validation.url_validation')->translate();

            return;
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (!$host) {
            $fail('validation.url_validation')->translate();

            return;
        }

        // Reject local/test URLs.
        $invalidHosts = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
        ];

        if (in_array(strtolower($host), $invalidHosts, true)) {
            $fail('validation.url_validation')->translate();
        }
    }
}