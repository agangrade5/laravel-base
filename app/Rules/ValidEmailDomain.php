<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidEmailDomain implements ValidationRule
{
    /**
     * Valid email domains.
     *
     * @var array
     */
    protected array $validDomains = [
        'gmail.com',
        'yahoo.com',
        'yahoo.co.in',
        'outlook.com',
        'hotmail.com',
        'mailinator.com',
        'yopmail.com',
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(
            substr(strrchr($value, '@'), 1)
        );

        if (!$domain) {
            $fail('validation.email_domain')->translate();

            return;
        }

        // Check whether the domain is valid.
        if (!in_array($domain, $this->validDomains, true)) {
            $fail('validation.email_validation')->translate();

            return;
        }

        // Check whether the domain exists.
        if (
            !checkdnsrr($domain, 'MX') &&
            !checkdnsrr($domain, 'A')
        ) {
            $fail('validation.email_validation')->translate();
        }
    }
}
