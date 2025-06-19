<?php

namespace Laravel\Cashier\Services;

use Iyzipay\Model\Address;
use Iyzipay\Model\Customer;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\Subscription\SubscriptionCreate;
use Iyzipay\Request\Subscription\SubscriptionCreateRequest;
use Iyzipay\Options;

class IyzicoSubscriptionService
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
    public function createSubscription(array $data): SubscriptionCreate
    {
        $request = new SubscriptionCreateRequest();
        $request->setLocale("tr");
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
        $customer->setBillingAddress($billingAddress);

        $shippingAddress = new Address();
        $shippingAddress->setContactName($data['customer']['shippingAddress']['contactName']);
        $shippingAddress->setCity($data['customer']['shippingAddress']['city']);
        $shippingAddress->setCountry($data['customer']['shippingAddress']['country']);
        $customer->setShippingAddress($shippingAddress);

        $paymentCard = new PaymentCard();
        $paymentCard->setRegisterConsumerCard(false);
        $customer->setShippingAddress($paymentCard);

        $request->setCustomer($customer);

        return SubscriptionCreate::create($request, $this->options);
    }
}
