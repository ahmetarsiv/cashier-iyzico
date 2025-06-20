<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type')->default('default');
            $table->string('plan_id');
            $table->string('iyzico_reference')->nullable();
            $table->string('iyzico_product_reference')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index('iyzico_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
