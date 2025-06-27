<template>
    <AdminLayout>
        <Head title="User Details" />
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Details</h1>
                            <p class="text-gray-600 dark:text-gray-400">View and manage user information</p>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" @click="$inertia.visit(route('admin.users.index'))">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Users
                            </Button>
                            <Button @click="editUser">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit User
                            </Button>
                        </div>
                    </div>

                    <!-- User Stats Cards -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardContent class="p-6">
                                <div class="flex items-center">
                                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                                        <ShoppingCart class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Orders</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ userStats.total_orders }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent class="p-6">
                                <div class="flex items-center">
                                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                                        <DollarSign class="h-6 w-6 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Spent</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(userStats.total_spent) }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent class="p-6">
                                <div class="flex items-center">
                                    <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900">
                                        <Calendar class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Subscriptions</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ userStats.active_subscriptions }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent class="p-6">
                                <div class="flex items-center">
                                    <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900">
                                        <Star class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Rating</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ userStats.average_rating ? userStats.average_rating.toFixed(1) : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- User Information -->
                        <div class="lg:col-span-1">
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <User class="h-5 w-5" />
                                        User Information
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                                            <User class="h-8 w-8 text-gray-500 dark:text-gray-400" />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</h3>
                                            <p class="text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                                            <div class="mt-1 flex items-center gap-2">
                                                <Badge :variant="user.role === 'admin' ? 'default' : 'secondary'">
                                                    {{ user.role }}
                                                </Badge>
                                                <Badge :variant="user.email_verified_at ? 'default' : 'destructive'">
                                                    {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>

                                    <Separator />

                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                            <p class="text-gray-900 dark:text-white">{{ user.phone || 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Member Since</label>
                                            <p class="text-gray-900 dark:text-white">{{ formatDate(user.created_at) }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                                            <p class="text-gray-900 dark:text-white">{{ formatDate(user.updated_at) }}</p>
                                        </div>
                                    </div>

                                    <Separator />

                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" @click="toggleAdminStatus" :disabled="processing">
                                            {{ user.role === 'admin' ? 'Remove Admin' : 'Make Admin' }}
                                        </Button>
                                        <Button
                                            v-if="!user.email_verified_at"
                                            variant="outline"
                                            size="sm"
                                            @click="verifyEmail"
                                            :disabled="processing"
                                        >
                                            Verify Email
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Detailed Information -->
                        <div class="space-y-6 lg:col-span-2">
                            <!-- Addresses -->
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <MapPin class="h-5 w-5" />
                                        Addresses ({{ user.addresses?.length || 0 }})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="user.addresses && user.addresses.length > 0" class="space-y-4">
                                        <div
                                            v-for="address in user.addresses"
                                            :key="address.id"
                                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">{{ address.label || 'Address' }}</p>
                                                    <p class="text-gray-600 dark:text-gray-400">{{ address.address_line_1 }}</p>
                                                    <p v-if="address.address_line_2" class="text-gray-600 dark:text-gray-400">
                                                        {{ address.address_line_2 }}
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        {{ address.city }}, {{ address.state }} {{ address.postal_code }}
                                                    </p>
                                                    <p v-if="address.country" class="text-gray-600 dark:text-gray-400">{{ address.country }}</p>
                                                </div>
                                                <Badge v-if="address.is_default" variant="default">Default</Badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">No addresses found</div>
                                </CardContent>
                            </Card>

                            <!-- Subscriptions -->
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <Calendar class="h-5 w-5" />
                                        Subscriptions ({{ user.subscriptions?.length || 0 }})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="user.subscriptions && user.subscriptions.length > 0" class="space-y-4">
                                        <div
                                            v-for="subscription in user.subscriptions"
                                            :key="subscription.id"
                                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        {{ subscription.meal_plan?.name || 'Unknown Plan' }}
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">Started: {{ formatDate(subscription.start_date) }}</p>
                                                    <p v-if="subscription.end_date" class="text-gray-600 dark:text-gray-400">
                                                        Ends: {{ formatDate(subscription.end_date) }}
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        Next Billing: {{ formatDate(subscription.next_billing_date) }}
                                                    </p>
                                                </div>
                                                <Badge :variant="getSubscriptionStatusVariant(subscription.status)">
                                                    {{ subscription.status }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">No subscriptions found</div>
                                </CardContent>
                            </Card>

                            <!-- Recent Orders -->
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <ShoppingCart class="h-5 w-5" />
                                        Recent Orders ({{ user.orders?.length || 0 }})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="user.orders && user.orders.length > 0" class="space-y-4">
                                        <div
                                            v-for="order in user.orders.slice(0, 5)"
                                            :key="order.id"
                                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">Order #{{ order.id }}</p>
                                                    <p class="text-gray-600 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                                                    <p class="text-gray-600 dark:text-gray-400">{{ order.order_items?.length || 0 }} items</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-medium text-gray-900 dark:text-white">${{ formatCurrency(order.total_amount) }}</p>
                                                    <Badge :variant="getOrderStatusVariant(order.status)">
                                                        {{ order.status }}
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.orders.length > 5" class="text-center">
                                            <Button variant="outline" size="sm"> View All Orders </Button>
                                        </div>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">No orders found</div>
                                </CardContent>
                            </Card>

                            <!-- Reviews -->
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <Star class="h-5 w-5" />
                                        Reviews ({{ user.reviews?.length || 0 }})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="user.reviews && user.reviews.length > 0" class="space-y-4">
                                        <div
                                            v-for="review in user.reviews.slice(0, 3)"
                                            :key="review.id"
                                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                        >
                                            <div class="mb-2 flex items-start justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        {{ review.menu_item?.name || 'Unknown Item' }}
                                                    </p>
                                                    <div class="flex items-center gap-1">
                                                        <Star
                                                            v-for="i in 5"
                                                            :key="i"
                                                            class="h-4 w-4"
                                                            :class="i <= review.rating ? 'fill-current text-yellow-400' : 'text-gray-300'"
                                                        />
                                                        <span class="ml-1 text-sm text-gray-600 dark:text-gray-400"> {{ review.rating }}/5 </span>
                                                    </div>
                                                </div>
                                                <Badge :variant="review.is_published ? 'default' : 'secondary'">
                                                    {{ review.is_published ? 'Published' : 'Draft' }}
                                                </Badge>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ review.comment }}</p>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                                {{ formatDate(review.created_at) }}
                                            </p>
                                        </div>
                                        <div v-if="user.reviews.length > 3" class="text-center">
                                            <Button variant="outline" size="sm"> View All Reviews </Button>
                                        </div>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">No reviews found</div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <UserFormModal :show="showUserModal" :user="selectedUser" :is-editing="isEditing" @close="closeUserModal" @saved="handleUserSaved" />
    </AdminLayout>
</template>

<script setup lang="ts">
import UserFormModal from '@/components/Admin/UserFormModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, DollarSign, Edit, MapPin, ShoppingCart, Star, User } from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';

// Types
interface Address {
    id: number;
    label?: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    postal_code: string;
    country?: string;
    is_default: boolean;
}

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: number;
}

interface Subscription {
    id: number;
    status: string;
    start_date: string;
    end_date?: string;
    next_billing_date: string;
    meal_plan?: MealPlan;
}

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
}

interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    menu_item?: MenuItem;
}

