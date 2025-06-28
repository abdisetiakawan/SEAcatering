<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_id')->nullable()->after('user_id');
            $table->enum('order_type', ['subscription', 'direct'])->default('direct')->after('order_number');

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'order_type']);
        });
    }
};
