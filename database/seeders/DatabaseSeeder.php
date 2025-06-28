<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // CategorySeeder::class,
            MenuItemSeeder::class,
            MealPlanSeeder::class,
            PlanMenuItemSeeder::class,
            UserAddressSeeder::class,
            InventorySeeder::class,
            SubscriptionSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
            DriverSeeder::class,
            DeliverySeeder::class,
            ReviewSeeder::class,
            CartSeeder::class,
        ]);
    }
}
