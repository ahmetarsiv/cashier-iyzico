<?php

namespace Codenteq\Iyzico\Services;

use Codenteq\Iyzico\Enums\SubscriptionStatusEnum;
use Iyzipay\IyzipayResource;
use Iyzipay\Model\Customer;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\Subscription\SubscriptionActivate;
use Iyzipay\Model\Subscription\SubscriptionCancel;
use Iyzipay\Model\Subscription\SubscriptionCreate;
use Iyzipay\Model\Subscription\SubscriptionDetails;
use Iyzipay\Model\Subscription\SubscriptionRetry;
use Iyzipay\Model\Subscription\SubscriptionUpgrade;
use Iyzipay\Options;
use Iyzipay\Request\Subscription\SubscriptionActivateRequest;
use Iyzipay\Request\Subscription\SubscriptionCancelRequest;
use Iyzipay\Request\Subscription\SubscriptionCreateRequest;
use Iyzipay\Request\Subscription\SubscriptionDetailsRequest;
use Iyzipay\Request\Subscription\SubscriptionRetryRequest;
use Iyzipay\Request\Subscription\SubscriptionUpgradeRequest;

class SubscriptionService
{
    protected Options $options;

    /**
     * Create a new subscription service instance.
     */
    public function __construct()
    {
        $this->options = new Options;
        $this->options->setApiKey(config('cashier.iyzico.api_key'));
        $this->options->setSecretKey(config('cashier.iyzico.secret_key'));
        $this->options->setBaseUrl(config('cashier.iyzico.base_url'));
    }

    /**
     * Create a new subscription.
     *
     * @return SubscriptionCreateRequest
     *
     * @throws \Exception
     */
    public function create(array $data): SubscriptionCreate
    {
        $request = new SubscriptionCreateRequest;
        $request->setPricingPlanReferenceCode($data['pricing_plan_reference_code']);
        $request->setSubscriptionInitialStatus(SubscriptionStatusEnum::PENDING->value);

        $customer = new Customer;
        $customer->setName($data['customer']['name']);
        $customer->setSurname($data['customer']['surname']);
        $customer->setGsmNumber($data['customer']['gsmNumber']);
        $customer->setEmail($data['customer']['email']);
        $customer->setIdentityNumber($data['customer']['identityNumber']);

        $customer->setBillingContactName($data['customer']['billingAddress']['contactName']);
        $customer->setBillingCity($data['customer']['billingAddress']['city']);
        $customer->setBillingCountry($data['customer']['billingAddress']['country']);
        $customer->setBillingAddress($data['customer']['billingAddress']['address']);
        $customer->setBillingZipCode($data['customer']['billingAddress']['zipCode']);

        $customer->setShippingContactName($data['customer']['shippingAddress']['contactName']);
        $customer->setShippingCity($data['customer']['shippingAddress']['city']);
        $customer->setShippingCountry($data['customer']['shippingAddress']['country']);
        $customer->setShippingAddress($data['customer']['shippingAddress']['address']);
        $customer->setShippingZipCode($data['customer']['shippingAddress']['zipCode']);

        $request->setCustomer($customer);

        $paymentCard = new PaymentCard;
        $paymentCard->setCardHolderName($data['card']['cardHolderName']);
        $paymentCard->setCardNumber($data['card']['cardNumber']);
        $paymentCard->setExpireMonth($data['card']['expireMonth']);
        $paymentCard->setExpireYear($data['card']['expireYear']);
        $paymentCard->setCvc($data['card']['cvc']);
        $paymentCard->setRegisterConsumerCard(false);

        $request->setPaymentCard($paymentCard);

        return SubscriptionCreate::create($request, $this->options);
    }

    /**
     * Activate a subscription.
     *
     * @throws \Exception
     */
    public function activate(string $subscriptionReferenceCode): IyzipayResource
    {
        $request = new SubscriptionActivateRequest;
        $request->setSubscriptionReferenceCode($subscriptionReferenceCode);

        return SubscriptionActivate::update($request, $this->options);
    }

    /**
     * Retry a failed subscription payment.
     *
     * @throws \Exception
     */
    public function retry(string $referenceCode): IyzipayResource
    {
        $request = new SubscriptionRetryRequest;
        $request->setReferenceCode($referenceCode);

        return SubscriptionRetry::update($request, $this->options);
    }

    /**
     * Upgrade a subscription to a new pricing plan.
     *
     * @throws \Exception
     */
    public function upgrade(string $subscriptionReferenceCode, string $newPricingPlanReferenceCode): SubscriptionUpgrade
    {
        $request = new SubscriptionUpgradeRequest;
        $request->setSubscriptionReferenceCode($subscriptionReferenceCode);
        $request->setResetRecurrenceCount(true);
        $request->setUseTrial(false);
        $request->setNewPricingPlanReferenceCode($newPricingPlanReferenceCode);
        $request->setUpgradePeriod("NOW");

        return SubscriptionUpgrade::update($request, $this->options);
    }

    /**
     * Cancel a subscription.
     *
     * @throws \Exception
     */
    public function cancel(string $subscriptionReferenceCode): IyzipayResource
    {
        $request = new SubscriptionCancelRequest;
        $request->setSubscriptionReferenceCode($subscriptionReferenceCode);

        return SubscriptionCancel::cancel($request, $this->options);
    }

    /**
     * Retrieve subscription details.
     *
     * @throws \Exception
     */
    public function detail(string $subscriptionReferenceCode): SubscriptionDetails
    {
        $request = new SubscriptionDetailsRequest;
        $request->setSubscriptionReferenceCode($subscriptionReferenceCode);

        return SubscriptionDetails::retrieve($request, $this->options);
    }
}
