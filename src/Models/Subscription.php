<?php

namespace Codenteq\Iyzico\Models;

use Carbon\Carbon;
use Codenteq\Iyzico\Cashier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->owner();
    }

    public function owner(): BelongsTo
    {
        $model = config('cashier.model', config('auth.providers.users.model', 'App\\Models\\User'));

        return $this->belongsTo($model, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Cashier::$subscriptionItemModel, 'subscription_id');
    }

    public function valid(): bool
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    public function active(): bool
    {
        return (is_null($this->ends_at) || $this->onGracePeriod()) &&
            (!$this->onTrial() || $this->trial_ends_at->isFuture()) &&
            $this->iyzico_status === 'active';
    }

    public function cancelled(): bool
    {
        return !is_null($this->ends_at);
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
        $this->ends_at = $this->onTrial() ? $this->trial_ends_at : $this->ends_at ?? now();
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
}
