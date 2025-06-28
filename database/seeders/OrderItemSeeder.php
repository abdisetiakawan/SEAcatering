<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orderItems = [
            // Order 1 items
            [
                'order_id' => 1,
                'menu_item_id' => 1,
                'quantity' => 2,
                'unit_price' => 25000,
                'total_price' => 50000,
                'special_instructions' => 'Tidak pedas',
            ],
            [
                'order_id' => 1,
                'menu_item_id' => 9,
                'quantity' => 1,
                'unit_price' => 5000,
                'total_price' => 5000,
                'special_instructions' => null,
            ],
            [
                'order_id' => 1,
                'menu_item_id' => 7,
                'quantity' => 2,
                'unit_price' => 12000,
                'total_price' => 24000,
                'special_instructions' => null,
            ],

            // Order 2 items
            [
                'order_id' => 2,
                'menu_item_id' => 2,
                'quantity' => 1,
                'unit_price' => 32000,
                'total_price' => 32000,
                'special_instructions' => 'Extra sayuran',
            ],
            [
                'order_id' => 2,
                'menu_item_id' => 10,
                'quantity' => 1,
                'unit_price' => 8000,
                'total_price' => 8000,
                'special_instructions' => null,
            ],

            // Order 3 items
            [
                'order_id' => 3,
                'menu_item_id' => 11,
                'quantity' => 2,
                'unit_price' => 28000,
                'total_price' => 56000,
                'special_instructions' => 'Diet rendah garam',
            ],

            // Order 4 items
            [
                'order_id' => 4,
                'menu_item_id' => 5,
                'quantity' => 1,
                'unit_price' => 18000,
                'total_price' => 18000,
                'special_instructions' => '100% vegetarian',
            ],
            [
                'order_id' => 4,
                'menu_item_id' => 12,
                'quantity' => 1,
                'unit_price' => 20000,
                'total_price' => 20000,
                'special_instructions' => null,
            ],
            [
                'order_id' => 4,
                'menu_item_id' => 8,
                'quantity' => 1,
                'unit_price' => 10000,
                'total_price' => 10000,
                'special_instructions' => null,
            ],

            // Order 5 items
            [
                'order_id' => 5,
                'menu_item_id' => 1,
                'quantity' => 4,
                'unit_price' => 25000,
                'total_price' => 100000,
                'special_instructions' => 'Paket keluarga',
            ],
            [
                'order_id' => 5,
                'menu_item_id' => 9,
                'quantity' => 4,
                'unit_price' => 5000,
                'total_price' => 20000,
                'special_instructions' => null,
            ],
        ];

        foreach ($orderItems as $item) {
            OrderItem::create($item);
        }
    }
}
