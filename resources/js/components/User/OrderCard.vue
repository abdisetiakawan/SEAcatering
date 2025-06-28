<template>
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Order Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <div class="flex items-center space-x-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ order.order_number }}</h3>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <span>{{ formatDate(order.created_at) }}</span>
                            <span>•</span>
                            <Badge :variant="getOrderTypeVariant(order.order_type)">
                                {{ getOrderTypeLabel(order.order_type) }}
                            </Badge>
                        </div>
                    </div>
                    <OrderStatusBadge :status="order.status" />
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">Rp {{ formatPrice(order.total_amount) }}</p>
                    <p class="text-sm text-gray-600">{{ order.order_items.length }} items</p>
                </div>
            </div>

            <!-- Order Items Preview -->
            <div class="py-4">
                <div class="flex items-center space-x-4">
                    <div class="flex -space-x-2">
                        <img
                            v-for="(item, index) in order.order_items.slice(0, 3)"
                            :key="index"
                            :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                            :alt="item.menu_item.name"
                            class="h-10 w-10 rounded-full border-2 border-white object-cover"
                        />
                        <div
                            v-if="order.order_items.length > 3"
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-xs font-medium text-gray-600"
                        >
                            +{{ order.order_items.length - 3 }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ order.order_items[0]?.menu_item.name }}
                            <span v-if="order.order_items.length > 1" class="text-gray-600"> and {{ order.order_items.length - 1 }} more items </span>
                        </p>
                        <p class="text-sm text-gray-600">Delivery: {{ formatDate(order.delivery_date) }} at {{ order.delivery_time }}</p>
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div v-if="order.delivery_address" class="border-t border-gray-200 pt-4">
                <div class="flex items-start space-x-3">
                    <MapPin class="mt-1 h-4 w-4 text-gray-400" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Delivery Address</p>
                        <p class="text-sm text-gray-600">
                            {{ order.delivery_address.address_line_1 }}
                            <span v-if="order.delivery_address.address_line_2"> , {{ order.delivery_address.address_line_2 }} </span>
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ order.delivery_address.city }}, {{ order.delivery_address.province }} {{ order.delivery_address.postal_code }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                <div class="flex items-center space-x-2">
                    <PaymentStatusBadge :status="order.payment_status" />
                    <span v-if="order.special_instructions" class="text-xs text-gray-500">
                        <MessageSquare class="mr-1 inline h-3 w-3" />
                        Has special instructions
                    </span>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" size="sm" @click="$emit('reorder', order)" :disabled="order.status === 'cancelled'">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Reorder
                    </Button>
                    <Button
                        v-if="order.can_be_cancelled"
                        variant="outline"
                        size="sm"
                        @click="$emit('cancel-order', order)"
                        class="text-red-600 hover:text-red-700"
                    >
                        <X class="mr-2 h-4 w-4" />
                        Cancel
                    </Button>
                    <Button size="sm" @click="$emit('view-details', order)" class="bg-green-600 hover:bg-green-700">
                        <Eye class="mr-2 h-4 w-4" />
                        View Details
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import OrderStatusBadge from '@/components/User/OrderStatusBadge.vue';
import PaymentStatusBadge from '@/components/User/PaymentStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Eye, MapPin, MessageSquare, RotateCcw, X } from 'lucide-vue-next';

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    total: number;
    menu_item: {
        id: number;
        name: string;
        image: string;
    };
}

interface DeliveryAddress {
    id: number;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    province: string;
    postal_code: string;
}

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    order_source: string;
    delivery_date: string;
    delivery_time: string;
    total_amount: number;
    status: string;
    payment_status: string;
    special_instructions?: string;
    created_at: string;
    can_be_cancelled: boolean;
    order_items: OrderItem[];
    delivery_address: DeliveryAddress | null;
}

defineProps<{
    order: Order;
}>();

defineEmits<{
    'view-details': [order: Order];
    'cancel-order': [order: Order];
    reorder: [order: Order];
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

const getOrderTypeVariant = (type: string) => {
    return type === 'subscription' ? 'default' : 'secondary';
};

const getOrderTypeLabel = (type: string) => {
    return type === 'subscription' ? 'Subscription' : 'Direct Order';
};
</script>
