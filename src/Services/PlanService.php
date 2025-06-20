<?php

namespace Laravel\Cashier\Services;

use Iyzipay\Model\Subscription\SubscriptionPricingPlan;
use Iyzipay\Options;
use Iyzipay\Request\Subscription\SubscriptionCreatePricingPlanRequest;

class PlanService
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
     * Create a pricing plan in Iyzico
     */
    public function createPlan(array $planData): SubscriptionPricingPlan
    {
        $request = new SubscriptionCreatePricingPlanRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(\Illuminate\Support\Str::uuid());
        $request->setProductReferenceCode($planData['product_reference_code']);
        $request->setName($planData['name']);
        $request->setPrice($planData['price']);
        $request->setCurrencyCode($planData['currency_code']);
        $request->setPaymentInterval($planData['payment_interval']);
        $request->setPaymentIntervalCount($planData['payment_interval_count']);
        $request->setTrialPeriodDays($planData['trial_period_days'] ?? 0);
        $request->setPlanPaymentType($planData['plan_payment_type']);
        $request->setRecurrenceCount($planData['recurrence_count'] ?? null);

        return SubscriptionPricingPlan::create($request, $this->options);
    }
}
