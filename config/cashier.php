<?php

return [
    /*
    |--------------------------------------------------------------------------
    | İyzico API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your İyzico API credentials and settings.
    | You can get these values from your İyzico merchant dashboard.
    |
    */
    'iyzico' => [
        'api_key' => env('IYZICO_API_KEY'),
        'secret_key' => env('IYZICO_SECRET_KEY'),
        'base_url' => env('IYZICO_BASE_URL', 'https://api.iyzipay.com'),
        'sandbox' => env('IYZICO_SANDBOX', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Billable Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the Billable trait
    | provided by Cashier. It will serve as the primary model you use while
    | interacting with Cashier related methods, subscriptions, and so on.
    |
    */
    'model' => env('CASHIER_MODEL', App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used when generating charges
    | from your application. Of course, you are welcome to use any of the
    | various world currencies that are currently supported via İyzico.
    |
    */
    'currency' => env('CASHIER_CURRENCY', 'TRY'),

    /*
    |--------------------------------------------------------------------------
    | Currency Symbol
    |--------------------------------------------------------------------------
    |
    | This is the currency symbol that will be used when formatting currency
    | amounts for display in your application. You can change this to any
    | symbol that is appropriate for your application's currency.
    |
    */
    'currency_symbol' => env('CASHIER_CURRENCY_SYMBOL', '₺'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure webhook endpoints and secret for İyzico webhooks.
    | Make sure to configure these in your İyzico dashboard as well.
    |
    */
    'webhook' => [
        'secret' => env('IYZICO_WEBHOOK_SECRET'),
        'tolerance' => env('IYZICO_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration will be used when generating invoices for your
    | subscriptions. You can customize the company information, logo,
    | and other details that will appear on the generated invoices.
    |
    */
    'invoice' => [
        'paper' => env('CASHIER_PAPER', 'letter'),
        'locale' => env('CASHIER_LOCALE', 'tr'),
    ],

    'payment_notification' => env('CASHIER_PAYMENT_NOTIFICATION'),
];
