<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {


            $table->string('order_source')->default('web')->after('order_type'); // web, mobile, admin

            $table->string('customer_name')->nullable()->after('order_source');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone')->nullable()->after('customer_email');

            $table->string('payment_method')->default('cash')->after('payment_status');

            $table->renameColumn('delivery_time', 'delivery_time_slot');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_type',
                'order_source',
                'customer_name',
                'customer_email',
                'customer_phone',
                'payment_method'
            ]);
            $table->renameColumn('delivery_time_slot', 'delivery_time');
        });
    }
};
