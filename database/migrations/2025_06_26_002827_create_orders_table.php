<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('delivery_address_id')->nullable()->constrained('user_addresses')->onDelete('set null');

            $table->string('order_number')->unique();
            $table->enum('order_type', ['one_time', 'subscription'])->default('one_time');
            $table->date('delivery_date');
            $table->string('delivery_time_slot');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            // Status dan instruksi
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ])->default('pending');
            $table->text('special_instructions')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Index tambahan
            $table->index(['user_id', 'status']);
            $table->index(['subscription_id', 'status']);
            $table->index(['delivery_date', 'delivery_time_slot']);
            $table->index('status');
            $table->index('order_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
