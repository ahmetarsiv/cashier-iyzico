<?php

return [
    'model' => env('CASHIER_MODEL', 'App\\Models\\User'),

    'iyzico' => [
        'api_key' => env('IYZICO_API_KEY'),
        'secret_key' => env('IYZICO_SECRET_KEY'),
        'base_url' => env('IYZICO_BASE_URL', 'https://sandbox-api.iyzipay.com'),
    ],

    'currency' => env('CASHIER_CURRENCY', 'TRY'),

    'webhook' => [
        'secret' => env('IYZICO_WEBHOOK_SECRET'),
        'tolerance' => env('IYZICO_WEBHOOK_TOLERANCE', 300),
    ],

    'invoice' => [
        'paper' => env('CASHIER_PAPER', 'letter'),
        'locale' => env('CASHIER_LOCALE', 'tr'),
    ],

    'payment_notification' => env('CASHIER_PAYMENT_NOTIFICATION'),
];
