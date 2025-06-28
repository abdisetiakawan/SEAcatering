<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('meal_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_address_id')->nullable()->constrained('user_addresses')->onDelete('set null');
            $table->string('subscription_number')->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->integer('meals_per_day')->default(1);
            $table->json('delivery_days')->nullable(); // ['monday', 'tuesday', etc]
            $table->string('delivery_time_preference')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->text('special_requirements')->nullable();
            $table->timestamp('next_delivery_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('next_delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
