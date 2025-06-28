<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'order_number' => 'ORD-' . date('Ymd') . '-001',
                'user_id' => 2,
                'subscription_id' => null,
                'delivery_address_id' => 1,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'total_amount' => 75000,
                'delivery_fee' => 5000,
                'tax_amount' => 7500,
                'discount_amount' => 0,
                'delivery_date' => now()->subDays(2),
                'delivery_time_slot' => '12:30:00',
                'special_instructions' => 'Tidak pedas',
                'order_type' => 'one_time',
                'subtotal' => rand(100, 1000)
            ],
            [
                'order_number' => 'ORD-' . date('Ymd') . '-002',
                'user_id' => 3,
                'subscription_id' => 2,
                'delivery_address_id' => 3,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'total_amount' => 35000,
                'delivery_fee' => 0,
                'tax_amount' => 3500,
                'discount_amount' => 5000,
                'delivery_date' => now()->subDays(1),
                'delivery_time_slot' => '18:15:00',
                'special_instructions' => 'Extra sayuran',
                'order_type' => 'subscription',
                'subtotal' => rand(100, 1000)
            ],
            [
                'order_number' => 'ORD-' . date('Ymd') . '-003',
                'user_id' => 4,
                'subscription_id' => null,
                'delivery_address_id' => 4,
                'status' => 'preparing',
                'payment_status' => 'paid',
                'total_amount' => 56000,
                'delivery_fee' => 8000,
                'tax_amount' => 5600,
                'discount_amount' => 10000,
                'delivery_date' => now(),
                'delivery_time_slot' => '11:30:00',
                'special_instructions' => 'Diet rendah garam',
                'order_type' => 'one_time',
                'subtotal' => rand(100, 1000)
            ],
            [
                'order_number' => 'ORD-' . date('Ymd') . '-004',
                'user_id' => 5,
                'subscription_id' => null,
                'delivery_address_id' => 5,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => 43000,
                'delivery_fee' => 7000,
                'tax_amount' => 4300,
                'discount_amount' => 0,
                'delivery_date' => now()->addDays(1),
                'delivery_time_slot' => '13:00:00',
                'special_instructions' => '100% vegetarian',
                'order_type' => 'one_time',
                'subtotal' => rand(100, 1000)
            ],
            [
                'order_number' => 'ORD-' . date('Ymd') . '-005',
                'user_id' => 6,
                'subscription_id' => 5,
                'delivery_address_id' => 6,
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'total_amount' => 72000,
                'delivery_fee' => 0,
                'tax_amount' => 7200,
                'discount_amount' => 15000,
                'delivery_date' => now()->subDays(3),
                'delivery_time_slot' => '17:30:00',
                'special_instructions' => 'Paket keluarga',
                'order_type' => 'subscription',
                'subtotal' => rand(100, 1000)
            ],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }
    }
}
