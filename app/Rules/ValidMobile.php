<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidMobile implements ValidationRule
{
    public function __construct(
        protected string $countryCode
    ) {}

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        // Spaces, -, (, ) remove
        $mobile = preg_replace('/[\s\-\(\)]/', '', (string) $value);
        $patterns = [

            // India - 10 digits, starts 6-9
            '+91' => '/^[6-9][0-9]{9}$/',

            // USA - 10 digits
            '+1' => '/^[2-9][0-9]{9}$/',

            // UK - commonly 10 digits after leading 0 removed
            '+44' => '/^[1-9][0-9]{9}$/',

            // Australia - 9 digits after leading 0 removed
            '+61' => '/^[4][0-9]{8}$/',

            // Japan - 10 digits after leading 0 removed
            '+81' => '/^[7-9][0-9]{9}$/',

            // China - 11 digits, starts 1
            '+86' => '/^1[3-9][0-9]{9}$/',

            // Germany - variable length
            '+49' => '/^[1-9][0-9]{6,13}$/',

            // France - 9 digits after leading 0 removed
            '+33' => '/^[1-9][0-9]{8}$/',

            // Italy - commonly 9-10 digits
            '+39' => '/^[3][0-9]{8,9}$/',

            // UAE - 9 digits, mobile starts 5
            '+971' => '/^5[0-9]{8}$/',

            // Saudi Arabia - 9 digits, mobile starts 5
            '+966' => '/^5[0-9]{8}$/',

            // Pakistan - 10 digits after leading 0 removed
            '+92' => '/^3[0-9]{9}$/',

            // Bangladesh - 10 digits after leading 0 removed
            '+880' => '/^1[3-9][0-9]{8}$/',

            // Sri Lanka - 9 digits after leading 0 removed
            '+94' => '/^7[0-9]{8}$/',

            // Nepal - 10 digits after leading 0 removed
            '+977' => '/^9[6-9][0-9]{8}$/',
        ];

        $pattern = $patterns[$this->countryCode] ?? null;

        if (!$pattern) {
            $fail('validation.mobile_validation')->translate();

            return;
        }

        if (!preg_match($pattern, $mobile)) {
            $fail('validation.mobile_validation')->translate();
        }
    }
}