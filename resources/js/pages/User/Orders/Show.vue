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

                        <!-- Payment Information -->
                        <div v-if="order.payment" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Informasi Pembayaran</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Metode Pembayaran</h3>
                                        <div class="mt-2 flex items-center">
                                            <component :is="getPaymentIcon(order.payment.payment_method)" class="mr-2 h-5 w-5 text-gray-600" />
                                            <span class="text-sm text-gray-600">{{ getPaymentMethodName(order.payment.payment_method) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Status Pembayaran</h3>
                                        <div class="mt-2">
                                            <PaymentStatusBadge :status="order.payment_status" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Total Pembayaran</h3>
                                        <p class="mt-1 text-sm font-semibold text-green-600">{{ formatCurrency(order.payment.amount) }}</p>
                                    </div>
                                    <div v-if="order.payment.payment_date">
                                        <h3 class="text-sm font-medium text-gray-900">Tanggal Pembayaran</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ formatDate(order.payment.payment_date) }}</p>
                                    </div>
                                </div>
                                <div v-if="order.payment.transaction_id" class="mt-4">
                                    <h3 class="text-sm font-medium text-gray-900">ID Transaksi</h3>
                                    <p class="mt-1 font-mono text-sm text-gray-600">{{ order.payment.transaction_id }}</p>
                                </div>
                                <div v-if="order.payment.notes" class="mt-4">
                                    <h3 class="text-sm font-medium text-gray-900">Catatan Pembayaran</h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ order.payment.notes }}</p>
                                </div>
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
                                            :src="
                                                item.menu_item.image?.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image
                                            "
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

                                            <!-- Review Section -->
                                            <div v-if="item.has_review" class="mt-3 rounded-md bg-yellow-50 p-3">
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex">
                                                        <Star
                                                            v-for="i in 5"
                                                            :key="i"
                                                            :class="[
                                                                'h-4 w-4',
                                                                i <= item.review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300',
                                                            ]"
                                                        />
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">{{ item.review.rating }}/5</span>
                                                </div>
                                                <p v-if="item.review.comment" class="mt-2 text-sm text-gray-700">{{ item.review.comment }}</p>
                                                <p class="mt-1 text-xs text-gray-500">Direview pada {{ formatDate(item.review.created_at) }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(item.total_price) }}</p>

                                            <!-- Review Button -->
                                            <div v-if="item.can_review" class="mt-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="goToReview(item.menu_item.id)"
                                                    class="text-blue-600 hover:text-blue-700"
                                                >
                                                    <Star class="mr-1 h-4 w-4" />
                                                    Beri Review
                                                </Button>
                                            </div>
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
                                            <p>{{ order.delivery_address.full_address }}</p>
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
                                <Button v-if="order.can_pay" @click="payOrder" variant="default" class="w-full">
                                    <CreditCard class="mr-2 h-4 w-4" />
                                    Bayar Sekarang
                                </Button>
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
import { Banknote, Building2, CreditCard, Download, MessageCircle, Phone, RotateCcw, Smartphone, Star, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Review {
    id: number;
    rating: number;
    comment?: string;
    created_at: string;
}

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    can_review: boolean;
    has_review: boolean;
    review?: Review;
    menu_item: {
        id: number;
        name: string;
        image: string;
        category?: {
            name: string;
        };
    };
}

interface Payment {
    id: number;
    payment_number: string;
    amount: number;
    status: string;
    payment_method: string;
    payment_date?: string;
    transaction_id?: string;
    notes?: string;
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
    can_pay: boolean;
    order_items: OrderItem[];
    payment?: Payment;
    delivery_address: {
        recipient_name: string;
        phone_number: string;
        full_address: string;
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
        bank_transfer: 'Transfer Bank',
        e_wallet: 'E-Wallet',
        credit_card: 'Kartu Kredit',
        cash_on_delivery: 'Bayar di Tempat',
    };
    return names[paymentMethod as keyof typeof names] || paymentMethod;
};

const confirmCancelOrder = () => {
    showCancelModal.value = true;
};

const cancelOrder = () => {
    router.patch(
        route('user.orders.cancel', props.order.id),
        {},
        {
            onSuccess: () => {
                showCancelModal.value = false;
            },
        },
    );
};

const reorderItems = () => {
    router.post(
        route('user.orders.reorder', props.order.id),
        {},
        {
            onSuccess: () => {
                router.visit(route('user.cart.index'));
            },
        },
    );
};

const downloadInvoice = () => {
    router.visit(route('user.orders.invoice', props.order.id));
};

const payOrder = () => {
    router.visit(route('user.orders.payment', props.order.id));
};

const goToReview = (menuItemId: number) => {
    router.visit(route('user.reviews.create', [props.order.id, menuItemId]));
};
</script>
