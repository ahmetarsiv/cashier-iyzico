<?php

namespace Codenteq\Iyzico;

use Codenteq\Iyzico\Models\Payment;
use Codenteq\Iyzico\Models\Subscription;
use Codenteq\Iyzico\Models\SubscriptionItem;
use Iyzipay\Options;

class Cashier
{
    public static string $model = 'App\\Models\\User';
    public static string $subscriptionModel = Subscription::class;
    public static string $subscriptionItemModel = SubscriptionItem::class;
    public static string $paymentModel = Payment::class;

    public static function useUserModel(string $model): void
    {
        static::$model = $model;
    }

    public static function useSubscriptionModel(string $model): void
    {
        static::$subscriptionModel = $model;
    }

    public static function iyzicoOptions(): Options
    {
        $options = new Options();
        $options->setApiKey(config('cashier.iyzico.api_key'));
        $options->setSecretKey(config('cashier.iyzico.secret_key'));
        $options->setBaseUrl(config('cashier.iyzico.base_url'));

        return $options;
    }

    public static function formatAmount(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
