<?php

namespace Database\Seeders;

use App\Models\MealPlan;
use Illuminate\Database\Seeder;

class MealPlanSeeder extends Seeder
{
    public function run(): void
    {
        $mealPlans = [
            [
                'name' => 'Paket Hemat Harian',
                'description' => 'Paket makan harian dengan menu pilihan terbaik dan harga terjangkau',
                'price_per_meal' => 22000,
                'target_calories' => 450,
                'plan_type' => 'diet',
                'is_active' => true,
                'image' => '/images/plans/hemat-harian.jpg',
                'features' => json_encode([
                    'Menu berganti setiap hari',
                    'Porsi standar mengenyangkan',
                    'Gratis ongkir area Jakarta',
                    'Bisa cancel kapan saja'
                ]),
            ],
            [
                'name' => 'Paket Premium Mingguan',
                'description' => 'Paket premium dengan menu pilihan chef dan bahan berkualitas tinggi',
                'price_per_meal' => 35000,
                'target_calories' => 520,
                'plan_type' => 'protein',
                'is_active' => true,
                'image' => '/images/plans/premium-mingguan.jpg',
                'features' => json_encode([
                    'Menu premium chef recommendation',
                    'Bahan berkualitas premium',
                    'Porsi besar dan bergizi',
                    'Gratis ongkir seluruh Jabodetabek',
                    'Customer service 24/7',
                    'Garansi kepuasan 100%'
                ]),
            ],
            [
                'name' => 'Paket Sehat Bulanan',
                'description' => 'Paket khusus menu sehat rendah kalori untuk gaya hidup sehat',
                'price_per_meal' => 28000,
                'target_calories' => 350,
                'plan_type' => 'royal',
                'is_active' => true,
                'image' => '/images/plans/sehat-bulanan.jpg',
                'features' => json_encode([
                    'Menu rendah kalori tinggi protein',
                    'Konsultasi nutrisi gratis',
                    'Tracking kalori harian',
                    'Menu disesuaikan kebutuhan',
                    'Gratis ongkir dan vitamin',
                    'Diskon 15% untuk bulan kedua'
                ]),
            ],
            [
                'name' => 'Paket Vegetarian',
                'description' => 'Paket khusus vegetarian dengan menu bergizi dan lezat',
                'price_per_meal' => 25000,
                'target_calories' => 400,
                'plan_type' => 'protein',
                'is_active' => true,
                'image' => '/images/plans/vegetarian.jpg',
                'features' => json_encode([
                    '100% menu vegetarian',
                    'Tinggi protein nabati',
                    'Bahan organik pilihan',
                    'Menu bervariasi setiap hari',
                    'Gratis konsultasi gizi',
                    'Kemasan ramah lingkungan'
                ]),
            ],
            [
                'name' => 'Paket Family',
                'description' => 'Paket keluarga untuk 4 orang dengan menu lengkap dan hemat',
                'price_per_meal' => 18000,
                'target_calories' => 480,
                'plan_type' => 'royal',
                'is_active' => true,
                'image' => '/images/plans/family.jpg',
                'features' => json_encode([
                    'Paket untuk 4 orang',
                    'Menu anak dan dewasa',
                    'Harga paling hemat',
                    'Porsi besar keluarga',
                    'Gratis ongkir dan snack',
                    'Fleksibel jadwal pengiriman'
                ]),
            ],
        ];

        foreach ($mealPlans as $plan) {
            MealPlan::create($plan);
        }
    }
}
