<?php

namespace Codenteq\Iyzico;

use Codenteq\Iyzico\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Billable
{
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Cashier::$subscriptionModel, 'user_id')->orderBy('created_at', 'desc');
    }

    public function subscription(string $name = 'default'): ?Subscription
    {
        return $this->subscriptions()->where('name', $name)->first();
    }

    public function subscribed(string $name = 'default', ?string $plan = null): bool
    {
        $subscription = $this->subscription($name);

        if (!$subscription || !$subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    public function onTrial(string $name = 'default', ?string $plan = null): bool
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (!$subscription || !$subscription->onTrial()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    public function onGenericTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function newSubscription(string $name, string $plan): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $name, $plan);
    }
}
