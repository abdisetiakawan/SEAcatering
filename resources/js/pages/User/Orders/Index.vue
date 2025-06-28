<template>
    <UserLayout>
        <Head title="My Orders" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
                                <p class="mt-1 text-gray-600">Track and manage your food orders</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total Orders</p>
                                    <p class="text-lg font-semibold">{{ orders.total }}</p>
                                </div>
                                <Button @click="refreshOrders" variant="outline" size="sm">
                                    <RefreshCw class="mr-2 h-4 w-4" />
                                    Refresh
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ stats.pending }}</div>
                                <div class="text-sm text-gray-600">Pending Orders</div>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3">
                                <Clock class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-orange-600">{{ stats.preparing }}</div>
                                <div class="text-sm text-gray-600">Being Prepared</div>
                            </div>
                            <div class="rounded-full bg-orange-100 p-3">
                                <ChefHat class="h-6 w-6 text-orange-600" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-purple-600">{{ stats.out_for_delivery }}</div>
                                <div class="text-sm text-gray-600">Out for Delivery</div>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3">
                                <Truck class="h-6 w-6 text-purple-600" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ stats.delivered }}</div>
                                <div class="text-sm text-gray-600">Delivered</div>
                            </div>
                            <div class="rounded-full bg-green-100 p-3">
                                <CheckCircle class="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Search orders..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select v-model="filters.status" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem v-for="(label, value) in orderStatuses" :key="value" :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div class="grid grid-cols-2 gap-2">
                                <Input v-model="filters.from_date" type="date" placeholder="From Date" @change="handleFilter" />
                                <Input v-model="filters.to_date" type="date" placeholder="To Date" @change="handleFilter" />
                            </div>
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="space-y-4">
                    <OrderCard
                        v-for="order in orders.data"
                        :key="order.id"
                        :order="order"
                        @view-details="viewOrderDetails"
                        @cancel-order="confirmCancelOrder"
                        @reorder="reorderItems"
                    />
                </div>

                <!-- Empty State -->
                <div v-if="orders.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <ShoppingBag class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">No orders found</h3>
                    <p class="mb-6 text-gray-500">You haven't placed any orders yet</p>
                    <Button @click="navigateToMenu" class="bg-green-600 hover:bg-green-700">
                        <Plus class="mr-2 h-4 w-4" />
                        Browse Menu
                    </Button>
                </div>

                <!-- Pagination -->
                <div v-if="orders.data.length > 0" class="mt-6 rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="orders.links" />
                </div>
            </div>
        </div>

        <!-- Cancel Order Confirmation Modal -->
        <ConfirmationModal
            :show="showCancelModal"
            title="Cancel Order"
            message="Are you sure you want to cancel this order? This action cannot be undone."
            @confirm="cancelOrder"
            @cancel="showCancelModal = false"
        />
    </UserLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import OrderCard from '@/components/User/OrderCard.vue';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle, ChefHat, Clock, Plus, RefreshCw, Search, ShoppingBag, Truck } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface Order {
    id: number;
    order_number: string;
    delivery_date: string;
    delivery_time: string;
    total_amount: number;
    status: string;
    payment_status: string;
    special_instructions?: string;
    created_at: string;
    order_items: Array<{
        id: number;
        quantity: number;
        price: number;
        total: number;
        menu_item: {
            id: number;
            name: string;
            image: string;
        };
    }>;
    delivery_address: {
        id: number;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        province: string;
        postal_code: string;
    };
}

const props = defineProps<{
    orders: {
        current_page: number;
        data: Order[];
        first_page_url: string;
        from: number | null;
        last_page: number;
        last_page_url: string;
        links: any[];
        next_page_url: string | null;
        path: string;
        per_page: number;
        prev_page_url: string | null;
        to: number | null;
        total: number;
    };

    stats: {
        pending: number;
        preparing: number;
        out_for_delivery: number;
        delivered: number;
    };
    orderStatuses: Record<string, string>;
    filters: Record<string, any>;
}>();

// State
const showCancelModal = ref(false);
const orderToCancel = ref<Order | null>(null);

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    from_date: props.filters.from_date || '',
    to_date: props.filters.to_date || '',
});

// Methods
const refreshOrders = () => {
    router.reload();
};

const handleSearch = () => {
    router.get(route('user.orders.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('user.orders.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.keys(filters).forEach((key) => {
        filters[key as keyof typeof filters] = '';
    });
    router.get(route('user.orders.index'));
};

const viewOrderDetails = (order: Order) => {
    router.visit(route('user.orders.show', order.id));
};

const confirmCancelOrder = (order: Order) => {
    orderToCancel.value = order;
    showCancelModal.value = true;
};

const cancelOrder = () => {
    if (orderToCancel.value) {
        router.patch(
            route('user.orders.cancel', orderToCancel.value.id),
            {},
            {
                onSuccess: () => {
                    showCancelModal.value = false;
                    orderToCancel.value = null;
                    router.reload();
                },
            },
        );
    }
};

const reorderItems = (order: Order) => {
    // Add all items from this order to cart
    order.order_items.forEach((item) => {
        router.post(
            route('user.cart.add'),
            {
                menu_item_id: item.menu_item.id,
                quantity: item.quantity,
            },
            {
                preserveScroll: true,
            },
        );
    });

    router.visit(route('user.cart.index'));
};

const navigateToMenu = () => {
    router.visit(route('user.menu.index'));
};
</script>
