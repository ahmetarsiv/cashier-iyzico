<?php

namespace Codenteq\Iyzico;

use Codenteq\Iyzico\Models\Subscription;
use Iyzipay\Options;

class Cashier
{
    /**
     * The billable model class name.
     */
    public static string $model = 'App\\Models\\User';

    /**
     * The subscription model class name.
     */
    public static string $subscriptionModel = Subscription::class;

    /**
     * Set the billable model class name.
     */
    public static function useUserModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Set the subscription model class name.
     */
    public static function useSubscriptionModel(string $model): void
    {
        static::$subscriptionModel = $model;
    }

    /**
     * Get configured Iyzico API options.
     */
    public static function iyzicoOptions(): Options
    {
        $options = new Options;
        $options->setApiKey(config('cashier.iyzico.api_key'));
        $options->setSecretKey(config('cashier.iyzico.secret_key'));
        $options->setBaseUrl(config('cashier.iyzico.base_url'));

        return $options;
    }

    /**
     * Format amount from cents to decimal format.
     */
    public static function formatAmount(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
