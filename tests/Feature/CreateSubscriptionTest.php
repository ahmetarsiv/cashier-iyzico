<?php

namespace Laravel\Cashier\Tests\Feature;

use Laravel\Cashier\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CreateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    /** @test */
    public function it_creates_full_subscription_with_product_and_plan()
    {
        $user = User::factory()->create([
            'email' => 'info@codenteq.com',
        ]);

        $customerDetails = $this->getCustomerDetails();
        $cardDetails = $this->getCardDetails();
        $planDetails = $this->getPlanDetails();

        try {
            $subscription = $user->newSubscription('premium', 'premium-monthly')
                ->create($customerDetails, $cardDetails, $planDetails);

            $this->assertInstanceOf(Subscription::class, $subscription);
            $this->assertDatabaseHas('subscriptions', [
                'user_id' => $user->id,
                'type' => 'premium',
                'status' => 'active',
            ]);

            $this->assertNotNull($subscription->iyzico_reference);
            $this->assertNotNull($subscription->iyzico_product_reference);
            $this->assertTrue($subscription->isActive());
            $this->assertTrue($subscription->onTrial());

        } catch (\Exception $e) {
            $this->fail('Full subscription creation failed: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_creates_direct_subscription_with_existing_plan()
    {
        $user = User::factory()->create([
            'email' => 'direct@codenteq.com',
        ]);

        $customerDetails = $this->getCustomerDetails();
        $cardDetails = $this->getCardDetails();

        try {
            $subscription = $user->newSubscription('basic', 'basic-monthly')
                ->withPlanReference('45454e0b-755d-4653-944e-e2d6f4871b28')
                ->createDirect($customerDetails, $cardDetails);

            $this->assertInstanceOf(Subscription::class, $subscription);
            $this->assertEquals('45454e0b-755d-4653-944e-e2d6f4871b28', $subscription->plan_id);
            $this->assertTrue($subscription->isActive());

        } catch (\Exception $e) {
            $this->fail('Direct subscription creation failed: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_creates_subscription_without_trial()
    {
        $user = User::factory()->create([
            'email' => 'notrial@codenteq.com',
        ]);

        $customerDetails = $this->getCustomerDetails();
        $cardDetails = $this->getCardDetails();

        try {
            $subscription = $user->newSubscription('enterprise', 'enterprise-yearly')
                ->withPlanReference('45454e0b-755d-4653-944e-e2d6f4871b28')
                ->skipTrial()
                ->createDirect($customerDetails, $cardDetails);

            $this->assertInstanceOf(Subscription::class, $subscription);
            $this->assertFalse($subscription->onTrial());
            $this->assertNull($subscription->trial_ends_at);

        } catch (\Exception $e) {
            $this->fail('No-trial subscription creation failed: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_handles_subscription_status_methods()
    {
        $user = User::factory()->create();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'type' => 'test',
            'plan_id' => 'test-plan',
            'iyzico_reference' => 'test-ref',
            'status' => 'active',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->onTrial());
        $this->assertFalse($subscription->canceled());
        $this->assertFalse($subscription->expired());
        $this->assertEquals(7, $subscription->trialDaysRemaining());
    }

    /** @test */
    public function it_can_cancel_and_resume_subscription()
    {
        $user = User::factory()->create();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'type' => 'test',
            'plan_id' => 'test-plan',
            'iyzico_reference' => 'test-ref',
            'status' => 'active',
        ]);

        $subscription->cancel();
        $this->assertTrue($subscription->canceled());
        $this->assertFalse($subscription->isActive());

        $subscription->resume();
        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->canceled());
    }

    /** @test */
    public function it_throws_exception_for_direct_subscription_without_plan_reference()
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Plan reference code is required for direct subscription creation');

        $user->newSubscription('test', 'test-plan')
            ->createDirect($this->getCustomerDetails(), $this->getCardDetails());
    }

    /** @test */
    public function user_can_access_subscription_via_relationship()
    {
        $user = User::factory()->create();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'type' => 'premium',
            'plan_id' => 'premium-plan',
            'iyzico_reference' => 'test-ref',
            'status' => 'active',
        ]);

        $this->assertEquals($subscription->id, $user->subscription('premium')->id);
        $this->assertNull($user->subscription('basic'));
    }

    protected function getCustomerDetails(): array
    {
        return [
            'name' => 'Ahmet Sefa',
            'surname' => 'Arsiv',
            'gsmNumber' => '+905301112233',
            'identityNumber' => '12345678901',
            'billingAddress' => [
                'contactName' => 'Ahmet Sefa Arsiv',
                'city' => 'Istanbul',
                'country' => 'Turkey',
                'address' => 'Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1',
                'zipCode' => '34732',
            ],
            'shippingAddress' => [
                'contactName' => 'Ahmet Sefa Arsiv',
                'city' => 'Istanbul',
                'country' => 'Turkey',
                'address' => 'Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1',
                'zipCode' => '34732',
            ],
        ];
    }

    protected function getCardDetails(): array
    {
        return [
            'cardHolderName' => 'Ahmet Sefa Arsiv',
            'cardNumber' => '5528790000000008',
            'expireMonth' => '12',
            'expireYear' => '2030',
            'cvc' => '123',
        ];
    }

    protected function getPlanDetails(): array
    {
        return [
            'price' => 99.99,
            'currency_code' => 'TRY',
            'payment_interval' => 'MONTHLY',
            'payment_interval_count' => 1,
            'trial_period_days' => 7,
            'plan_payment_type' => 'RECURRING',
            'recurrence_count' => null,
        ];
    }
}
