<?php

declare(strict_types=1);

return [
    'default' => env('SMS_PROVIDER', 'kavenegar'),
    'providers' => [
        'kavenegar' => [
            'api_key' => env('SMS_KAVENEGAR_API_KEY'),
        ],
        'sms_ir' => [
            'api_token' => env('SMS_IR_API_TOKEN'),
        ],
    ],
];
