<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'user_id' => 2,
                'menu_item_id' => 1,
                'order_id' => 1,
                'rating' => 5,
                'comment' => 'Nasi gudeg nya enak banget! Rasanya autentik seperti di Jogja. Porsinya juga pas dan harganya reasonable.',
                'is_published' => true,
            ],
            [
                'user_id' => 2,
                'menu_item_id' => 7,
                'order_id' => 1,
                'rating' => 4,
                'comment' => 'Es cendol durian nya segar, tapi duriannya bisa lebih banyak lagi.',
                'is_published' => true,
            ],
            [
                'user_id' => 3,
                'menu_item_id' => 2,
                'order_id' => 2,
                'rating' => 5,
                'comment' => 'Ayam bakar taliwang nya juara! Pedasnya pas dan bumbunya meresap sempurna. Recommended!',
                'is_published' => true,
            ],
            [
                'user_id' => 3,
                'menu_item_id' => 10,
                'order_id' => 2,
                'rating' => 4,
                'comment' => 'Jus alpukat nya creamy dan segar. Cocok untuk cuaca panas.',
                'is_published' => true,
            ],
            [
                'user_id' => 4,
                'menu_item_id' => 11,
                'order_id' => 3,
                'rating' => 5,
                'comment' => 'Salad quinoa bowl nya perfect untuk diet sehat! Sayurannya fresh dan dressing lemon nya pas.',
                'is_published' => true,
            ],
            [
                'user_id' => 5,
                'menu_item_id' => 5,
                'order_id' => 4,
                'rating' => 4,
                'comment' => 'Gado-gado Jakarta nya enak, bumbu kacangnya gurih. Sayurannya juga segar.',
                'is_published' => true,
            ],
            [
                'user_id' => 5,
                'menu_item_id' => 12,
                'order_id' => 4,
                'rating' => 5,
                'comment' => 'Nasi pecel Madiun nya mantap! Rasanya seperti buatan ibu di rumah.',
                'is_published' => true,
            ],
            [
                'user_id' => 6,
                'menu_item_id' => 1,
                'order_id' => 5,
                'rating' => 3,
                'comment' => 'Gudeg nya lumayan, tapi agak kurang manis untuk selera keluarga.',
                'is_published' => false,
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
