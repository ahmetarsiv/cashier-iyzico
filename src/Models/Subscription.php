<?php

namespace Codenteq\Iyzico\Models;

use Carbon\Carbon;
use Codenteq\Iyzico\Cashier;
use Codenteq\Iyzico\Enums\SubscriptionStatusEnum;
use Codenteq\Iyzico\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->owner();
    }

    public function owner(): BelongsTo
    {
        $model = Cashier::$model;

        return $this->belongsTo($model, 'user_id');
    }

    public function valid(): bool
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    public function active(): bool
    {
        return (is_null($this->ends_at) || $this->onGracePeriod()) &&
            (! $this->onTrial() || $this->trial_ends_at->isFuture()) &&
            $this->iyzico_status === SubscriptionStatusEnum::ACTIVE->value;
    }

    public function cancelled(): bool
    {
        return ! is_null($this->ends_at);
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    public function cancel(): self
    {
        $subscriptionService = new SubscriptionService;

        $nextPaymentPeriod = $subscriptionService->detail($this->iyzico_id)->getOrders()[0]->startPeriod;

        $subscriptionService->cancel($this->iyzico_id);

        $this->iyzico_status = SubscriptionStatusEnum::CANCELED->value;

        $this->ends_at = $this->onTrial() ? $this->trial_ends_at : $this->ends_at ?? now();
        $this->save();

        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = Carbon::createFromTimestampMs($nextPaymentPeriod, 'UTC')->startOfDay();
        }

        $this->save();

        return $this;
    }

    public function resume(): self
    {
        $this->ends_at = null;
        $this->save();

        return $this;
    }

    public function hasPlan(string $plan): bool
    {
        return $this->iyzico_plan === $plan;
    }

    /**
     * @throws \Exception
     */
    public function retry(): self|bool
    {
        $subscriptionService = new SubscriptionService;

        $response = $subscriptionService->retry($this->iyzico_id);

        if ($response->getStatus() === 'success') {
            $this->iyzico_status = SubscriptionStatusEnum::ACTIVE->value;

            $this->save();

            return true;
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function activate(): self|bool
    {
        $subscriptionService = new SubscriptionService;

        $response = $subscriptionService->activate($this->iyzico_id);

        if ($response->getStatus() === 'success') {
            $this->iyzico_status = SubscriptionStatusEnum::ACTIVE->value;

            $this->save();

            return true;
        }

        return false;
    }

    public function upgrade(string $newPricingPlanReferenceCode): bool
    {
        $subscriptionService = new SubscriptionService;

        $response = $subscriptionService->upgrade($this->iyzico_id, $newPricingPlanReferenceCode);

        if ($response->getStatus() === 'success') {
            $this->iyzico_status = SubscriptionStatusEnum::ACTIVE->value;

            $this->save();

            return true;
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function detail()
    {
        $subscriptionService = new SubscriptionService;

        $response = $subscriptionService->detail($this->iyzico_id);

        if ($response->getStatus() === 'success') {
            return $response;
        }

        return false;
    }
}
