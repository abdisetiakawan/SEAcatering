<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->string('tracking_number')->unique();
            $table->enum('status', [
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'failed',
                'returned'
            ])->default('assigned');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('delivery_proof')->nullable(); // photo, signature, etc
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('status');
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
