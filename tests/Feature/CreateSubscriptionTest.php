<?php

namespace Codenteq\Iyzico\Tests\Feature;

use Codenteq\Iyzico\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_can_create_subscription()
    {
        $user = $this->createUser();

        $subscription = $user->newSubscription('default', 'monthly-premium')
            ->create();

        $this->assertTrue($user->subscribed('default'));
        $this->assertInstanceOf(Subscription::class, $subscription);
    }

    public function test_user_can_cancel_subscription()
    {
        $user = $this->createUser();
        $subscription = $user->newSubscription('default', 'monthly-premium')->create();

        $subscription->cancel();

        $this->assertTrue($subscription->cancelled());
    }
}
