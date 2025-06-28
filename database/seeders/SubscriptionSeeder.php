<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = [
            [
                'subscription_number' => 'SUB-' . date('Ymd') . '-001',
                'user_id' => 2,
                'meal_plan_id' => 1,
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(31),
                'status' => 'active',
                'delivery_days' => json_encode(['monday', 'wednesday', 'friday']),
                'meals_per_day' => 1,
                'next_delivery_date' => now()->addDays(1),
                'total_price' => rand(100, 1000)
            ],
            [
                'subscription_number' => 'SUB-' . date('Ymd') . '-002',
                'user_id' => 3,
                'meal_plan_id' => 2,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'status' => 'active',
                'delivery_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'meals_per_day' => 1,
                'next_delivery_date' => now()->addDays(1),
                'total_price' => rand(100, 1000)
            ],
            [
                'subscription_number' => 'SUB-' . date('Ymd') . '-003',
                'user_id' => 4,
                'meal_plan_id' => 3,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'status' => 'active',
                'delivery_days' => json_encode(['monday', 'wednesday', 'friday', 'sunday']),
                'meals_per_day' => 2,
                'next_delivery_date' => now()->addDays(2),
                'total_price' => rand(100, 1000)
            ],
            [
                'subscription_number' => 'SUB-' . date('Ymd') . '-004',
                'user_id' => 5,
                'meal_plan_id' => 4,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(4),
                'status' => 'paused',
                'delivery_days' => json_encode(['tuesday', 'thursday', 'saturday']),
                'meals_per_day' => 1,
                'next_delivery_date' => null,
                'total_price' => rand(100, 1000)
            ],
            [
                'subscription_number' => 'SUB-' . date('Ymd') . '-005',
                'user_id' => 6,
                'meal_plan_id' => 5,
                'start_date' => now()->subDays(15),
                'end_date' => now()->subDays(1),
                'status' => 'expired',
                'delivery_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
                'meals_per_day' => 4,
                'next_delivery_date' => null,
                'total_price' => rand(100, 1000)
            ],
        ];

        foreach ($subscriptions as $subscription) {
            Subscription::create($subscription);
        }
    }
}
