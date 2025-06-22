<?php

namespace Codenteq\Iyzico;

use Carbon\Carbon;
use Codenteq\Iyzico\Models\Subscription;

class SubscriptionBuilder
{
    protected $owner;
    protected $name;
    protected $plan;
    protected $quantity = 1;
    protected $trialDays = 0;
    protected $skipTrial = false;
    protected $metadata = [];

    public function __construct($owner, string $name, string $plan)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->plan = $plan;
    }

    public function quantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function trialDays(int $trialDays): self
    {
        $this->trialDays = $trialDays;
        return $this;
    }

    public function skipTrial(): self
    {
        $this->skipTrial = true;
        return $this;
    }

    public function create(array $options = []): Subscription
    {
        $iyzicoSubscription = $this->createIyzicoSubscription($options);

        return $this->owner->subscriptions()->create([
            'name' => $this->name,
            'iyzico_id' => $iyzicoSubscription->getReferenceCode(),
            'iyzico_status' => $iyzicoSubscription->getSubscriptionStatus(),
            'iyzico_plan' => $this->plan,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->skipTrial ? null : $this->trialExpiration(),
            'ends_at' => null,
        ]);
    }

    protected function createIyzicoSubscription(array $options = [])
    {
        // İyzico SDK kullanarak abonelik oluşturma implementasyonu
        // Bu kısım İyzico API dokümantasyonuna göre geliştirilmeli
    }

    protected function trialExpiration(): ?Carbon
    {
        if ($this->trialDays) {
            return now()->addDays($this->trialDays);
        }

        return null;
    }
}
