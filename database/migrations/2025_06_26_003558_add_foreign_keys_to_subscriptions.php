<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_address_id')->nullable()->after('user_id');

            $table->foreign('user_address_id')->references('id')->on('user_addresses')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_address_id']);
            $table->dropColumn('user_address_id');
        });
    }
};
