<?php

namespace Codenteq\Iyzico;

use Codenteq\Iyzico\Models\Subscription;
use Codenteq\Iyzico\Services\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Billable
{
    /**
     * Begin creating a new subscription.
     */
    public function newSubscription(string $name, string $plan): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $name, $plan);
    }

    /**
     * Get all of the subscriptions for the billable model.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Cashier::$subscriptionModel);
    }

    /**
     * Get a subscription instance by name.
     */
    public function subscription(string $name = 'default'): ?Subscription
    {
        return $this->subscriptions()->where('name', $name)->first();
    }

    /**
     * Determine if the billable model is actively subscribed to one of the given plans.
     */
    public function subscribed(string $name = 'default', ?string $plan = null): bool
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * Determine if the billable model is on trial.
     */
    public function onTrial(string $name = 'default', ?string $plan = null): bool
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return ! $plan || $subscription->hasPlan($plan);
    }

    /**
     * Determine if the billable model has a generic trial applied.
     */
    public function onGenericTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function cancel()
    {
        $subscription = $this->subscription();

        if ($subscription) {
            $subscription->cancel();

            return true;
        }

        return false;
    }
}
