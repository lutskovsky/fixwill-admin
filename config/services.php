<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'comagic' => [
        'token' => env('COMAGIC_TOKEN'),
        'username' => env('COMAGIC_USERNAME', 'fixwill'),
        'password' => env('COMAGIC_PASSWORD', 'rasa1hague'),
        'virtual_numbers' => [
            'default' => env('COMAGIC_DEFAULT_VIRTUAL_NUMBER', '74954893455'),
            'scenario' => env('COMAGIC_SCENARIO_VIRTUAL_NUMBER', '79053056181'),
        ],
        'fixcpa_scenario_id' => env('COMAGIC_FIXCPA_SCENARIO_ID', 545220),
        'potential_call_scenario_id' => env('COMAGIC_POTENTIAL_CALL_SCENARIO_ID', 549800),
    ],

];
