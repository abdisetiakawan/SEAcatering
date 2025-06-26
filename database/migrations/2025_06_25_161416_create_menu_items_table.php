<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('ingredients')->nullable();
            $table->integer('calories');
            $table->decimal('protein', 5, 2);
            $table->decimal('carbs', 5, 2);
            $table->decimal('fat', 5, 2);
            $table->json('allergens')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('category', ['diet', 'protein', 'royal', 'vegetarian', 'seafood']);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_available']);
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
};
