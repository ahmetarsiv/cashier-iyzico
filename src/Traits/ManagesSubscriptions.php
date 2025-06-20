<?php

namespace Laravel\Cashier\Traits;

use Laravel\Cashier\Models\Subscription;
use Laravel\Cashier\Services\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ManagesSubscriptions
{
    /**
     * Get all subscriptions for the billable model.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, $this->getForeignKey())
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the active subscription by type.
     */
    public function subscription(string $type = 'default'): ?Subscription
    {
        return $this->subscriptions()
            ->where('type', $type)
            ->active()
            ->first();
    }

    /**
     * Get all active subscriptions.
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->active();
    }

    /**
     * Create a new subscription instance.
     */
    public function newSubscription(string $type, string $plan): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $type, $plan);
    }

    /**
     * Check if the billable model is subscribed to a particular plan.
     */
    public function subscribed(string $type = 'default', ?string $plan = null): bool
    {
        $subscription = $this->subscription($type);

        if (!$subscription || !$subscription->isActive()) {
            return false;
        }

        return $plan ? $subscription->plan_id === $plan : true;
    }

    /**
     * Check if the billable model is on trial.
     */
    public function onTrial(string $type = 'default'): bool
    {
        $subscription = $this->subscription($type);

        return $subscription && $subscription->onTrial();
    }

    /**
     * Check if the billable model has a canceled subscription.
     */
    public function hasCanceledSubscription(string $type = 'default'): bool
    {
        $subscription = $this->subscriptions()
            ->where('type', $type)
            ->latest()
            ->first();

        return $subscription && $subscription->canceled();
    }

    /**
     * Check if the billable model has an expired subscription.
     */
    public function hasExpiredSubscription(string $type = 'default'): bool
    {
        $subscription = $this->subscriptions()
            ->where('type', $type)
            ->latest()
            ->first();

        return $subscription && $subscription->expired();
    }

    /**
     * Get the subscription that was most recently created.
     */
    public function latestSubscription(string $type = 'default'): ?Subscription
    {
        return $this->subscriptions()
            ->where('type', $type)
            ->latest()
            ->first();
    }

    /**
     * Get subscriptions that are on trial.
     */
    public function subscriptionsOnTrial()
    {
        return $this->subscriptions()->onTrial();
    }

    /**
     * Get canceled subscriptions.
     */
    public function canceledSubscriptions()
    {
        return $this->subscriptions()->canceled();
    }

    /**
     * Get the foreign key for the subscription relationship.
     */
    public function getForeignKey(): string
    {
        return 'user_id';
    }
}