interface Order {
    id: number;
    status: string;
    total_amount: number;
    created_at: string;
    order_items?: OrderItem[];
}

interface Review {
    id: number;
    rating: number;
    comment: string;
    is_published: boolean;
    created_at: string;
    menu_item?: MenuItem;
}

interface UserDetails {
    id: number;
    name: string;
    email: string;
    phone?: string;
    role: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
    addresses?: Address[];
    subscriptions?: Subscription[];
    orders?: Order[];
    reviews?: Review[];
}

interface UserStats {
    total_orders: number;
    total_spent: number;
    active_subscriptions: number;
    average_rating: number | null;
}

interface Props {
    user: UserDetails;
    userStats: UserStats;
}

const props = defineProps<Props>();
const processing = ref(false);

// Edit User Modal State
const showUserModal = ref(false);
const isEditing = ref(false);
const selectedUser = ref<UserDetails | null>(null);

// Helper functions
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getSubscriptionStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'cancelled':
            return 'destructive';
        case 'paused':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getOrderStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'cancelled':
            return 'destructive';
        case 'pending':
            return 'secondary';
        case 'processing':
            return 'outline';
        default:
            return 'outline';
    }
};

// Edit User Actions
const editUser = () => {
    selectedUser.value = props.user; // Set current user data
    isEditing.value = true; // Set editing mode to true
    showUserModal.value = true; // Show the modal
};

const closeUserModal = () => {
    showUserModal.value = false;
    selectedUser.value = null;
    isEditing.value = false;
};

const handleUserSaved = () => {
    closeUserModal();
    // Reload the current page to get updated user data
    router.reload();
};

// Other Actions
const toggleAdminStatus = () => {
    if (processing.value) return;

    processing.value = true;
    router.patch(
        route('admin.users.toggle-admin', props.user.id),
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const verifyEmail = () => {
    if (processing.value) return;

    processing.value = true;
    router.patch(
        route('admin.users.verify-email', props.user.id),
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};
</script>
