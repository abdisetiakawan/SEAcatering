<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $deliveries = [
            [
                'order_id' => 1,
                'driver_id' => 1,
                'delivery_address_id' => 1,
                'status' => 'delivered',
                'delivery_notes' => 'Diterima langsung oleh penerima',
                'recipient_name' => 'John Doe',
                'delivery_fee' => 5000,
            ],
            [
                'order_id' => 2,
                'driver_id' => 2,
                'delivery_address_id' => 3,
                'status' => 'delivered',
                'delivery_notes' => 'Titip di security karena tidak ada yang di rumah',
                'recipient_name' => 'Jane Smith',
                'delivery_fee' => 0,
            ],
            [
                'order_id' => 3,
                'driver_id' => 4,
                'delivery_address_id' => 4,
                'status' => 'in_transit',
                'delivery_notes' => null,
                'recipient_name' => 'Bob Wilson',
                'delivery_fee' => 8000,
            ],
            [
                'order_id' => 4,
                'driver_id' => null,
                'delivery_address_id' => 5,
                'status' => 'returned',
                'delivery_notes' => null,
                'recipient_name' => 'Alice Johnson',
                'delivery_fee' => 7000,
            ],
            [
                'order_id' => 5,
                'driver_id' => 3,
                'delivery_address_id' => 6,
                'status' => 'failed',
                'delivery_notes' => 'Order dibatalkan oleh customer',
                'recipient_name' => 'Charlie Brown',
                'delivery_fee' => 0,
            ],
        ];

        foreach ($deliveries as $delivery) {
            Delivery::create($delivery);
        }
    }
}
