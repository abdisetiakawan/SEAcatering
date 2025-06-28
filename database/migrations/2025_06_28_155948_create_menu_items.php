<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description');
            $table->text('ingredients')->nullable();
            $table->integer('calories');
            $table->decimal('protein', 5, 2);
            $table->decimal('carbs', 5, 2);
            $table->decimal('fat', 5, 2);
            $table->integer('spice_level')->default(0); // 0-5 scale
            $table->json('allergens')->nullable();
            $table->json('dietary_tags')->nullable(); // vegetarian, vegan, halal, etc
            $table->integer('preparation_time')->nullable(); // in minutes
            $table->decimal('price', 10, 2);
            $table->enum('category', ['diet', 'protein', 'royal', 'vegetarian', 'seafood']);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_available']);
            $table->index('name');
            $table->index('category_id');
            $table->index('spice_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
