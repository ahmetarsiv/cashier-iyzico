<?php

namespace Feature;

use Laravel\Cashier\Models\Subscription;
use Laravel\Cashier\Services\SubscriptionBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CreateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_subscription_with_iyzico()
    {
        $user = User::factory()->create([
            'email' => 'info@codenteq.com',
        ]);

        $builder = new SubscriptionBuilder($user, 'default', 'd128e8bf-60e4-42f8-8d1b-3190d61b755d');

        $subscription = $builder->create([
            'name' => 'Ahmet Sefa',
            'surname' => 'Arsiv',
            'gsmNumber' => '+905301112233',
            'identityNumber' => '12345678901',
            'billingAddress' => [
                'contactName' => 'John Doe',
                'city' => 'Istanbul',
                'country' => 'Turkiye',
                'description' => '123 Main St, Apt 4B',
            ],
            'shippingAddress' => [
                'contactName' => 'John Doe',
                'city' => 'Istanbul',
                'country' => 'Turkiye',
                'description' => '123 Main St, Apt 4B',
            ],
        ]);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => 'd128e8bf-60e4-42f8-8d1b-3190d61b755d',
            'iyzico_reference' => $subscription->iyzico_reference,
        ]);
    }
}
