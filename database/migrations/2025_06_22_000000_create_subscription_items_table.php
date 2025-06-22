<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('iyzico_id')->unique();
            $table->string('iyzico_plan');
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['subscription_id', 'iyzico_plan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
    }
};
