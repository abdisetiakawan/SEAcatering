<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('meal_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_address_id')->constrained('user_addresses')->onDelete('cascade');
            $table->string('subscription_number')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->integer('meals_per_day')->default(1);
            $table->json('delivery_days');
            $table->string('delivery_time_preference')->default('morning');
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('special_requirements')->nullable();
            $table->datetime('next_delivery_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
