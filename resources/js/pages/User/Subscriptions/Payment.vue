<template>
    <UserLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Payment Subscription</h1>
                    <p class="mt-2 text-gray-600">Complete your subscription payment</p>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Payment Form -->
                    <div class="lg:col-span-2">
                        <div class="rounded-lg border bg-white p-6 shadow-sm">
                            <h2 class="mb-6 text-xl font-semibold text-gray-900">Payment Details</h2>

                            <form @submit.prevent="processPayment">
                                <!-- Payment Method Display -->
                                <div class="mb-6">
                                    <label class="mb-2 block text-sm font-medium text-gray-700"> Payment Method </label>
                                    <div class="rounded-lg border bg-gray-50 p-4">
                                        <div class="flex items-center">
                                            <Icon :name="getPaymentIcon(subscription.payment.payment_method)" class="mr-3 h-6 w-6 text-gray-600" />
                                            <span class="font-medium">
                                                {{ getPaymentMethodName(subscription.payment.payment_method) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Instructions -->
                                <div class="mb-6" v-if="subscription.payment.payment_method === 'bank_transfer'">
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                        <h3 class="mb-2 font-medium text-blue-900">Transfer Instructions</h3>
                                        <p class="mb-3 text-sm text-blue-800">Please transfer the exact amount to our bank account:</p>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-blue-700">Bank:</span>
                                                <span class="font-medium">Bank Mandiri</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-blue-700">Account Number:</span>
                                                <span class="font-medium">1234567890123456</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-blue-700">Account Name:</span>
                                                <span class="font-medium">SEA Catering</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- E-Wallet Instructions -->
                                <div class="mb-6" v-if="subscription.payment.payment_method === 'e_wallet'">
                                    <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                                        <h3 class="mb-2 font-medium text-green-900">E-Wallet Payment</h3>
                                        <p class="mb-3 text-sm text-green-800">Scan QR code or use deep link to complete payment:</p>
                                        <div class="mb-3 flex justify-center">
                                            <div class="flex h-32 w-32 items-center justify-center rounded-lg border-2 border-green-300 bg-white">
                                                <span class="text-xs text-gray-500">QR Code</span>
                                            </div>
                                        </div>
                                        <p class="text-center text-xs text-green-700">Payment expires in 15 minutes</p>
                                    </div>
                                </div>

                                <!-- Cash Payment Instructions -->
                                <div class="mb-6" v-if="subscription.payment.payment_method === 'cash'">
                                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                        <h3 class="mb-2 font-medium text-yellow-900">Cash Payment</h3>
                                        <p class="text-sm text-yellow-800">
                                            You can pay cash when your first meal is delivered. Please prepare the exact amount.
                                        </p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-6">
                                    <label for="notes" class="mb-2 block text-sm font-medium text-gray-700"> Payment Notes (Optional) </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        placeholder="Add any notes about your payment..."
                                    ></textarea>
                                </div>

                                <!-- Submit Button -->
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="w-full rounded-lg bg-blue-600 px-4 py-3 font-medium text-white transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span v-if="processing">Processing Payment...</span>
                                    <span v-else>Complete Payment</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Subscription Summary -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-8 rounded-lg border bg-white p-6 shadow-sm">
                            <h2 class="mb-6 text-xl font-semibold text-gray-900">Subscription Summary</h2>

                            <!-- Subscription Details -->
                            <div class="mb-6 space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subscription Number:</span>
                                    <span class="font-medium">{{ subscription.subscription_number }}</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Meal Plan:</span>
                                    <span class="font-medium">{{ subscription.meal_plan.name }}</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Duration:</span>
                                    <span class="font-medium">
                                        {{ formatDate(subscription.start_date) }} - {{ formatDate(subscription.end_date) }}
                                    </span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Frequency:</span>
                                    <span class="font-medium capitalize">{{ subscription.frequency }}</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Meals per Day:</span>
                                    <span class="font-medium">{{ subscription.meals_per_day }}</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Delivery Days:</span>
                                    <span class="font-medium">{{ subscription.delivery_days.join(', ') }}</span>
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="mb-6" v-if="subscription.delivery_address">
                                <h3 class="mb-2 font-medium text-gray-900">Delivery Address</h3>
                                <div class="text-sm text-gray-600">
                                    <p class="font-medium">{{ subscription.delivery_address.recipient_name }}</p>
                                    <p>{{ subscription.delivery_address.phone_number }}</p>
                                    <p>{{ subscription.delivery_address.full_address }}</p>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="border-t pt-4">
                                <div class="mb-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span>{{ formatCurrency(subscription.total_price + subscription.discount_amount) }}</span>
                                    </div>

                                    <div class="flex justify-between text-sm text-green-600" v-if="subscription.discount_amount > 0">
                                        <span>Discount:</span>
                                        <span>-{{ formatCurrency(subscription.discount_amount) }}</span>
                                    </div>
                                </div>

                                <div class="border-t pt-2">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total:</span>
                                        <span class="text-blue-600">{{ formatCurrency(subscription.total_price) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                                <div class="flex items-center">
                                    <Icon name="clock" class="mr-2 h-5 w-5 text-yellow-600" />
                                    <span class="text-sm font-medium text-yellow-800">Payment Pending</span>
                                </div>
                                <p class="mt-1 text-xs text-yellow-700">Complete payment to activate your subscription</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import UserLayout from '@/layouts/UserLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Payment {
    id: number;
    payment_number: string;
    amount: number;
    status: string;
    payment_method: string;
}

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: number;
}

interface DeliveryAddress {
    id: number;
    recipient_name: string;
    phone_number: string;
    full_address: string;
    city: string;
    province: string;
    postal_code: string;
}

interface Subscription {
    id: number;
    subscription_number: string;
    start_date: string;
    end_date: string;
    frequency: string;
    delivery_days: string[];
    meals_per_day: number;
    total_price: number;
    discount_amount: number;
    status: string;
    meal_plan: MealPlan;
    delivery_address: DeliveryAddress;
    payment: Payment;
}

interface Props {
    subscription: Subscription;
}

const props = defineProps<Props>();

const form = useForm({
    notes: '',
});

const processing = ref<boolean>(false);

const processPayment = (): void => {
    processing.value = true;

    form.post(route('user.subscriptions.process-payment', props.subscription.id), {
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: () => {
            // Payment successful, redirect handled by controller
        },
        onError: (errors) => {
            console.error('Payment failed:', errors);
        },
    });
};

const getPaymentIcon = (method: string): string => {
    const icons: Record<string, string> = {
        bank_transfer: 'building-2',
        credit_card: 'credit-card',
        e_wallet: 'smartphone',
        cash: 'banknote',
    };
    return icons[method] || 'credit-card';
};

const getPaymentMethodName = (method: string): string => {
    const names: Record<string, string> = {
        bank_transfer: 'Bank Transfer',
        credit_card: 'Credit Card',
        e_wallet: 'E-Wallet',
        cash: 'Cash Payment',
    };
    return names[method] || method;
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>
