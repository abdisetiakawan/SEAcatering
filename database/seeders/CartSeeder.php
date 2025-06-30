<?php

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $cartItems = [
            // User 2 cart items
            [
                'user_id' => 2,
                'menu_item_id' => 3,
                'quantity' => 1,
                'unit_price' => 38000,
                'total_price' => 38000,
            ],
            [
                'user_id' => 2,
                'menu_item_id' => 9,
                'quantity' => 2,
                'unit_price' => 5000,
                'total_price' => 10000,
            ],

            // User 3 cart items
            [
                'user_id' => 3,
                'menu_item_id' => 4,
                'quantity' => 1,
                'unit_price' => 35000,
                'total_price' => 35000,
            ],
            [
                'user_id' => 3,
                'menu_item_id' => 8,
                'quantity' => 3,
                'unit_price' => 10000,
                'total_price' => 30000,
            ],

            // User 4 cart items
            [
                'user_id' => 4,
                'menu_item_id' => 11,
                'quantity' => 2,
                'unit_price' => 28000,
                'total_price' => 56000,
            ],

            // User 5 cart items
            [
                'user_id' => 5,
                'menu_item_id' => 5,
                'quantity' => 1,
                'unit_price' => 18000,
                'total_price' => 18000,
            ],
            [
                'user_id' => 5,
                'menu_item_id' => 12,
                'quantity' => 1,
                'unit_price' => 20000,
                'total_price' => 20000,
            ],
            [
                'user_id' => 5,
                'menu_item_id' => 10,
                'quantity' => 1,
                'unit_price' => 8000,
                'total_price' => 8000,
            ],
        ];

        foreach ($cartItems as $item) {
            Cart::create($item);
        }
    }
}
