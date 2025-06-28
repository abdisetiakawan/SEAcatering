<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'Ahmad Kurniawan',
                'phone' => '081234567801',
                'email' => 'ahmad.driver@seacatering.com',
                'license_number' => 'B1234567890',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'B 1234 ABC',
                'status' => 'active',
                'rating' => 4.8,
                'total_deliveries' => 245,
            ],
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567802',
                'email' => 'budi.driver@seacatering.com',
                'license_number' => 'B2345678901',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'B 2345 DEF',
                'status' => 'active',
                'rating' => 4.6,
                'total_deliveries' => 189,
            ],
            [
                'name' => 'Candra Wijaya',
                'phone' => '081234567803',
                'email' => 'candra.driver@seacatering.com',
                'license_number' => 'B3456789012',
                'vehicle_type' => 'car',
                'vehicle_plate' => 'B 3456 GHI',
                'status' => 'active',
                'rating' => 4.9,
                'total_deliveries' => 312,
            ],
            [
                'name' => 'Dedi Prasetyo',
                'phone' => '081234567804',
                'email' => 'dedi.driver@seacatering.com',
                'license_number' => 'B4567890123',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'B 4567 JKL',
                'status' => 'inactive',
                'rating' => 4.7,
                'total_deliveries' => 156,
            ],
            [
                'name' => 'Eko Susilo',
                'phone' => '081234567805',
                'email' => 'eko.driver@seacatering.com',
                'license_number' => 'B5678901234',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'B 5678 MNO',
                'status' => 'active',
                'rating' => 4.5,
                'total_deliveries' => 98,
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
