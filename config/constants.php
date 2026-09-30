<?php

return [
    /**
     * Date format to be used for the application
     */
    'date_format' => [
        'admin_display' => 'd-m-Y h:i:s A',
    ],

    /**
     * Dropdown option lists for General Settings
     */
    'general_options' => [
        'pagination_limit' => [ 5, 10, 15, 20, 25],
        'password_reset_expiry' => [
            1    => '1 Minute',
            5    => '5 Minutes',
            15   => '15 Minutes',
            30   => '30 Minutes',
            60   => '60 Minutes',
            120  => '2 Hours',
            1440 => '24 Hours',
        ],
    ],

    /**
     * System settings configuration
     */
    'settings' => [
        'general' => [
            'pagination_limit' => 10, // per page
            'password_reset_expiry' => 60, // in minutes
        ],
        'twilio' => [
            'enable_twilio' => false,
            'twilio_account_sid' => '',
            'twilio_auth_token' => '',
            'twilio_from_number' => '',
        ],
        'mail' => [
            'mail_mailer' => 'smtp',
            'mail_host' => '',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => '',
            'mail_password' => '',
            'mail_from_address' => '',
            'mail_from_name' => config('app.name'),
        ],
        'aws' => [
            'aws_access_key_id' => '',
            'aws_secret_access_key' => '',
            'aws_default_region' => 'us-east-1',
            'aws_bucket' => '',
        ],
        'otp' => [
            'max_time' => 90, // in seconds
            'otp_length' => 6, // in digits
            'is_default' => true, // true=Fixed OTP or false=Dynamic OTP
            'default' => '999999',
        ],
    ],
];
