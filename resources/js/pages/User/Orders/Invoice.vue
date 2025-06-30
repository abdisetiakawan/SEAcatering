<template>
    <div class="min-h-screen bg-white">
        <Head :title="`Invoice ${order.order_number}`" />

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <Button :href="route('user.orders.show', order.id)" variant="outline" size="sm" as="Link">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Order
                </Button>
                <Button @click="printInvoice" variant="outline" size="sm">
                    <Printer class="mr-2 h-4 w-4" />
                    Print Invoice
                </Button>
            </div>

            <!-- Invoice Content -->
            <div id="invoice-content" class="rounded-lg border border-gray-200 bg-white p-8">
                <!-- Invoice Header -->
                <div class="mb-8 flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">SEA Catering</h1>
                        <p class="mt-2 text-gray-600">Premium Meal Delivery Service</p>
                        <div class="mt-4 text-sm text-gray-600">
                            <p>Jl. Sudirman No. 123</p>
                            <p>Jakarta Pusat, DKI Jakarta 10220</p>
                            <p>Phone: (021) 1234-5678</p>
                            <p>Email: info@seacatering.com</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold text-gray-900">INVOICE</h2>
                        <p class="mt-2 text-lg font-semibold text-gray-700">{{ order.order_number }}</p>
                        <div class="mt-4 text-sm text-gray-600">
                            <p><strong>Invoice Date:</strong> {{ formatDate(order.created_at) }}</p>
                            <p><strong>Order Type:</strong> {{ getOrderTypeLabel(order.order_type) }}</p>
                            <p><strong>Status:</strong> {{ order.status }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer & Delivery Info -->
                <div class="mb-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <!-- Bill To -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Bill To:</h3>
                        <div class="text-gray-700">
                            <p class="font-medium">{{ order.customer_name }}</p>
                            <p>{{ order.customer_email }}</p>
                        </div>
                    </div>

                    <!-- Deliver To -->
                    <div v-if="order.delivery_address">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Deliver To:</h3>
                        <div class="text-gray-700">
                            <p>{{ order.delivery_address.address_line_1 }}</p>
                            <p v-if="order.delivery_address.address_line_2">{{ order.delivery_address.address_line_2 }}</p>
                            <p>{{ order.delivery_address.city }}, {{ order.delivery_address.province }}</p>
                            <p>{{ order.delivery_address.postal_code }}</p>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <p><strong>Delivery Date:</strong> {{ formatDate(order.delivery_date) }}</p>
                            <p><strong>Delivery Time:</strong> {{ order.delivery_time }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Items</h3>
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in order.order_items" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ item.menu_item.name }}</div>
                                            <div class="text-sm text-gray-500">{{ item.menu_item.description }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">Rp {{ formatPrice(item.price) }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">Rp {{ formatPrice(item.total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="mb-8 flex justify-end">
                    <div class="w-full max-w-sm">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="text-gray-900">Rp {{ formatPrice(order.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (10%):</span>
                                <span class="text-gray-900">Rp {{ formatPrice(order.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery Fee:</span>
                                <span class="text-gray-900">Rp {{ formatPrice(order.delivery_fee) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                                    <span class="text-lg font-semibold text-gray-900">Rp {{ formatPrice(order.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Payment Information</h3>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Payment Method</p>
                                <p class="text-gray-900 capitalize">{{ order.payment_method }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Payment Status</p>
                                <PaymentStatusBadge :status="order.payment_status" />
                            </div>
                            <div v-if="order.payment?.payment_date">
                                <p class="text-sm font-medium text-gray-600">Payment Date</p>
                                <p class="text-gray-900">{{ formatDate(order.payment.payment_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div v-if="order.special_instructions" class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Special Instructions</h3>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <p class="text-gray-700">{{ order.special_instructions }}</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-600">
                    <p>Thank you for choosing SEA Catering!</p>
                    <p class="mt-2">For any questions about this invoice, please contact us at info@seacatering.com</p>
                    <p class="mt-4">This is a computer-generated invoice and does not require a signature.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PaymentStatusBadge from '@/components/User/PaymentStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from 'lucide-vue-next';

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    total: number;
    menu_item: {
        name: string;
        description: string;
    };
}

interface DeliveryAddress {
    address_line_1: string;
    address_line_2?: string;
    city: string;
    province: string;
    postal_code: string;
}

interface Payment {
    amount: number;
    status: string;
    payment_method: string;
    payment_date?: string;
}

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    delivery_date: string;
    delivery_time: string;
    subtotal: number;
    tax_amount: number;
    delivery_fee: number;
    total_amount: number;
    special_instructions?: string;
    status: string;
    payment_status: string;
    payment_method: string;
    created_at: string;
    customer_name: string;
    customer_email: string;
    delivery_address: DeliveryAddress | null;
    order_items: OrderItem[];
    payment: Payment | null;
}

const props = defineProps<{
    order: Order;
}>();

// Methods
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const getOrderTypeLabel = (type: string) => {
    return type === 'subscription' ? 'Subscription Order' : 'Direct Order';
};

const printInvoice = () => {
    window.print();
};
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-content,
    #invoice-content * {
        visibility: visible;
    }
    #invoice-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
