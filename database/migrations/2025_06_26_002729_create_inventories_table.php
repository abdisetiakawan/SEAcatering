<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(10);
            $table->integer('maximum_stock')->default(100);
            $table->string('unit')->default('pcs'); // pcs, kg, liter, etc
            $table->decimal('cost_per_unit', 10, 2)->default(0);
            $table->string('supplier')->nullable();
            $table->timestamp('last_restocked_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'discontinued', 'seasonal'])->default('active');
            $table->timestamps();

            $table->index(['menu_item_id', 'status']);
            $table->index('current_stock');
            $table->index('expiry_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
};
