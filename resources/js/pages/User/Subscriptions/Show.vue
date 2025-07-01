<template>
    <UserLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="mb-8 flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <Link
                                :href="route('user.subscriptions.index')"
                                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600"
                            >
                                <Icon name="arrow-left" class="mr-2 h-4 w-4" />
                                Subscriptions
                            </Link>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <Icon name="chevron-right" class="h-4 w-4 text-gray-400" />
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                    {{ subscription.subscription_number }}
                                </span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-6 rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">
                                    {{ subscription.meal_plan.name }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ subscription.subscription_number }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <SubscriptionStatusBadge :status="subscription.status" />

                                <!-- Pay Now Button for Subscription -->
                                <button
                                    v-if="subscription.can_pay"
                                    @click="paySubscription"
                                    class="flex items-center rounded-md bg-green-600 px-4 py-2 font-medium text-white transition-colors hover:bg-green-700"
                                >
                                    <Icon name="credit-card" class="mr-2 h-4 w-4" />
                                    Pay Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 bg-gray-50 px-6 py-4">
                        <button
                            v-if="subscription.can_be_paused"
                            @click="pauseSubscription"
                            class="flex items-center rounded-md bg-yellow-600 px-4 py-2 font-medium text-white transition-colors hover:bg-yellow-700"
                        >
                            <Icon name="pause" class="mr-2 h-4 w-4" />
                            Pause
                        </button>

                        <button
                            v-if="subscription.can_be_resumed"
                            @click="resumeSubscription"
                            class="flex items-center rounded-md bg-green-600 px-4 py-2 font-medium text-white transition-colors hover:bg-green-700"
                        >
                            <Icon name="play" class="mr-2 h-4 w-4" />
                            Resume
                        </button>

                        <button
                            v-if="subscription.can_be_cancelled"
                            @click="cancelSubscription"
                            class="flex items-center rounded-md bg-red-600 px-4 py-2 font-medium text-white transition-colors hover:bg-red-700"
                        >
                            <Icon name="x" class="mr-2 h-4 w-4" />
                            Cancel
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Subscription Details -->
                        <div class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h2 class="text-lg font-semibold text-gray-900">Subscription Details</h2>
                            </div>
                            <div class="px-6 py-4">
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Frequency</dt>
                                        <dd class="mt-1 text-sm text-gray-900 capitalize">{{ subscription.frequency }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Meals per Day</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ subscription.meals_per_day }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Delivery Days</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ formatDeliveryDays(subscription.delivery_days) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Delivery Time</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ subscription.delivery_time_preference }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(subscription.start_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(subscription.end_date) }}</dd>
                                    </div>
                                    <div v-if="subscription.next_delivery_date">
                                        <dt class="text-sm font-medium text-gray-500">Next Delivery</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(subscription.next_delivery_date) }}</dd>
                                    </div>
                                    <div v-if="subscription.special_requirements">
                                        <dt class="text-sm font-medium text-gray-500">Special Requirements</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ subscription.special_requirements }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                                    <Link
                                        :href="route('user.orders.index', { subscription_id: subscription.id })"
                                        class="text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        View All Orders
                                    </Link>
                                </div>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div v-for="order in subscription.orders" :key="order.id" class="px-6 py-4 hover:bg-gray-50">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                                            <p class="text-sm text-gray-500">Delivery: {{ formatDate(order.delivery_date) }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="flex items-center space-x-2">
                                                <OrderStatusBadge :status="order.status" />
                                                <PaymentStatusBadge :status="order.payment_status" />
                                            </div>
                                            <p class="mt-1 text-sm font-medium text-gray-900">Rp {{ formatCurrency(order.total_amount) }}</p>

                                            <!-- Pay Now Button for Individual Orders -->
                                            <button
                                                v-if="order.can_pay"
                                                @click="payOrder(order.id)"
                                                class="mt-2 flex items-center rounded bg-green-600 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-green-700"
                                            >
                                                <Icon name="credit-card" class="mr-1 h-3 w-3" />
                                                Pay Now
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="subscription.orders.length === 0" class="px-6 py-8 text-center">
                                    <Icon name="package" class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                                    <p class="text-gray-500">No orders yet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Pricing Summary -->
                        <div class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="text-lg font-semibold text-gray-900">Pricing Summary</h3>
                            </div>
                            <div class="px-6 py-4">
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-600">Total Price</dt>
                                        <dd class="text-sm font-medium text-gray-900">Rp {{ formatCurrency(subscription.total_price) }}</dd>
                                    </div>
                                    <div v-if="subscription.discount_amount > 0" class="flex justify-between">
                                        <dt class="text-sm text-gray-600">Discount</dt>
                                        <dd class="text-sm font-medium text-green-600">-Rp {{ formatCurrency(subscription.discount_amount) }}</dd>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="flex justify-between">
                                            <dt class="text-base font-medium text-gray-900">Final Amount</dt>
                                            <dd class="text-base font-medium text-gray-900">
                                                Rp {{ formatCurrency(subscription.total_price - subscription.discount_amount) }}
                                            </dd>
                                        </div>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="text-lg font-semibold text-gray-900">Delivery Address</h3>
                            </div>
                            <div class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <p class="font-medium">{{ subscription.delivery_address.recipient_name }}</p>
                                    <p class="mt-1">{{ subscription.delivery_address.address_line_1 }}</p>
                                    <p v-if="subscription.delivery_address.address_line_2">
                                        {{ subscription.delivery_address.address_line_2 }}
                                    </p>
                                    <p class="mt-1">
                                        {{ subscription.delivery_address.city }}, {{ subscription.delivery_address.state }}
                                        {{ subscription.delivery_address.postal_code }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Meal Plan Info -->
                        <div class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="text-lg font-semibold text-gray-900">Meal Plan</h3>
                            </div>
                            <div class="px-6 py-4">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ subscription.meal_plan.name }}</p>
                                    <p class="mt-1 text-gray-600">{{ subscription.meal_plan.description }}</p>
                                    <p class="mt-2 text-gray-900">
                                        <span class="font-medium">Price per meal:</span>
                                        Rp {{ formatCurrency(subscription.meal_plan.price_per_meal) }}
                                    </p>
                                    <p class="text-gray-900">
                                        <span class="font-medium">Plan type:</span>
                                        <span class="capitalize">{{ subscription.meal_plan.plan_type }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment History -->
                        <div v-if="subscription.payments.length > 0" class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div v-for="payment in subscription.payments" :key="payment.id" class="px-6 py-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ payment.payment_number }}</p>
                                            <p class="text-xs text-gray-500">{{ payment.payment_method }}</p>
                                        </div>
                                        <div class="text-right">
                                            <PaymentStatusBadge :status="payment.status" />
                                            <p class="mt-1 text-sm font-medium text-gray-900">Rp {{ formatCurrency(payment.amount) }}</p>
                                            <p v-if="payment.payment_date" class="text-xs text-gray-500">
                                                {{ formatDateTime(payment.payment_date) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modals -->
        <ConfirmationModal
            :show="showPauseModal"
            title="Pause Subscription"
            message="Are you sure you want to pause this subscription? Future deliveries will be cancelled until you resume."
            confirm-text="Pause"
            @close="showPauseModal = false"
            @confirm="confirmPause"
        />

        <ConfirmationModal
            :show="showResumeModal"
            title="Resume Subscription"
            message="Are you sure you want to resume this subscription? New deliveries will be scheduled."
            confirm-text="Resume"
            @close="showResumeModal = false"
            @confirm="confirmResume"
        />

        <ConfirmationModal
            :show="showCancelModal"
            title="Cancel Subscription"
            message="Are you sure you want to cancel this subscription? This action cannot be undone."
            confirm-text="Cancel Subscription"
            confirm-class="bg-red-600 hover:bg-red-700"
            @close="showCancelModal = false"
            @confirm="confirmCancel"
        />
    </UserLayout>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import OrderStatusBadge from '@/components/User/OrderStatusBadge.vue';
import PaymentStatusBadge from '@/components/User/PaymentStatusBadge.vue';
import SubscriptionStatusBadge from '@/components/User/SubscriptionStatusBadge.vue';
import UserLayout from '@/layouts/UserLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, toRaw } from 'vue';

interface Order {
    id: number;
    order_number: string;
    delivery_date: string;
    status: string;
    payment_status: string;
    total_amount: number;
    can_pay: boolean;
}

interface Payment {
    id: number;
    payment_number: string;
    amount: number;
    status: string;
    payment_method: string;
    payment_date?: string;
}

interface Subscription {
    id: number;
    subscription_number: string;
    start_date: string;
    end_date: string;
    status: string;
    frequency: string;
    meals_per_day: number;
    delivery_days: string[];
    delivery_time_preference: string;
    total_price: number;
    discount_amount: number;
    special_requirements?: string;
    next_delivery_date?: string;
    auto_renew: boolean;
    can_be_paused: boolean;
    can_be_resumed: boolean;
    can_be_cancelled: boolean;
    can_pay: boolean;
    created_at: string;
    meal_plan: {
        id: number;
        name: string;
        description: string;
        price_per_meal: number;
        plan_type: string;
        image?: string;
    };
    delivery_address: {
        recipient_name: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
    };
    orders: Order[];
    payments: Payment[];
}

interface Props {
    subscription: Subscription;
}

const props = defineProps<Props>();
console.log(toRaw(props.subscription));

// Modal states
const showPauseModal = ref(false);
const showResumeModal = ref(false);
const showCancelModal = ref(false);

// Methods
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID').format(amount);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (date: string): string => {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDeliveryDays = (days: string[]): string => {
    const dayMap: Record<string, string> = {
        monday: 'Monday',
        tuesday: 'Tuesday',
        wednesday: 'Wednesday',
        thursday: 'Thursday',
        friday: 'Friday',
        saturday: 'Saturday',
        sunday: 'Sunday',
    };

    return days.map((day) => dayMap[day]).join(', ');
};

const paySubscription = () => {
    router.visit(route('user.subscriptions.payment', props.subscription.id));
};

const payOrder = (orderId: number) => {
    router.visit(route('user.orders.payment', orderId));
};

const pauseSubscription = () => {
    showPauseModal.value = true;
};

const resumeSubscription = () => {
    showResumeModal.value = true;
};

const cancelSubscription = () => {
    showCancelModal.value = true;
};

const confirmPause = () => {
    router.patch(
        route('user.subscriptions.pause', props.subscription.id),
        {},
        {
            onSuccess: () => {
                showPauseModal.value = false;
            },
        },
    );
};

const confirmResume = () => {
    router.patch(
        route('user.subscriptions.resume', props.subscription.id),
        {},
        {
            onSuccess: () => {
                showResumeModal.value = false;
            },
        },
    );
};

const confirmCancel = () => {
    router.patch(
        route('user.subscriptions.cancel', props.subscription.id),
        {},
        {
            onSuccess: () => {
                showCancelModal.value = false;
            },
        },
    );
};
</script>
