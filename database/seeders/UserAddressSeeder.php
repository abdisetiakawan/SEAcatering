<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class UserAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create 1-3 addresses per user
            $addressCount = rand(1, 3);

            for ($i = 0; $i < $addressCount; $i++) {
                UserAddress::create([
                    'user_id' => $user->id,
                    'label' => $this->getAddressLabel($i),
                    'recipient_name' => $user->name,
                    'phone_number' => $this->generatePhoneNumber(),
                    'address_line_1' => $this->getAddressLine1(),
                    'address_line_2' => rand(0, 1) ? $this->getAddressLine2() : null,
                    'city' => $this->getRandomCity(),
                    'state' => $this->getRandomState(),
                    'postal_code' => $this->generatePostalCode(),
                    'country' => 'Indonesia',
                    'delivery_instructions' => rand(0, 1) ? $this->getDeliveryInstructions() : null,
                    'address_type' => $this->getAddressType($i),
                    'is_default' => $i === 0, // First address is default
                ]);
            }
        }
    }

    private function getAddressLabel($index)
    {
        $labels = ['Rumah Utama', 'Kantor', 'Rumah Orang Tua', 'Kost', 'Apartemen'];
        return $labels[$index] ?? 'Alamat ' . ($index + 1);
    }

    private function getAddressType($index)
    {
        $types = ['home', 'office', 'other'];
        return $types[$index % 3];
    }

    private function generatePhoneNumber()
    {
        return '08' . rand(10000000, 99999999);
    }

    private function getAddressLine1()
    {
        $streets = [
            'Jl. Sudirman No. 123',
            'Jl. Thamrin No. 456',
            'Jl. Gatot Subroto No. 789',
            'Jl. Kuningan No. 321',
            'Jl. Senopati No. 654',
            'Jl. Kemang Raya No. 987',
            'Jl. Pondok Indah No. 147',
            'Jl. Cipete Raya No. 258',
        ];

        return $streets[array_rand($streets)] . ', RT.' . rand(1, 15) . '/RW.' . rand(1, 10);
    }

    private function getAddressLine2()
    {
        $details = [
            'Blok A No. 15',
            'Lantai 2',
            'Komplek Griya Asri',
            'Perumahan Indah Permai',
            'Apartemen Tower B',
        ];

        return $details[array_rand($details)];
    }

    private function getRandomCity()
    {
        $cities = [
            'Jakarta Selatan',
            'Jakarta Pusat',
            'Jakarta Barat',
            'Jakarta Timur',
            'Jakarta Utara',
            'Tangerang',
            'Bekasi',
            'Depok',
            'Bogor',
        ];

        return $cities[array_rand($cities)];
    }

    private function getRandomState()
    {
        $states = [
            'DKI Jakarta',
            'Jawa Barat',
            'Banten',
        ];

        return $states[array_rand($states)];
    }

    private function generatePostalCode()
    {
        return rand(10000, 99999);
    }

    private function getDeliveryInstructions()
    {
        $instructions = [
            'Rumah cat hijau, sebelah warung Pak Budi',
            'Tolong hubungi sebelum sampai',
            'Masuk gang kecil, rumah nomor 15',
            'Apartemen tower B, lantai 5',
            'Kantor gedung biru, lantai 3',
        ];

        return $instructions[array_rand($instructions)];
    }
}
