<?php

namespace Laravel\Cashier\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    /**
     * Create a new subscription
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'plan' => 'required|string|max:255',
            'plan_reference' => 'nullable|string',
            'customer.name' => 'required|string|max:255',
            'customer.surname' => 'required|string|max:255',
            'customer.gsmNumber' => 'required|string|max:20',
            'customer.identityNumber' => 'required|string|size:11',
            'customer.billingAddress' => 'required|array',
            'customer.shippingAddress' => 'required|array',
            'card.cardHolderName' => 'required|string|max:255',
            'card.cardNumber' => 'required|string|size:16',
            'card.expireMonth' => 'required|string|size:2',
            'card.expireYear' => 'required|string|size:4',
            'card.cvc' => 'required|string|min:3|max:4',
            'plan_details' => 'nullable|array',
            'skip_trial' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if ($user->subscribed($request->type)) {
            return response()->json([
                'error' => 'Already subscribed to ' . $request->type
            ], 422);
        }

        try {
            $builder = $user->newSubscription($request->type, $request->plan);

            if ($request->boolean('skip_trial')) {
                $builder->skipTrial();
            }

            if ($request->plan_reference) {
                $subscription = $builder
                    ->withPlanReference($request->plan_reference)
                    ->createDirect($request->customer, $request->card);
            } else {
                $subscription = $builder->create(
                    $request->customer,
                    $request->card,
                    $request->plan_details ?? []
                );
            }

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'type' => $subscription->type,
                    'plan_id' => $subscription->plan_id,
                    'status' => $subscription->status,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'is_active' => $subscription->isActive(),
                    'on_trial' => $subscription->onTrial(),
                ],
                'message' => 'Subscription created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Subscription creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user subscriptions
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $subscriptions = $user->subscriptions()->get()->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'type' => $subscription->type,
                'plan_id' => $subscription->plan_id,
                'status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'is_active' => $subscription->isActive(),
                'on_trial' => $subscription->onTrial(),
                'canceled' => $subscription->canceled(),
                'expired' => $subscription->expired(),
                'trial_days_remaining' => $subscription->trialDaysRemaining(),
                'created_at' => $subscription->created_at,
            ];
        });

        return response()->json([
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * Get specific subscription
     */
    public function show(string $type): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->subscription($type);

        if (!$subscription) {
            return response()->json([
                'error' => 'Subscription not found'
            ], 404);
        }

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'type' => $subscription->type,
                'plan_id' => $subscription->plan_id,
                'status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'is_active' => $subscription->isActive(),
                'on_trial' => $subscription->onTrial(),
                'canceled' => $subscription->canceled(),
                'expired' => $subscription->expired(),
                'trial_days_remaining' => $subscription->trialDaysRemaining(),
                'created_at' => $subscription->created_at,
            ]
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(string $type): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->subscription($type);

        if (!$subscription) {
            return response()->json([
                'error' => 'Subscription not found'
            ], 404);
        }

        if (!$subscription->isActive()) {
            return response()->json([
                'error' => 'Subscription is not active'
            ], 422);
        }

        $subscription->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Subscription canceled successfully'
        ]);
    }

    /**
     * Resume subscription
     */
    public function resume(string $type): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->latestSubscription($type);

        if (!$subscription) {
            return response()->json([
                'error' => 'Subscription not found'
            ], 404);
        }

        if (!$subscription->canceled()) {
            return response()->json([
                'error' => 'Subscription is not canceled'
            ], 422);
        }

        $subscription->resume();

        return response()->json([
            'success' => true,
            'message' => 'Subscription resumed successfully'
        ]);
    }
}
