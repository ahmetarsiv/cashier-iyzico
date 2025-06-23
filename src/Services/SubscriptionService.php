<?php

namespace Codenteq\Iyzico\Services;

use Iyzipay\Model\Address;
use Iyzipay\Model\Customer;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\Subscription\SubscriptionCancel;
use Iyzipay\Model\Subscription\SubscriptionCreate;
use Iyzipay\Model\Subscription\SubscriptionRetry;
use Iyzipay\Model\Subscription\SubscriptionUpgrade;
use Iyzipay\Request\Subscription\SubscriptionCancelRequest;
use Iyzipay\Request\Subscription\SubscriptionCreateRequest;
use Iyzipay\Options;
use Iyzipay\Request\Subscription\SubscriptionRetryRequest;
use Iyzipay\Request\Subscription\SubscriptionUpgradeRequest;

class SubscriptionService
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
     * Send subscription create request to Iyzico API.
     */
    public function create(array $data): SubscriptionCreate
    {
        $request = new SubscriptionCreateRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($data['conversation_id']);
        $request->setPricingPlanReferenceCode($data['pricing_plan_reference_code']);
        $request->setSubscriptionInitialStatus("ACTIVE");

        $customer = new Customer();
        $customer->setName($data['customer']['name']);
        $customer->setSurname($data['customer']['surname']);
        $customer->setGsmNumber($data['customer']['gsmNumber']);
        $customer->setEmail($data['customer']['email']);
        $customer->setIdentityNumber($data['customer']['identityNumber']);

        $billingAddress = new Address();
        $billingAddress->setContactName($data['customer']['billingAddress']['contactName']);
        $billingAddress->setCity($data['customer']['billingAddress']['city']);
        $billingAddress->setCountry($data['customer']['billingAddress']['country']);
        $billingAddress->setAddress($data['customer']['billingAddress']['address']);
        $billingAddress->setZipCode($data['customer']['billingAddress']['zipCode']);
        $customer->setBillingAddress($billingAddress);

        $shippingAddress = new Address();
        $shippingAddress->setContactName($data['customer']['shippingAddress']['contactName']);
        $shippingAddress->setCity($data['customer']['shippingAddress']['city']);
        $shippingAddress->setCountry($data['customer']['shippingAddress']['country']);
        $shippingAddress->setAddress($data['customer']['shippingAddress']['address']);
        $shippingAddress->setZipCode($data['customer']['shippingAddress']['zipCode']);
        $customer->setShippingAddress($shippingAddress);

        $request->setCustomer($customer);

        $paymentCard = new PaymentCard();
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
     * Cancel subscription in İyzico
     */
    public function cancel(string $subscriptionReferenceCode): SubscriptionCancel
    {
        $request = new SubscriptionCancelRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(uniqid());
        $request->setSubscriptionReferenceCode($subscriptionReferenceCode);

        return SubscriptionCancel::create($request, $this->options);
    }

    /**
     * Retrieve subscription from İyzico
     */
    public function retrieve(string $referenceCode): SubscriptionRetry
    {
        $request = new SubscriptionRetryRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(uniqid());
        $request->setReferenceCode($referenceCode);

        return SubscriptionRetry::create($request, $this->options);
    }

    /**
     * Upgrade subscription in İyzico
     */
    public function upgrade(string $subscriptionReferenceCode, string $newPricingPlanReferenceCode): SubscriptionUpgrade
    {
        $request = new SubscriptionUpgradeRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(uniqid());
        $request->setSubscriptionReferenceCode($subscriptionReferenceCode);
        $request->setNewPricingPlanReferenceCode($newPricingPlanReferenceCode);
        $request->setUpgradePeriod("NOW");
        $request->setUseTrial(false);

        return SubscriptionUpgrade::create($request, $this->options);
    }
}
