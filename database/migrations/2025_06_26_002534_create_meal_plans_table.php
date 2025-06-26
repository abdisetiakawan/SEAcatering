<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price_per_meal', 10, 2);
            $table->integer('target_calories');
            $table->enum('plan_type', ['diet', 'protein', 'royal', 'vegetarian', 'seafood']);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();

            $table->index(['plan_type', 'is_active']);
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_plans');
    }
};
