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
    protected ?string $productReferenceCode = null;
    protected ?string $planReferenceCode = null;
    protected bool $skipTrial = false;

    public function __construct(Model $user, string $type, string $plan)
    {
        $this->user = $user;
        $this->type = $type;
        $this->plan = $plan;
    }

    /**
     * Skip trial period
     */
    public function skipTrial(): self
    {
        $this->skipTrial = true;
        return $this;
    }

    /**
     * Set existing plan reference code (if plan already exists in Iyzico)
     */
    public function withPlanReference(string $planReferenceCode): self
    {
        $this->planReferenceCode = $planReferenceCode;
        return $this;
    }

    /**
     * Create the subscription using Iyzico API - Full 3-step process
     */
    public function create(array $customerDetails = [], array $cardDetails = [], array $planDetails = []): SubscriptionModel
    {
        // Step 1: Create Product (if not exists)
        if (!$this->productReferenceCode) {
            $this->createProduct();
        }

        // Step 2: Create Plan (if not exists)
        if (!$this->planReferenceCode) {
            $this->createPlan($planDetails);
        }

        // Step 3: Create Subscription
        return $this->createSubscription($customerDetails, $cardDetails);
    }

    /**
     * Create subscription directly (if product and plan already exist)
     */
    public function createDirect(array $customerDetails = [], array $cardDetails = []): SubscriptionModel
    {
        if (!$this->planReferenceCode) {
            throw new \Exception('Plan reference code is required for direct subscription creation');
        }

        return $this->createSubscription($customerDetails, $cardDetails);
    }

    /**
     * Step 1: Create Product in Iyzico
     */
    protected function createProduct(): void
    {
        $productService = new ProductService();

        $response = $productService->createProduct(
            $this->type . ' Subscription',
            'Subscription product for ' . $this->type
        );

        if ($response->getStatus() !== 'success') {
            throw new \Exception('İyzico product creation failed: ' . $response->getErrorMessage());
        }

        $this->productReferenceCode = $response->getReferenceCode();
    }

    /**
     * Step 2: Create Plan in Iyzico
     */
    protected function createPlan(array $planDetails): void
    {
        if (!$this->productReferenceCode) {
            throw new \Exception('Product must be created before plan');
        }

        $planService = new PlanService();

        $planData = array_merge([
            'product_reference_code' => $this->productReferenceCode,
            'name' => $this->plan . ' Plan',
            'price' => 50.0,
            'currency_code' => 'TRY',
            'payment_interval' => 'MONTHLY',
            'payment_interval_count' => 1,
            'trial_period_days' => $this->skipTrial ? 0 : 7,
            'plan_payment_type' => 'RECURRING',
            'recurrence_count' => null, // Unlimited
        ], $planDetails);

        $response = $planService->createPlan($planData);

        if ($response->getStatus() !== 'success') {
            throw new \Exception('İyzico plan creation failed: ' . $response->getErrorMessage());
        }

        $this->planReferenceCode = $response->getReferenceCode();
    }

    /**
     * Step 3: Create Subscription in Iyzico
     */
    protected function createSubscription(array $customerDetails, array $cardDetails): SubscriptionModel
    {
        $service = new SubscriptionService();

        $response = $service->createSubscription([
            'conversation_id' => Str::uuid(),
            'pricing_plan_reference_code' => $this->planReferenceCode,
            'customer' => [
                "name" => $customerDetails['name'],
                "surname" => $customerDetails['surname'],
                "email" => $this->user->email,
                "gsmNumber" => $customerDetails['gsmNumber'],
                "identityNumber" => $customerDetails['identityNumber'],
                "billingAddress" => $customerDetails['billingAddress'],
                "shippingAddress" => $customerDetails['shippingAddress'],
            ],
            'card' => $cardDetails,
        ]);

        if ($response->getStatus() !== 'success') {
            throw new \Exception('İyzico subscription creation failed: ' . $response->getErrorMessage());
        }

        return SubscriptionModel::create([
            'user_id' => $this->user->id,
            'type' => $this->type,
            'plan_id' => $this->planReferenceCode,
            'iyzico_reference' => $response->getReferenceCode(),
            'status' => 'active',
            'trial_ends_at' => $this->skipTrial ? null : now()->addDays(7),
            'ends_at' => null,
        ]);
    }
}
