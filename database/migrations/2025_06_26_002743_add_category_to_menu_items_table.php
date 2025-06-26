<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->json('dietary_tags')->nullable()->after('allergens'); // vegetarian, vegan, halal, etc
            $table->integer('preparation_time')->nullable()->after('dietary_tags'); // in minutes
            $table->integer('spice_level')->default(0)->after('fat'); // 0-5 scale

            $table->index('category_id');
            $table->index('spice_level');
        });
    }

    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'dietary_tags',
                'preparation_time',
                'spice_level'
            ]);
        });
    }
};
