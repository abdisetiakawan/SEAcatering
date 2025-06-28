<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Main Course',
                'slug' => 'main-course',
                'description' => 'Hidangan utama yang mengenyangkan',
                'image_url' => '/images/categories/main-course.jpg',
                'icon' => 'utensils',
                'color' => '#FF6B6B',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Appetizer',
                'slug' => 'appetizer',
                'description' => 'Pembuka selera sebelum hidangan utama',
                'image_url' => '/images/categories/appetizer.jpg',
                'icon' => 'cookie',
                'color' => '#4ECDC4',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Dessert',
                'slug' => 'dessert',
                'description' => 'Penutup manis untuk melengkapi makan',
                'image_url' => '/images/categories/dessert.jpg',
                'icon' => 'ice-cream',
                'color' => '#45B7D1',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Minuman segar dan sehat',
                'image_url' => '/images/categories/beverages.jpg',
                'icon' => 'coffee',
                'color' => '#96CEB4',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Healthy',
                'slug' => 'healthy',
                'description' => 'Menu sehat rendah kalori',
                'image_url' => '/images/categories/healthy.jpg',
                'icon' => 'leaf',
                'color' => '#FFEAA7',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Vegetarian',
                'slug' => 'vegetarian',
                'description' => 'Menu khusus vegetarian',
                'image_url' => '/images/categories/vegetarian.jpg',
                'icon' => 'carrot',
                'color' => '#DDA0DD',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
