<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [
            [
                'order_id' => 1,
                'subscription_id' => null,
                'payment_method' => 'debit_card',
                'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'amount' => 87500,
                'transaction_id' => 'TXN-CC-001-' . time(),
                'gateway_response' => json_encode([
                    'status' => 'success',
                    'card_type' => 'visa',
                    'last_four' => '1234',
                    'approval_code' => 'APP123'
                ]),
                'payment_date' => now()->subDays(2),
                'notes' => 'Pembayaran berhasil via Visa ending 1234',
            ],
            [
                'order_id' => 2,
                'subscription_id' => 2,
                'payment_method' => 'bank_transfer',
                'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'amount' => 33500,
                'transaction_id' => 'TXN-BT-002-' . time(),
                'gateway_response' => json_encode([
                    'status' => 'success',
                    'bank' => 'BCA',
                    'account_number' => '1234567890',
                    'reference_number' => 'REF123456'
                ]),
                'payment_date' => now()->subDays(1),
                'notes' => 'Transfer dari BCA berhasil diverifikasi',
            ],
            [
                'order_id' => 3,
                'subscription_id' => null,
                'payment_method' => 'e_wallet',
                'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'amount' => 59600,
                'transaction_id' => 'TXN-EW-003-' . time(),
                'gateway_response' => json_encode([
                    'status' => 'success',
                    'wallet_type' => 'gopay',
                    'wallet_id' => '081234567893',
                    'transaction_time' => now()->subHours(2)->toISOString()
                ]),
                'payment_date' => now()->subHours(2),
                'notes' => 'Pembayaran via GoPay berhasil',
            ],
            [
                'order_id' => 4,
                'subscription_id' => null,
                'payment_method' => 'cash',
                'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'amount' => 54300,
                'transaction_id' => 'TXN-COD-004-' . time(),
                'gateway_response' => json_encode([
                    'status' => 'pending',
                    'delivery_date' => now()->addDays(1)->toDateString(),
                    'driver_assigned' => false
                ]),
                'payment_date' => null,
                'notes' => 'Menunggu pembayaran saat pengiriman',
            ],
            [
                'order_id' => 5,
                'subscription_id' => 5,
                'payment_method' => 'debit_card',
                'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'amount' => 64200,
                'transaction_id' => 'TXN-CC-005-' . time(),
                'gateway_response' => json_encode([
                    'status' => 'refunded',
                    'card_type' => 'mastercard',
                    'last_four' => '5678',
                    'refund_id' => 'REF789',
                    'refund_amount' => 64200
                ]),
                'payment_date' => now()->subDays(3),
                'notes' => 'Pembayaran dibatalkan dan dana dikembalikan',
            ],
        ];

        foreach ($payments as $payment) {
            Payment::create($payment);
        }
    }
}
