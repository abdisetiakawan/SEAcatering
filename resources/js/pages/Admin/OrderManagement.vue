<template>
    <AdminLayout>
        <Head title="Order Management" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
                                <p class="mt-1 text-gray-600">Kelola semua pesanan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-8">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.total_orders }}</div>
                        <div class="text-sm text-gray-600">Total Orders</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending_orders }}</div>
                        <div class="text-sm text-gray-600">Pending</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-orange-600">{{ stats.preparing_orders }}</div>
                        <div class="text-sm text-gray-600">Preparing</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">{{ stats.ready_orders }}</div>
                        <div class="text-sm text-gray-600">Ready</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-purple-600">{{ stats.delivered_orders }}</div>
                        <div class="text-sm text-gray-600">Delivered</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-indigo-600">{{ stats.today_orders }}</div>
                        <div class="text-sm text-gray-600">Today</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-teal-600">{{ stats.direct_orders }}</div>
                        <div class="text-sm text-gray-600">Direct Orders</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-pink-600">{{ stats.subscription_orders }}</div>
                        <div class="text-sm text-gray-600">Subscription Orders</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Cari order..." class="pl-10" @input="handleSearch" />
                            </div>

                            <Select v-model="filters.status" @update:model-value="handleFilter">
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

                            <Select v-model="filters.order_type" @update:model-value="handleFilter">
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

                            <Input v-model="filters.delivery_date" type="date" @change="handleFilter" />
                            <Input v-model="filters.date_from" type="date" placeholder="Dari tanggal" @change="handleFilter" />

                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Delivery Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="order in orders.data" :key="order.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ order.order_number }}</div>
                                        <div class="text-sm text-gray-500">{{ order.plan_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ order.customer_name }}</div>
                                        <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge :variant="getOrderTypeVariant(order.order_type)">
                                            {{ orderTypeOptions[order.order_type] }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ formatDate(order.delivery_date) }}
                                        <div class="text-sm text-gray-500">{{ order.delivery_time_slot }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge :variant="getStatusVariant(order.status)">
                                            {{ statusOptions[order.status] }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">
                                        Rp {{ formatPrice(order.total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Link :href="route('admin.orders.show', order.id)" class="text-indigo-600 hover:text-indigo-900">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                            <Button size="sm" variant="outline" @click="openStatusModal(order)"> Update Status </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.data.length > 0" class="mt-6 rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="orders.links" />
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <OrderStatusModal
            :show="showStatusModal"
            :order="selectedOrder"
            :statusOptions="statusOptions"
            @close="showStatusModal = false"
            @updated="handleStatusUpdated"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import OrderStatusModal from '@/components/Admin/OrderStatusModal.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, RefreshCw, Search } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'out_for_delivery' | 'delivered' | 'cancelled';
type OrderType = 'direct' | 'subscription';

interface Order {
    id: number;
    order_number: string;
    order_type: OrderType;
    customer_name: string;
    customer_email: string;
    delivery_date: string;
    delivery_time_slot: string;
    total_amount: number;
    status: OrderStatus;
    payment_status: string;
    created_at: string;
    plan_name: string;
}

const props = defineProps<{
    orders: { data: Order[]; links: any[]; meta: any };
    filters: Record<string, any>;
    stats: Record<string, number>;
    statusOptions: Record<string, string>;
    orderTypeOptions: Record<string, string>;
}>();

// State
const showStatusModal = ref(false);
const selectedOrder = ref<Order | undefined>(undefined);

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    order_type: props.filters.order_type || 'all',
    delivery_date: props.filters.delivery_date || '',
    date_from: props.filters.date_from || '',
});

// Methods
const openStatusModal = (order: Order) => {
    selectedOrder.value = order;
    showStatusModal.value = true;
};

const handleStatusUpdated = () => {
    showStatusModal.value = false;
    selectedOrder.value = undefined;
    router.reload();
};

const getStatusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        confirmed: 'default',
        preparing: 'outline',
        ready: 'default',
        out_for_delivery: 'outline',
        delivered: 'default',
        cancelled: 'destructive',
    };
    return variants[status] || 'default';
};

const getOrderTypeVariant = (type: string): 'default' | 'secondary' => {
    return type === 'subscription' ? 'default' : 'secondary';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID');
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const handleSearch = () => {
    router.get(route('admin.orders.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.orders.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.keys(filters).forEach((key) => {
        filters[key as keyof typeof filters] = '';
    });
    router.get(route('admin.orders.index'));
};
</script>
