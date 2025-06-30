<?php

namespace Database\Seeders;

use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = [
            // User ID 2 (John Doe)
            [
                'user_id' => 2,
                'label' => 'Rumah',
                'recipient_name' => 'John Doe',
                'phone_number' => '081234567891',
                'address_line_1' => 'Jl. Sudirman No. 123',
                'address_line_2' => 'Apartemen Sudirman Park, Tower A, Lt. 15',
                'city' => 'Jakarta Pusat',
                'state' => 'DKI Jakarta',
                'postal_code' => '10220',
                'country' => 'Indonesia',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'is_default' => true,
                'delivery_instructions' => 'Tolong hubungi via intercom, unit 15A',
                'address_type' => 'home',
            ],
            [
                'user_id' => 2,
                'label' => 'Kantor',
                'recipient_name' => 'John Doe',
                'phone_number' => '081234567891',
                'address_line_1' => 'Jl. Thamrin No. 456',
                'address_line_2' => 'Gedung BCA Tower, Lt. 25',
                'city' => 'Jakarta Pusat',
                'state' => 'DKI Jakarta',
                'postal_code' => '10230',
                'country' => 'Indonesia',
                'latitude' => -6.1944,
                'longitude' => 106.8229,
                'is_default' => false,
                'delivery_instructions' => 'Titip di resepsionis lantai 25',
                'address_type' => 'office',
            ],

            // User ID 3 (Jane Smith)
            [
                'user_id' => 3,
                'label' => 'Rumah',
                'recipient_name' => 'Jane Smith',
                'phone_number' => '081234567892',
                'address_line_1' => 'Jl. Kemang Raya No. 789',
                'address_line_2' => 'Komplek Kemang Village, Blok C No. 12',
                'city' => 'Jakarta Selatan',
                'state' => 'DKI Jakarta',
                'postal_code' => '12560',
                'country' => 'Indonesia',
                'latitude' => -6.2615,
                'longitude' => 106.8106,
                'is_default' => true,
                'delivery_instructions' => 'Rumah cat hijau, ada pagar putih',
                'address_type' => 'home',
            ],

            // User ID 4 (Bob Wilson)
            [
                'user_id' => 4,
                'label' => 'Rumah',
                'recipient_name' => 'Bob Wilson',
                'phone_number' => '081234567893',
                'address_line_1' => 'Jl. Pondok Indah No. 321',
                'address_line_2' => 'Pondok Indah Mall 2, Residence Tower',
                'city' => 'Jakarta Selatan',
                'state' => 'DKI Jakarta',
                'postal_code' => '12310',
                'country' => 'Indonesia',
                'latitude' => -6.2659,
                'longitude' => 106.7844,
                'is_default' => true,
                'delivery_instructions' => 'Masuk dari pintu samping mall',
                'address_type' => 'home',
            ],

            // User ID 5 (Alice Johnson)
            [
                'user_id' => 5,
                'label' => 'Rumah',
                'recipient_name' => 'Alice Johnson',
                'phone_number' => '081234567894',
                'address_line_1' => 'Jl. Kelapa Gading No. 654',
                'address_line_2' => 'Kelapa Gading Square, Blok B2 No. 8',
                'city' => 'Jakarta Utara',
                'state' => 'DKI Jakarta',
                'postal_code' => '14240',
                'country' => 'Indonesia',
                'latitude' => -6.1598,
                'longitude' => 106.9056,
                'is_default' => true,
                'delivery_instructions' => 'Rumah sudut, ada mobil merah di depan',
                'address_type' => 'home',
            ],

            // User ID 6 (Charlie Brown)
            [
                'user_id' => 6,
                'label' => 'Rumah',
                'recipient_name' => 'Charlie Brown',
                'phone_number' => '081234567895',
                'address_line_1' => 'Jl. Cibubur No. 987',
                'address_line_2' => 'Cibubur Country, Cluster Magnolia',
                'city' => 'Depok',
                'state' => 'Jawa Barat',
                'postal_code' => '16454',
                'country' => 'Indonesia',
                'latitude' => -6.3751,
                'longitude' => 106.8650,
                'is_default' => true,
                'delivery_instructions' => 'Masuk cluster, rumah nomor 15',
                'address_type' => 'home',
            ],
        ];

        foreach ($addresses as $address) {
            UserAddress::create($address);
        }
    }
}
