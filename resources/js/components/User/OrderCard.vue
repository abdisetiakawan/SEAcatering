<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <!-- Header -->
        <div class="border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ order.order_number }}</h3>
                    <p class="text-sm text-gray-600">{{ order.delivery_address?.city }}, {{ formatDate(order.created_at) }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <OrderStatusBadge :status="order.status" />
                    <PaymentStatusBadge :status="order.payment_status" />
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Order Items Preview -->
            <div class="mb-4">
                <h4 class="mb-2 text-sm font-medium text-gray-900">Items ({{ order.order_items.length }})</h4>
                <div class="flex -space-x-2">
                    <img
                        v-for="(item, index) in order.order_items.slice(0, 3)"
                        :key="item.id"
                        :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                        :alt="item.menu_item.name"
                        class="h-10 w-10 rounded-full border-2 border-white object-cover"
                        :class="{ 'z-10': index === 0, 'z-0': index > 0 }"
                    />
                    <div
                        v-if="order.order_items.length > 3"
                        class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-xs font-medium text-gray-600"
                    >
                        +{{ order.order_items.length - 3 }}
                    </div>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Delivery Address</h4>
                    <p class="text-sm text-gray-600">{{ order.delivery_address?.recipient_name }}</p>
                    <p class="text-sm text-gray-600">{{ order.delivery_address?.city }}, {{ order.delivery_address?.province }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Delivery Schedule</h4>
                    <p class="text-sm text-gray-600">{{ formatDate(order.delivery_date) }}</p>
                    <p class="text-sm text-gray-600">{{ order.delivery_time }}</p>
                </div>
            </div>

            <!-- Payment Info -->
            <div v-if="order.payment" class="mb-4">
                <h4 class="text-sm font-medium text-gray-900">Payment Method</h4>
                <div class="mt-1 flex items-center">
                    <component :is="getPaymentIcon(order.payment.payment_method)" class="mr-2 h-4 w-4 text-gray-600" />
                    <span class="text-sm text-gray-600">{{ getPaymentMethodName(order.payment.payment_method) }}</span>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="space-y-2 border-t pt-4">
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

        <!-- Actions -->
        <div class="border-t border-gray-200 p-6">
            <div class="flex flex-wrap gap-2">
                <Button v-if="order.can_pay" @click="$emit('pay-order', order)" variant="default" size="sm" class="min-w-0 flex-1">
                    <CreditCard class="mr-2 h-4 w-4" />
                    Pay Now
                </Button>
                <Button @click="$emit('view-details', order)" variant="outline" size="sm" class="min-w-0 flex-1">
                    <Eye class="mr-2 h-4 w-4" />
                    View Details
                </Button>
                <Button
                    v-if="order.can_be_cancelled"
                    @click="$emit('cancel-order', order)"
                    variant="outline"
                    size="sm"
                    class="text-red-600 hover:text-red-700"
                >
                    <X class="mr-2 h-4 w-4" />
                    Cancel
                </Button>
                <Button @click="$emit('reorder', order)" variant="outline" size="sm">
                    <RotateCcw class="mr-2 h-4 w-4" />
                    Reorder
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import OrderStatusBadge from '@/components/User/OrderStatusBadge.vue';
import PaymentStatusBadge from '@/components/User/PaymentStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Banknote, Building2, CreditCard, Eye, RotateCcw, Smartphone, X } from 'lucide-vue-next';

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    menu_item: {
        id: number;
        name: string;
        image: string;
    };
}

interface DeliveryAddress {
    id: number;
    recipient_name: string;
    phone_number: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    province: string;
    postal_code: string;
    full_address: string;
    delivery_instructions?: string;
}

interface Payment {
    id: number;
    payment_number: string;
    amount: number;
    status: string;
    payment_method: string;
    payment_date?: string;
    transaction_id?: string;
}

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    order_source: string;
    delivery_date: string;
    delivery_time: string;
    subtotal: number;
    tax_amount: number;
    delivery_fee: number;
    total_amount: number;
    payment_status: string;
    can_pay: boolean;
    status: string;
    special_instructions?: string;
    created_at: string;
    can_be_cancelled: boolean;
    order_items: OrderItem[];
    delivery_address: DeliveryAddress | null;
    payment: Payment | null;
}

defineProps<{
    order: Order;
}>();

defineEmits<{
    'view-details': [order: Order];
    'cancel-order': [order: Order];
    reorder: [order: Order];
    'pay-order': [order: Order];
}>();

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
        month: 'short',
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
</script>
