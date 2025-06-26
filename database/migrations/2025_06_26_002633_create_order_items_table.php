<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Buat table tanpa foreign key dulu
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->json('customizations')->nullable();
            $table->text('special_instructions')->nullable();
            $table->json('nutritional_preferences')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'menu_item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
