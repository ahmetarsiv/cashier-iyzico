<?php

namespace Laravel\Cashier\Services;

use Iyzipay\Model\Subscription\SubscriptionProduct;
use Iyzipay\Request\Subscription\SubscriptionCreateProductRequest;
use Iyzipay\Options;

class ProductService
{
    protected Options $options;

    public function __construct()
    {
        $this->options = new Options();
        $this->options->setApiKey(config('cashier-iyzico.api_key'));
        $this->options->setSecretKey(config('cashier-iyzico.secret_key'));
        $this->options->setBaseUrl(config('cashier-iyzico.base_url'));
    }

    /**
     * Create a product in Iyzico
     */
    public function createProduct(string $name, string $description): SubscriptionProduct
    {
        $request = new SubscriptionCreateProductRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(\Illuminate\Support\Str::uuid());
        $request->setName($name);
        $request->setDescription($description);

        return SubscriptionProduct::create($request, $this->options);
    }
}
