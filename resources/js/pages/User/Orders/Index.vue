<template>
    <UserLayout>
        <Head title="My Orders" />

        <!-- Header -->
        <div class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
                        <p class="mt-1 text-sm text-gray-600">Track and manage your orders</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <Clock class="h-8 w-8 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <ChefHat class="h-8 w-8 text-orange-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Preparing</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.preparing }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <Truck class="h-8 w-8 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Out for Delivery</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.out_for_delivery }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CheckCircle class="h-8 w-8 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Delivered</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.delivered }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div class="relative">
                        <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                        <Input v-model="searchForm.search" placeholder="Search orders..." class="pl-10" @input="handleSearch" />
                    </div>

                    <Select v-model="searchForm.status" @update:model-value="handleFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem v-for="(label, value) in statusOptions" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="searchForm.order_type" @update:model-value="handleFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            <SelectItem v-for="(label, value) in orderTypeOptions" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Input v-model="searchForm.from_date" type="date" placeholder="From Date" @change="handleFilter" />

                    <Button variant="outline" @click="resetFilters">
                        <RefreshCw class="mr-2 h-4 w-4" />
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-6">
                <div v-if="orders.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <Package class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No orders found</h3>
                    <p class="mt-2 text-gray-600">You haven't placed any orders yet.</p>
                    <Button :href="route('user.menu.index')" class="mt-4" as="Link"> Browse Menu </Button>
                </div>

                <OrderCard
                    v-for="order in orders.data"
                    :key="order.id"
                    :order="order"
                    @view-details="viewOrderDetails"
                    @cancel-order="cancelOrder"
                    @reorder="reorderItems"
                    @pay-order="payOrder"
                />
            </div>

            <!-- Pagination -->
            <div v-if="orders.data.length > 0" class="mt-8">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} results</div>
                    <div class="flex space-x-2">
                        <Button v-if="orders.prev_page_url" :href="orders.prev_page_url" variant="outline" size="sm" as="Link"> Previous </Button>
                        <Button v-if="orders.next_page_url" :href="orders.next_page_url" variant="outline" size="sm" as="Link"> Next </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <ConfirmationModal
            :show="showCancelModal"
            title="Cancel Order"
            message="Are you sure you want to cancel this order? This action cannot be undone."
            @close="showCancelModal = false"
            @confirm="confirmCancelOrder"
        />
    </UserLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import OrderCard from '@/components/User/OrderCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle, ChefHat, Clock, Package, RefreshCw, Search, Truck } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

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
    status: string;
    payment_status: string;
    special_instructions?: string;
    created_at: string;
    can_be_cancelled: boolean;
    can_pay: boolean;
    order_items: OrderItem[];
    delivery_address: DeliveryAddress | null;
    payment: Payment | null;
}

interface Props {
    orders: {
        data: Order[];
        from: number;
        to: number;
        total: number;
        prev_page_url?: string;
        next_page_url?: string;
    };
    stats: {
        pending: number;
        preparing: number;
        out_for_delivery: number;
        delivered: number;
    };
    filters: Record<string, any>;
    statusOptions: Record<string, string>;
    orderTypeOptions: Record<string, string>;
}

const props = defineProps<Props>();

// State
const showCancelModal = ref(false);
const selectedOrder = ref<Order | null>(null);

const searchForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    order_type: props.filters.order_type || 'all',
    from_date: props.filters.from_date || '',
});

// Methods
const handleSearch = () => {
    router.get(route('user.orders.index'), searchForm, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('user.orders.index'), searchForm, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.keys(searchForm).forEach((key) => {
        searchForm[key as keyof typeof searchForm] = '';
    });
    router.get(route('user.orders.index'));
};

const viewOrderDetails = (order: Order) => {
    router.visit(route('user.orders.show', order.id));
};

const cancelOrder = (order: Order) => {
    selectedOrder.value = order;
    showCancelModal.value = true;
};

const confirmCancelOrder = () => {
    if (selectedOrder.value) {
        router.patch(
            route('user.orders.cancel', selectedOrder.value.id),
            {},
            {
                onSuccess: () => {
                    showCancelModal.value = false;
                    selectedOrder.value = null;
                },
            },
        );
    }
};

const reorderItems = (order: Order) => {
    router.post(
        route('user.orders.reorder', order.id),
        {},
        {
            onSuccess: () => {
                router.visit(route('user.cart.index'));
            },
        },
    );
};

const payOrder = (order: Order) => {
    router.visit(route('user.orders.payment', order.id));
};
</script>
