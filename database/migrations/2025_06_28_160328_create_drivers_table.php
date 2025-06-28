<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('license_number')->unique();
            $table->string('vehicle_type'); // motorcycle, car, van
            $table->string('vehicle_plate');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('availability', ['available', 'busy', 'offline'])->default('offline');
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_deliveries')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'availability']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
