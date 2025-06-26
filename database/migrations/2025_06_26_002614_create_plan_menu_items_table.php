<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plan_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->boolean('is_featured')->default(false);
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack'])->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Composite indexes for better query performance
            $table->index(['meal_plan_id', 'menu_item_id']);
            $table->index(['meal_plan_id', 'day_of_week']);
            $table->index(['meal_plan_id', 'meal_type']);
            $table->index(['meal_plan_id', 'is_featured']);

            // Unique constraint to prevent duplicate entries
            $table->unique(['meal_plan_id', 'menu_item_id', 'day_of_week', 'meal_type'], 'unique_plan_menu_item');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_menu_items');
    }
};
