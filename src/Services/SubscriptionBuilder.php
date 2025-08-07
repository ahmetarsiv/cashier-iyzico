<?php

namespace Codenteq\Iyzico\Services;

use Carbon\Carbon;
use Codenteq\Iyzico\Models\Subscription;

class SubscriptionBuilder
{
    /**
     * @var mixed
     */
    protected $owner;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $plan;

    /**
     * @var int
     */
    protected $trialDays = 0;

    /**
     * @var bool
     */
    protected $skipTrial = false;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * Create a new subscription builder instance.
     */
    public function __construct(mixed $owner, string $name, string $plan)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->plan = $plan;
    }

    /**
     * Set the trial period in days.
     */
    public function trialDays(int $trialDays): self
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    /**
     * Skip the trial period.
     */
    public function skipTrial(): self
    {
        $this->skipTrial = true;

        return $this;
    }

    /**
     * Create the subscription.
     *
     * @return Subscription
     *
     * @throws \Exception
     */
    public function create(array $data = [])
    {
        $iyzicoSubscriptionService = new SubscriptionService;

        $response = $iyzicoSubscriptionService->create($data);

        if ($response->getStatus() === 'failure') {
            throw new \Exception($response->getRawResult());
        }

        return $this->owner->subscriptions()->create([
            'name' => $this->name,
            'iyzico_id' => $response->getReferenceCode(),
            'iyzico_status' => $response->getSubscriptionStatus(),
            'iyzico_plan' => $this->plan,
            'iyzico_price' => $data['price'],
            'trial_ends_at' => $this->skipTrial ? null : $this->trialExpiration(),
            'ends_at' => null,
        ]);
    }

    /**
     * Calculate the trial expiration date.
     */
    protected function trialExpiration(): ?Carbon
    {
        if ($this->trialDays) {
            return now()->addDays($this->trialDays);
        }

        return null;
    }
}
