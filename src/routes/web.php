<?php

use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\SubscriptionController;

Route::middleware(['auth:sanctum'])->prefix('api/subscriptions')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index']);

    Route::post('/', [SubscriptionController::class, 'store']);

    Route::get('/{type}', [SubscriptionController::class, 'show']);

    Route::post('/{type}/cancel', [SubscriptionController::class, 'cancel']);

    Route::post('/{type}/resume', [SubscriptionController::class, 'resume']);
});
