<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Bell, Calendar, DollarSign, MessageCircle, Package, Plus, Settings, Utensils } from 'lucide-vue-next';
import { route } from 'ziggy-js';

interface UserStats {
    total_orders: number;
    total_spent: number;
    active_subscriptions: number;
    reward_points: number;
}

interface RecentOrder {
    id: number;
    order_number: string;
    status: string;
    total_amount: number;
    created_at: string;
    items_count: number;
}

interface ActiveSubscription {
    id: number;
    meal_plan: {
        id: number;
        name: string;
        plan_type: string;
    };
    status: string;
    next_delivery?: string;
    delivery_frequency: string;
}

interface Props {
    userStats: UserStats;
    recentOrders: RecentOrder[];
    activeSubscriptions: ActiveSubscription[];
    user: {
        name: string;
        email: string;
    };
}

defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        preparing: 'bg-orange-100 text-orange-800',
        ready: 'bg-green-100 text-green-800',
        delivered: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        active: 'bg-green-100 text-green-800',
        paused: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <UserLayout>
        <Head title="User Dashboard" />
        <div class="py-6">
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <!-- Welcome Section -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">Selamat Datang, {{ user.name }}!</h1>
                            <p class="text-muted-foreground">Kelola langganan dan preferensi Anda di SEA Catering</p>
                        </div>
                        <div class="flex space-x-2">
                            <Button variant="outline" size="sm">
                                <Bell class="mr-2 h-4 w-4" />
                                Notifikasi
                            </Button>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('profile.edit')">
                                    <Settings class="mr-2 h-4 w-4" />
                                    Pengaturan
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Langganan Aktif</CardTitle>
                                <Package class="h-8 w-8 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-green-600">{{ userStats.active_subscriptions }}</div>
                                <p class="text-xs text-muted-foreground">Currently active plans</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Meals Bulan Ini</CardTitle>
                                <Utensils class="h-8 w-8 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-blue-600">{{ userStats.total_orders }}</div>
                                <p class="text-xs text-muted-foreground">Total orders placed</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Total Pengeluaran</CardTitle>
                                <DollarSign class="h-8 w-8 text-purple-600" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(userStats.total_spent) }}</div>
                                <p class="text-xs text-muted-foreground">Lifetime spending</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid gap-4 md:grid-cols-3">
                        <Button class="h-20 flex-col" as-child>
                            <Link :href="route('user.meal-plans.index')">
                                <Plus class="mb-2 h-6 w-6" />
                                Tambah Langganan
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col" as-child>
                            <Link :href="route('user.orders.index')">
                                <Calendar class="mb-2 h-6 w-6" />
                                Jadwal Pengiriman
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col">
                            <MessageCircle class="mb-2 h-6 w-6" />
                            Customer Support
                        </Button>
                    </div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- Recent Orders -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Recent Orders</CardTitle>
                                        <CardDescription>Your latest food orders</CardDescription>
                                    </div>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="route('user.orders.index')">View All</Link>
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div
                                        v-for="order in recentOrders"
                                        :key="order.id"
                                        class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-accent/50"
                                    >
                                        <div class="flex items-center space-x-4">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                                <Package class="h-5 w-5 text-primary" />
                                            </div>
                                            <div>
                                                <p class="font-medium">#{{ order.order_number }}</p>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ order.items_count }} items • {{ formatDate(order.created_at) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-medium">{{ formatCurrency(order.total_amount) }}</p>
                                            <Badge :class="getStatusColor(order.status)" class="text-xs">
                                                {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div v-if="recentOrders.length === 0" class="py-8 text-center text-muted-foreground">
                                        <Package class="mx-auto mb-4 h-12 w-12 opacity-50" />
                                        <p>No orders yet</p>
                                        <Button class="mt-2" size="sm" as-child>
                                            <Link :href="route('user.menu.index')">Start Ordering</Link>
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Active Subscriptions -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Active Subscriptions</CardTitle>
                                        <CardDescription>Your meal plan subscriptions</CardDescription>
                                    </div>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="route('user.subscriptions.index')">Manage</Link>
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div
                                        v-for="subscription in activeSubscriptions"
                                        :key="subscription.id"
                                        class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-accent/50"
                                    >
                                        <div class="flex items-center space-x-4">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                                <Calendar class="h-5 w-5 text-green-600" />
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ subscription.meal_plan.name }}</p>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ subscription.delivery_frequency }} • {{ subscription.meal_plan.plan_type }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <Badge :class="getStatusColor(subscription.status)" class="mb-1 text-xs">
                                                {{ subscription.status.charAt(0).toUpperCase() + subscription.status.slice(1) }}
                                            </Badge>
                                            <p class="text-xs text-muted-foreground">
                                                Next: {{ subscription.next_delivery ? formatDate(subscription.next_delivery) : 'TBD' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="activeSubscriptions.length === 0" class="py-8 text-center text-muted-foreground">
                                        <Calendar class="mx-auto mb-4 h-12 w-12 opacity-50" />
                                        <p>No active subscriptions</p>
                                        <Button class="mt-2" size="sm" as-child>
                                            <Link :href="route('user.meal-plans.index')">Browse Plans</Link>
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
