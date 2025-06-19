<?php

namespace Laravel\Cashier\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = [];

    protected $dates = [
        'trial_ends_at',
        'ends_at',
    ];

    /**
     * Check if the subscription is currently active.
     */
    public function isActive(): bool
    {
        return is_null($this->ends_at) || $this->ends_at->isFuture();
    }

    /**
     * Check if the subscription is on trial.
     */
    public function onTrial(): bool
    {
        return ! is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }
}
