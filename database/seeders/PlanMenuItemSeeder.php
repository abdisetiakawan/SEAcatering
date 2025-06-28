<?php

namespace Database\Seeders;

use App\Models\PlanMenuItem;
use Illuminate\Database\Seeder;

class PlanMenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $planMenuItems = [
            // Paket Hemat Harian (plan_id: 1)
            ['meal_plan_id' => 1, 'menu_item_id' => 1, 'is_featured' => true],
            ['meal_plan_id' => 1, 'menu_item_id' => 5, 'is_featured' => false],
            ['meal_plan_id' => 1, 'menu_item_id' => 9, 'is_featured' => false],
            ['meal_plan_id' => 1, 'menu_item_id' => 12, 'is_featured' => false],

            // Paket Premium Mingguan (plan_id: 2)
            ['meal_plan_id' => 2, 'menu_item_id' => 2, 'is_featured' => true],
            ['meal_plan_id' => 2, 'menu_item_id' => 3, 'is_featured' => true],
            ['meal_plan_id' => 2, 'menu_item_id' => 4, 'is_featured' => false],
            ['meal_plan_id' => 2, 'menu_item_id' => 7, 'is_featured' => false],
            ['meal_plan_id' => 2, 'menu_item_id' => 10, 'is_featured' => false],

            // Paket Sehat Bulanan (plan_id: 3)
            ['meal_plan_id' => 3, 'menu_item_id' => 11, 'is_featured' => true],
            ['meal_plan_id' => 3, 'menu_item_id' => 5, 'is_featured' => false],
            ['meal_plan_id' => 3, 'menu_item_id' => 10, 'is_featured' => false],

            // Paket Vegetarian (plan_id: 4)
            ['meal_plan_id' => 4, 'menu_item_id' => 5, 'is_featured' => true],
            ['meal_plan_id' => 4, 'menu_item_id' => 12, 'is_featured' => true],
            ['meal_plan_id' => 4, 'menu_item_id' => 7, 'is_featured' => false],
            ['meal_plan_id' => 4, 'menu_item_id' => 8, 'is_featured' => false],
            ['meal_plan_id' => 4, 'menu_item_id' => 11, 'is_featured' => false],

            // Paket Family (plan_id: 5)
            ['meal_plan_id' => 5, 'menu_item_id' => 1, 'is_featured' => true],
            ['meal_plan_id' => 5, 'menu_item_id' => 2, 'is_featured' => false],
            ['meal_plan_id' => 5, 'menu_item_id' => 4, 'is_featured' => false],
            ['meal_plan_id' => 5, 'menu_item_id' => 6, 'is_featured' => false],
            ['meal_plan_id' => 5, 'menu_item_id' => 9, 'is_featured' => false],
            ['meal_plan_id' => 5, 'menu_item_id' => 10, 'is_featured' => false],
        ];

        foreach ($planMenuItems as $item) {
            PlanMenuItem::create($item);
        }
    }
}
