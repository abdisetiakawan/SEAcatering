<template>
    <AdminLayout>
        <Head title="Admin Dashboard" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                                <p class="mt-1 text-gray-600">Selamat datang kembali, {{ $page.props.auth.user.name }}!</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Hari ini</p>
                                    <p class="text-lg font-semibold">{{ currentDate }}</p>
                                </div>
                                <Button @click="refreshData" variant="outline" size="sm" :disabled="refreshing">
                                    <RefreshCw class="mr-2 h-4 w-4" :class="{ 'animate-spin': refreshing }" />
                                    Refresh
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Revenue -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                    <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.totalRevenue) }}</p>
                                    <p class="mt-1 text-xs" :class="stats.revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                                        <component :is="stats.revenueGrowth >= 0 ? TrendingUp : TrendingDown" class="mr-1 inline h-3 w-3" />
                                        {{ stats.revenueGrowth >= 0 ? '+' : '' }}{{ stats.revenueGrowth }}% dari bulan lalu
                                    </p>
                                </div>
                                <div class="rounded-full bg-green-100 p-3">
                                    <DollarSign class="h-6 w-6 text-green-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Active Subscriptions -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Active Subscriptions</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ stats.activeSubscriptions }}</p>
                                    <p class="mt-1 text-xs text-blue-600">
                                        <Users class="mr-1 inline h-3 w-3" />
                                        +{{ stats.newSubscriptions }} baru minggu ini
                                    </p>
                                </div>
                                <div class="rounded-full bg-blue-100 p-3">
                                    <Users class="h-6 w-6 text-blue-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Today's Orders -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Today's Orders</p>
                                    <p class="text-2xl font-bold text-orange-600">{{ stats.todayOrders }}</p>
                                    <p class="mt-1 text-xs text-orange-600">
                                        <Package class="mr-1 inline h-3 w-3" />
                                        {{ stats.pendingOrders }} pending
                                    </p>
                                </div>
                                <div class="rounded-full bg-orange-100 p-3">
                                    <Package class="h-6 w-6 text-orange-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Customer Satisfaction -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Customer Rating</p>
                                    <p class="text-2xl font-bold text-purple-600">{{ stats.averageRating }}/5</p>
                                    <p class="mt-1 text-xs text-purple-600">
                                        <Star class="mr-1 inline h-3 w-3" />
                                        {{ stats.totalReviews }} reviews
                                    </p>
                                </div>
                                <div class="rounded-full bg-purple-100 p-3">
                                    <Star class="h-6 w-6 text-purple-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Charts Section -->
                <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Revenue Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between">
                                <span>Revenue Trend</span>
                                <select
                                    v-model="revenueFilter"
                                    @change="updateRevenueChart"
                                    class="rounded border px-2 py-1 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                >
                                    <option value="7">7 Days</option>
                                    <option value="30">30 Days</option>
                                    <option value="90">90 Days</option>
                                </select>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <RevenueChart :data="revenueChartData" :loading="chartLoading.revenue" title="Daily Revenue" />
                        </CardContent>
                    </Card>

                    <!-- Subscription Growth -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between">
                                <span>Subscription Growth</span>
                                <select
                                    v-model="subscriptionFilter"
                                    @change="updateSubscriptionChart"
                                    class="rounded border px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="7">7 Days</option>
                                    <option value="30">30 Days</option>
                                    <option value="90">90 Days</option>
                                </select>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <SubscriptionChart :data="subscriptionChartData" :loading="chartLoading.subscription" title="Subscription Trends" />
                        </CardContent>
                    </Card>
                </div>

                <!-- Quick Actions & Recent Activity -->
                <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button @click="navigateTo('admin.menu.index')" variant="outline" class="w-full justify-start">
                                <Plus class="mr-2 h-4 w-4" />
                                Add New Menu Item
                            </Button>
                            <Button @click="navigateTo('admin.plans.index')" variant="outline" class="w-full justify-start">
                                <Package class="mr-2 h-4 w-4" />
                                Create Meal Plan
                            </Button>
                            <Button @click="navigateTo('admin.orders.index')" variant="outline" class="w-full justify-start">
                                <Truck class="mr-2 h-4 w-4" />
                                View Orders
                            </Button>
                            <Button @click="navigateTo('admin.users.index')" variant="outline" class="w-full justify-start">
                                <Users class="mr-2 h-4 w-4" />
                                Manage Users
                            </Button>
                            <Button @click="navigateTo('admin.inventory.index')" variant="outline" class="w-full justify-start">
                                <AlertTriangle class="mr-2 h-4 w-4" />
                                Check Inventory
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Recent Orders -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between">
                                <span>Recent Orders</span>
                                <Button @click="navigateTo('admin.orders.index')" variant="ghost" size="sm"> View All </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="order in recentOrders"
                                    :key="order.id"
                                    class="flex cursor-pointer items-center justify-between rounded-lg border p-3 transition-colors hover:bg-gray-50"
                                    @click="navigateTo('admin.orders.show', { order: order.id })"
                                >
                                    <div>
                                        <p class="text-sm font-medium">{{ order.customer }}</p>
                                        <p class="text-xs text-gray-600">{{ order.plan }} • {{ order.created_at }}</p>
                                        <p class="text-xs text-gray-500">{{ formatCurrency(order.total_amount) }}</p>
                                    </div>
                                    <Badge :class="getOrderStatusClass(order.status)" class="text-xs">
                                        {{ order.status }}
                                    </Badge>
                                </div>
                                <div v-if="recentOrders.length === 0" class="py-4 text-center text-gray-500">
                                    <Package class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                                    <p class="text-sm">No recent orders</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Low Stock Alerts -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between">
                                <span class="flex items-center">
                                    <AlertTriangle class="mr-2 h-4 w-4 text-red-500" />
                                    Stock Alerts
                                </span>
                                <Button @click="navigateTo('admin.inventory.index')" variant="ghost" size="sm"> View All </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="item in lowStockItems"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                    :class="item.status === 'critical' ? 'border-red-200 bg-red-50' : 'border-orange-200 bg-orange-50'"
                                >
                                    <div>
                                        <p class="text-sm font-medium">{{ item.name }}</p>
                                        <p class="text-xs text-gray-600">{{ item.current }} {{ item.unit }} remaining</p>
                                        <p class="text-xs text-gray-500">Min: {{ item.minimum }} {{ item.unit }}</p>
                                    </div>
                                    <Badge
                                        :class="item.status === 'critical' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800'"
                                        class="text-xs"
                                    >
                                        {{ item.status }}
                                    </Badge>
                                </div>
                                <div v-if="lowStockItems.length === 0" class="py-4 text-center text-gray-500">
                                    <AlertTriangle class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                                    <p class="text-sm">All items in stock</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Today's Schedule & Performance -->
                <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Today's Delivery Schedule -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Today's Delivery Schedule</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="slot in deliverySchedule"
                                    :key="slot.time"
                                    class="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="text-center">
                                            <p class="text-sm font-semibold">{{ slot.time }}</p>
                                            <p class="text-xs text-gray-600">{{ slot.period }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ slot.total_orders }} Orders</p>
                                            <p class="text-sm text-gray-600">{{ slot.drivers_assigned }} drivers assigned</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <Badge :class="getDeliveryStatusClass(slot.status)" class="text-xs">
                                            {{ slot.status.replace('_', ' ') }}
                                        </Badge>
                                        <p class="mt-1 text-xs text-gray-600">{{ slot.completed }}/{{ slot.total_orders }} completed</p>
                                    </div>
                                </div>
                                <div v-if="deliverySchedule.length === 0" class="py-4 text-center text-gray-500">
                                    <Truck class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                                    <p class="text-sm">No deliveries scheduled for today</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Performance Metrics -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Performance Metrics</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Order Fulfillment Rate -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium">Order Fulfillment Rate</span>
                                        <span class="text-sm font-semibold">{{ performance.fulfillmentRate }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full bg-green-600 transition-all duration-300"
                                            :style="{ width: performance.fulfillmentRate + '%' }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- On-Time Delivery -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium">On-Time Delivery</span>
                                        <span class="text-sm font-semibold">{{ performance.onTimeDelivery }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full bg-blue-600 transition-all duration-300"
                                            :style="{ width: performance.onTimeDelivery + '%' }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Customer Satisfaction -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium">Customer Satisfaction</span>
                                        <span class="text-sm font-semibold">{{ performance.customerSatisfaction }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full bg-purple-600 transition-all duration-300"
                                            :style="{ width: performance.customerSatisfaction + '%' }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Kitchen Efficiency -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium">Kitchen Efficiency</span>
                                        <span class="text-sm font-semibold">{{ performance.kitchenEfficiency }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full bg-orange-600 transition-all duration-300"
                                            :style="{ width: performance.kitchenEfficiency + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Activity Feed -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                class="flex items-start space-x-3 border-l-4 border-gray-200 p-3 transition-colors hover:border-green-500 hover:bg-gray-50"
                            >
                                <div
                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full"
                                    :class="getActivityIconClass(activity.type)"
                                >
                                    <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                                    <p class="text-sm text-gray-600">{{ activity.description }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ activity.time }}</p>
                                </div>
                            </div>
                            <div v-if="recentActivity.length === 0" class="py-4 text-center text-gray-500">
                                <Clock class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                                <p class="text-sm">No recent activity</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import RevenueChart from '@/components/Admin/RevenueChart.vue';
import SubscriptionChart from '@/components/Admin/SubscriptionChart.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlertTriangle,
    Clock,
    DollarSign,
    Package,
    Plus,
    RefreshCw,
    ShoppingCart,
    Star,
    TrendingDown,
    TrendingUp,
    Truck,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Props from backend
const props = defineProps<{
    stats: {
        totalRevenue: number;
        revenueGrowth: number;
        activeSubscriptions: number;
        newSubscriptions: number;
        todayOrders: number;
        pendingOrders: number;
        averageRating: number;
        totalReviews: number;
    };
    recentOrders: Array<{
        id: number;
        customer: string;
        plan: string;
        created_at: string;
        status: string;
        total_amount: number;
    }>;
    lowStockItems: Array<{
        id: number;
        name: string;
        current: number;
        minimum: number;
        unit: string;
        status: string;
    }>;
    deliverySchedule: Array<{
        time: string;
        period: string;
        total_orders: number;
        drivers_assigned: number;
        status: string;
        completed: number;
    }>;
    performance: {
        fulfillmentRate: number;
        onTimeDelivery: number;
        customerSatisfaction: number;
        kitchenEfficiency: number;
    };
    recentActivity: Array<{
        id: string;
        type: string;
        title: string;
        description: string;
        time: string;
    }>;
    revenueChart: {
        labels: string[];
        datasets: Array<{
            label: string;
            data: number[];
            borderColor: string;
            backgroundColor: string;
            tension: number;
            fill: boolean;
        }>;
    };
    subscriptionGrowth: {
        labels: string[];
        datasets: Array<{
            label: string;
            data: number[];
            borderColor: string;
            backgroundColor: string;
            tension: number;
            fill: boolean;
        }>;
    };
}>();

// Reactive data
const revenueFilter = ref('30');
const subscriptionFilter = ref('30');
const refreshing = ref(false);
const chartLoading = ref({
    revenue: false,
    subscription: false,
});

const revenueChartData = ref(props.revenueChart);
const subscriptionChartData = ref(props.subscriptionGrowth);

// Computed properties
const currentDate = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

// Methods
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const navigateTo = (routeName: string, params?: any) => {
    if (params) {
        router.visit(route(routeName, params));
    } else {
        router.visit(route(routeName));
    }
};

const refreshData = async () => {
    refreshing.value = true;
    try {
        await axios.post(route('admin.dashboard.refresh'));
        router.reload({ only: ['stats', 'recentOrders', 'lowStockItems', 'deliverySchedule', 'performance', 'recentActivity'] });
    } catch (error) {
        console.error('Failed to refresh data:', error);
    } finally {
        refreshing.value = false;
    }
};

const updateRevenueChart = async () => {
    chartLoading.value.revenue = true;
    try {
        const response = await axios.get(route('admin.dashboard.chart-data'), {
            params: { type: 'revenue', period: revenueFilter.value },
        });
        revenueChartData.value = response.data;
    } catch (error) {
        console.error('Failed to update revenue chart:', error);
    } finally {
        chartLoading.value.revenue = false;
    }
};

const updateSubscriptionChart = async () => {
    chartLoading.value.subscription = true;
    try {
        const response = await axios.get(route('admin.dashboard.chart-data'), {
            params: { type: 'subscriptions', period: subscriptionFilter.value },
        });
        subscriptionChartData.value = response.data;
    } catch (error) {
        console.error('Failed to update subscription chart:', error);
    } finally {
        chartLoading.value.subscription = false;
    }
};

const getOrderStatusClass = (status: string) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        preparing: 'bg-orange-100 text-orange-800',
        ready: 'bg-purple-100 text-purple-800',
        out_for_delivery: 'bg-indigo-100 text-indigo-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getDeliveryStatusClass = (status: string) => {
    const classes = {
        on_schedule: 'bg-green-100 text-green-800',
        behind_schedule: 'bg-yellow-100 text-yellow-800',
        delayed: 'bg-red-100 text-red-800',
        completed: 'bg-blue-100 text-blue-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getActivityIcon = (type: string) => {
    const icons = {
        order: ShoppingCart,
        user: UserPlus,
        payment: DollarSign,
        review: Star,
        delivery: Truck,
    };
    return icons[type as keyof typeof icons] || Clock;
};

const getActivityIconClass = (type: string) => {
    const classes = {
        order: 'bg-blue-100 text-blue-600',
        user: 'bg-green-100 text-green-600',
        payment: 'bg-yellow-100 text-yellow-600',
        review: 'bg-purple-100 text-purple-600',
        delivery: 'bg-indigo-100 text-indigo-600',
    };
    return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-600';
};

onMounted(() => {
    // Auto-refresh data every 5 minutes
    setInterval(() => {
        if (!refreshing.value) {
            refreshData();
        }
    }, 300000); // 5 minutes
});
</script>
