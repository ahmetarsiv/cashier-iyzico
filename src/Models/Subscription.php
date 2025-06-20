<?php

namespace Laravel\Cashier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'plan_id',
        'iyzico_reference',
        'iyzico_product_reference',
        'status',
        'trial_ends_at',
        'ends_at',
        'current_period_start',
        'current_period_end',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Check if the subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
            (is_null($this->ends_at) || $this->ends_at->isFuture());
    }

    /**
     * Check if the subscription is on trial.
     */
    public function onTrial(): bool
    {
        return !is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription is canceled.
     */
    public function canceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if the subscription is expired.
     */
    public function expired(): bool
    {
        return $this->status === 'expired' ||
            (!is_null($this->ends_at) && $this->ends_at->isPast());
    }

    /**
     * Check if the subscription is past due.
     */
    public function pastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Get the subscription's remaining trial days.
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->onTrial()) {
            return 0;
        }

        return $this->trial_ends_at->diffInDays(now());
    }

    /**
     * Mark the subscription as canceled.
     */
    public function cancel(): self
    {
        $this->update([
            'status' => 'canceled',
            'ends_at' => now(),
        ]);

        return $this;
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(): self
    {
        $this->update([
            'status' => 'active',
            'ends_at' => null,
        ]);

        return $this;
    }

    /**
     * Scope to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope to only include canceled subscriptions.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Scope to only include subscriptions on trial.
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now());
    }
}
