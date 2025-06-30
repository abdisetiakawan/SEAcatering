<template>
    <UserLayout>
        <Head :title="`Payment - ${order.order_number}`" />

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Payment for Order {{ order.order_number }}</h1>
                                <p class="mt-1 text-gray-600">Complete your payment to confirm the order</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <OrderStatusBadge :status="order.status" />
                                <PaymentStatusBadge :status="order.payment_status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Payment Form -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Payment Information</h2>
                            </div>
                            <div class="p-6">
                                <!-- Selected Payment Method -->
                                <div class="mb-6">
                                    <h3 class="mb-3 text-sm font-medium text-gray-900">Selected Payment Method</h3>
                                    <div class="flex items-center rounded-lg border bg-gray-50 p-4">
                                        <component :is="getPaymentIcon(order.payment.payment_method)" class="mr-3 h-6 w-6 text-gray-600" />
                                        <div>
                                            <p class="font-medium text-gray-900">{{ getPaymentMethodName(order.payment.payment_method) }}</p>
                                            <p class="text-sm text-gray-600">Amount: {{ formatCurrency(order.payment.amount) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Instructions -->
                                <div class="mb-6">
                                    <h3 class="mb-3 text-sm font-medium text-gray-900">Payment Instructions</h3>
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                        <div v-if="order.payment.payment_method === 'bank_transfer'">
                                            <p class="mb-2 text-sm text-blue-800">Please transfer the exact amount to:</p>
                                            <div class="space-y-1 text-sm text-blue-700">
                                                <p><strong>Bank:</strong> Bank Mandiri</p>
                                                <p><strong>Account Number:</strong> 1234567890123456</p>
                                                <p><strong>Account Name:</strong> SEA Catering</p>
                                                <p><strong>Amount:</strong> {{ formatCurrency(order.payment.amount) }}</p>
                                            </div>
                                        </div>
                                        <div v-else-if="order.payment.payment_method === 'credit_card'">
                                            <p class="text-sm text-blue-800">
                                                You will be redirected to secure payment gateway to complete your credit card payment.
                                            </p>
                                        </div>
                                        <div v-else-if="order.payment.payment_method === 'e_wallet'">
                                            <p class="mb-2 text-sm text-blue-800">Scan the QR code below or use the payment link:</p>
                                            <div class="mt-2">
                                                <div class="flex h-32 w-32 items-center justify-center rounded-lg border bg-gray-200">
                                                    <span class="text-xs text-gray-500">QR Code</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="order.payment.payment_method === 'cash_on_delivery'">
                                            <p class="text-sm text-blue-800">
                                                You will pay {{ formatCurrency(order.payment.amount) }} in cash when your order is delivered.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Form -->
                                <form @submit.prevent="processPayment">
                                    <div class="mb-6">
                                        <label for="notes" class="mb-2 block text-sm font-medium text-gray-700"> Payment Notes (Optional) </label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="3"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Add any notes about your payment..."
                                        ></textarea>
                                    </div>

                                    <div class="flex space-x-4">
                                        <Button type="submit" :disabled="processing" class="flex-1">
                                            <CreditCard class="mr-2 h-4 w-4" />
                                            {{ processing ? 'Processing...' : 'Confirm Payment' }}
                                        </Button>
                                        <Button type="button" variant="outline" @click="goBack">
                                            <ArrowLeft class="mr-2 h-4 w-4" />
                                            Back
                                        </Button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="space-y-6">
                        <!-- Order Details -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div v-for="item in order.order_items" :key="item.id" class="flex items-center space-x-3">
                                        <img
                                            :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                                            :alt="item.menu_item.name"
                                            class="h-12 w-12 rounded-lg object-cover"
                                        />
                                        <div class="flex-1">
                                            <h3 class="text-sm font-medium text-gray-900">{{ item.menu_item.name }}</h3>
                                            <p class="text-sm text-gray-600">Qty: {{ item.quantity }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">{{ formatCurrency(item.total_price) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-2 border-t pt-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium">{{ formatCurrency(order.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Delivery Fee</span>
                                        <span class="font-medium">{{ formatCurrency(order.delivery_fee) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tax</span>
                                        <span class="font-medium">{{ formatCurrency(order.tax_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2">
                                        <span class="font-semibold text-gray-900">Total</span>
                                        <span class="font-semibold text-green-600">{{ formatCurrency(order.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Delivery Information</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Delivery Address</h3>
                                        <div class="mt-1 text-sm text-gray-600">
                                            <p class="font-medium">{{ order.delivery_address.recipient_name }}</p>
                                            <p>{{ order.delivery_address.phone_number }}</p>
                                            <p>{{ order.delivery_address.full_address }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Delivery Schedule</h3>
                                        <div class="mt-1 text-sm text-gray-600">
                                            <p>{{ formatDate(order.delivery_date) }}</p>
                                            <p>{{ order.delivery_time_slot }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import OrderStatusBadge from '@/components/User/OrderStatusBadge.vue';
import PaymentStatusBadge from '@/components/User/PaymentStatusBadge.vue';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Banknote, Building2, CreditCard, Smartphone } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    menu_item: {
        name: string;
        description: string;
        image: string;
    };
}

interface Payment {
    id: number;
    payment_number: string;
    amount: number;
    status: string;
    payment_method: string;
}

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    delivery_date: string;
    delivery_time_slot: string;
    subtotal: number;
    tax_amount: number;
    delivery_fee: number;
    total_amount: number;
    special_instructions?: string;
    status: string;
    payment_status: string;
    created_at: string;
    delivery_address: {
        recipient_name: string;
        phone_number: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        province: string;
        postal_code: string;
        full_address: string;
    };
    order_items: OrderItem[];
    payment: Payment;
}

const props = defineProps<{
    order: Order;
}>();

const processing = ref(false);
const form = reactive({
    notes: '',
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getPaymentIcon = (paymentMethod: string) => {
    const icons = {
        bank_transfer: Building2,
        e_wallet: Smartphone,
        credit_card: CreditCard,
        cash_on_delivery: Banknote,
    };
    return icons[paymentMethod as keyof typeof icons] || CreditCard;
};

const getPaymentMethodName = (paymentMethod: string) => {
    const names = {
        bank_transfer: 'Bank Transfer',
        e_wallet: 'E-Wallet',
        credit_card: 'Credit Card',
        cash_on_delivery: 'Cash on Delivery',
    };
    return names[paymentMethod as keyof typeof names] || paymentMethod;
};

const processPayment = () => {
    processing.value = true;

    router.post(route('user.orders.payment.process', props.order.id), form, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route('user.orders.show', props.order.id));
};
</script>
