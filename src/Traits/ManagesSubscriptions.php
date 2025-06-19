<?php

namespace Laravel\Cashier\Traits;

use Laravel\Cashier\Models\Subscription;
use Laravel\Cashier\Services\SubscriptionBuilder;

class ManagesSubscriptions
{
    /**
     * Get all subscriptions for the billable model.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, $this->getForeignKey());
    }

    /**
     * Get the active subscription by type.
     */
    public function subscription(string $type = 'default')
    {
        return $this->subscriptions()->where('type', $type)->latest()->first();
    }

    /**
     * Create a new subscription instance.
     */
    public function newSubscription(string $type, string $plan)
    {
        return new SubscriptionBuilder($this, $type, $plan);
    }
}
