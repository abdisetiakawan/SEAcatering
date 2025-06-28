<template>
    <UserLayout>
        <Head :title="`Order ${order.order_number}`" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Order {{ order.order_number }}</h1>
                                <p class="mt-1 text-gray-600">Dipesan pada {{ formatDate(order.created_at) }}</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <OrderStatusBadge :status="order.status" />
                                <PaymentStatusBadge :status="order.payment_status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Order Details -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Order Status Timeline -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Status Pesanan</h2>
                            </div>
                            <div class="p-6">
                                <OrderTimeline :status="order.status" :created-at="order.created_at" />
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Item Pesanan</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div v-for="item in order.order_items" :key="item.id" class="flex items-center space-x-4 rounded-lg border p-4">
                                        <img
                                            :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                                            :alt="item.menu_item.name"
                                            class="h-20 w-20 rounded-lg object-cover"
                                        />
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ item.menu_item.name }}</h3>
                                            <p class="text-sm text-gray-500">{{ item.menu_item.category ? item.menu_item.category.name : '' }}</p>
                                            <div class="mt-2 flex items-center space-x-4">
                                                <span class="text-sm text-gray-600">Qty: {{ item.quantity }}</span>
                                                <span class="text-sm text-gray-600">{{ formatCurrency(item.unit_price) }} each</span>
                                            </div>
                                            <div v-if="item.special_instructions" class="mt-1">
                                                <p class="text-xs text-gray-500">Catatan: {{ item.special_instructions }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(item.total_price) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Informasi Pengiriman</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Alamat Pengiriman</h3>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p class="font-medium">{{ order.delivery_address.recipient_name }}</p>
                                            <p>{{ order.delivery_address.phone_number }}</p>
                                            <p>{{ order.delivery_address.address_line_1 }}</p>
                                            <p v-if="order.delivery_address.address_line_2">{{ order.delivery_address.address_line_2 }}</p>
                                            <p>
                                                {{ order.delivery_address.city }}, {{ order.delivery_address.province }}
                                                {{ order.delivery_address.postal_code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Jadwal Pengiriman</h3>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p>{{ formatDate(order.delivery_date) }}</p>
                                            <p>{{ order.delivery_time_slot }}</p>
                                        </div>
                                        <div v-if="order.special_instructions" class="mt-4">
                                            <h3 class="text-sm font-medium text-gray-900">Catatan Khusus</h3>
                                            <p class="mt-1 text-sm text-gray-600">{{ order.special_instructions }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary & Actions -->
                    <div class="space-y-6">
                        <!-- Order Summary -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Pesanan</h2>
                            </div>
                            <div class="space-y-3 p-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium">{{ formatCurrency(order.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Biaya Pengiriman</span>
                                    <span class="font-medium">{{ formatCurrency(order.delivery_fee) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Pajak</span>
                                    <span class="font-medium">{{ formatCurrency(order.tax_amount) }}</span>
                                </div>
                                <div class="border-t pt-3">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total</span>
                                        <span class="text-green-600">{{ formatCurrency(order.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Actions -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Aksi</h2>
                            </div>
                            <div class="space-y-3 p-6">
                                <Button
                                    v-if="order.can_be_cancelled"
                                    @click="confirmCancelOrder"
                                    variant="outline"
                                    class="w-full text-red-600 hover:text-red-700"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Batalkan Pesanan
                                </Button>
                                <Button @click="reorderItems" variant="outline" class="w-full">
                                    <RotateCcw class="mr-2 h-4 w-4" />
                                    Pesan Lagi
                                </Button>
                                <Button @click="downloadInvoice" variant="outline" class="w-full">
                                    <Download class="mr-2 h-4 w-4" />
                                    Download Invoice
                                </Button>
                            </div>
                        </div>

                        <!-- Contact Support -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-sm font-medium text-gray-900">Butuh Bantuan?</h3>
                                <p class="mt-1 text-sm text-gray-600">Hubungi customer service kami</p>
                                <div class="mt-4 space-y-2">
                                    <Button variant="outline" size="sm" class="w-full">
                                        <Phone class="mr-2 h-4 w-4" />
                                        Call Center
                                    </Button>
                                    <Button variant="outline" size="sm" class="w-full">
                                        <MessageCircle class="mr-2 h-4 w-4" />
                                        Live Chat
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Confirmation Modal -->
        <ConfirmationModal
            :show="showCancelModal"
            title="Batalkan Pesanan"
            message="Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan."
            @confirm="cancelOrder"
            @cancel="showCancelModal = false"
        />
    </UserLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import OrderStatusBadge from '@/components/User/OrderStatusBadge.vue';
import OrderTimeline from '@/components/User/OrderTimeline.vue';
import PaymentStatusBadge from '@/components/User/PaymentStatusBadge.vue';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, MessageCircle, Phone, RotateCcw, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    special_instructions?: string;
    menu_item: {
        id: number;
        name: string;
        image: string;
        category?: {
            name: string;
        };
    };
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    delivery_date: string;
    delivery_time_slot: string;
    subtotal: number;
    delivery_fee: number;
    tax_amount: number;
    total_amount: number;
    special_instructions?: string;
    created_at: string;
    can_be_cancelled: boolean;
    order_items: OrderItem[];
    delivery_address: {
        recipient_name: string;
        phone_number: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        province: string;
        postal_code: string;
    };
}

const props = defineProps<{
    order: Order;
}>();

const showCancelModal = ref(false);

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
        hour: '2-digit',
        minute: '2-digit',
    });
};

const confirmCancelOrder = () => {
    showCancelModal.value = true;
};

const cancelOrder = () => {
    router.patch(
        route('orders.cancel', props.order.id),
        {},
        {
            onSuccess: () => {
                showCancelModal.value = false;
            },
        },
    );
};

const reorderItems = () => {
    props.order.order_items.forEach((item) => {
        router.post(
            route('cart.add'),
            {
                menu_item_id: item.menu_item.id,
                quantity: item.quantity,
            },
            {
                preserveScroll: true,
            },
        );
    });

    router.visit(route('cart.index'));
};

const downloadInvoice = () => {
    window.open(`/orders/${props.order.id}/invoice`, '_blank');
};
</script>
