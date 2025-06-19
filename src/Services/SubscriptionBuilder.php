<?php

namespace Laravel\Cashier\Services;

use Laravel\Cashier\Models\Subscription as SubscriptionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubscriptionBuilder
{
    protected Model $user;
    protected string $type;
    protected string $plan;

    public function __construct(Model $user, string $type, string $plan)
    {
        $this->user = $user;
        $this->type = $type;
        $this->plan = $plan;
    }

    /**
     * Create the subscription using Iyzico API.
     */
    public function create(array $customerDetails = []): mixed
    {
        $service = new IyzicoSubscriptionService();

        $response = $service->createSubscription([
            'conversation_id' => Str::uuid(),
            'pricing_plan_reference_code' => $this->plan,
            'customer' => [
                "name" => $customerDetails['name'],
                "surname" => $customerDetails['surname'],
                "email" => $this->user->email,
                "gsmNumber" => $customerDetails['gsmNumber'],
                "identityNumber" => $customerDetails['identityNumber'],
                "billingAddress" => $customerDetails['billingAddress'],
                "shippingAddress" => $customerDetails['shippingAddress'],
            ],
        ]);

        return SubscriptionModel::create([
            'user_id' => $this->user->id,
            'type' => $this->type,
            'plan_id' => $this->plan,
            'iyzico_reference' => $response->getReferenceCode(),
            'trial_ends_at' => now()->addDays(7),
        ]);
    }
}
