<template>
    <UserLayout>
        <Head title="My Subscriptions" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">My Subscriptions</h1>
                                <p class="mt-1 text-gray-600">Kelola langganan meal plan Anda</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total Active</p>
                                    <p class="text-lg font-semibold text-green-600">{{ stats.active_subscriptions }}</p>
                                </div>
                                <Button @click="refreshData" variant="outline" size="sm">
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
                                <div class="text-2xl font-bold text-green-600">{{ stats.active_subscriptions }}</div>
                                <div class="text-sm text-gray-600">Active Plans</div>
                            </div>
                            <div class="rounded-full bg-green-100 p-3">
                                <CheckCircle class="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-orange-600">{{ stats.paused_subscriptions }}</div>
                                <div class="text-sm text-gray-600">Paused Plans</div>
                            </div>
                            <div class="rounded-full bg-orange-100 p-3">
                                <Pause class="h-6 w-6 text-orange-600" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ stats.total_deliveries }}</div>
                                <div class="text-sm text-gray-600">Total Deliveries</div>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3">
                                <Truck class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-purple-600">Rp {{ formatPrice(stats.monthly_savings) }}</div>
                                <div class="text-sm text-gray-600">Monthly Savings</div>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3">
                                <PiggyBank class="h-6 w-6 text-purple-600" />
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
                                <Input v-model="filters.search" placeholder="Cari subscription..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select v-model="filters.status" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in subscriptionStatuses" :key="value" :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="filters.plan_type" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Plan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="diet">Diet Plan</SelectItem>
                                    <SelectItem value="protein">Protein Plan</SelectItem>
                                    <SelectItem value="royal">Royal Plan</SelectItem>
                                    <SelectItem value="vegetarian">Vegetarian</SelectItem>
                                    <SelectItem value="seafood">Seafood</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions List -->
                <div v-if="subscriptions.data.length > 0" class="mb-6 space-y-4">
                    <SubscriptionCard
                        v-for="subscription in subscriptions.data"
                        :key="subscription.id"
                        :subscription="subscription"
                        @pause="handlePause"
                        @resume="handleResume"
                        @cancel="handleCancel"
                        @modify="handleModify"
                        @view-details="handleViewDetails"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <Calendar class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">Belum ada subscription</h3>
                    <p class="mb-6 text-gray-500">Mulai dengan berlangganan meal plan favorit Anda</p>
                    <Button @click="navigateToMealPlans" class="bg-green-600 hover:bg-green-700">
                        <Plus class="mr-2 h-4 w-4" />
                        Browse Meal Plans
                    </Button>
                </div>

                <!-- Pagination -->
                <div v-if="subscriptions.data.length > 0" class="mt-6 rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="subscriptions.links" />
                </div>
            </div>
        </div>

        <!-- Pause Confirmation Modal -->
        <ConfirmationModal
            :show="showPauseModal"
            title="Pause Subscription"
            message="Are you sure you want to pause this subscription? You can resume it anytime."
            @confirm="confirmPause"
            @cancel="showPauseModal = false"
        />

        <!-- Cancel Confirmation Modal -->
        <ConfirmationModal
            :show="showCancelModal"
            title="Cancel Subscription"
            message="Are you sure you want to cancel this subscription? This action cannot be undone and you'll lose any remaining deliveries."
            @confirm="confirmCancel"
            @cancel="showCancelModal = false"
        />

        <!-- Modify Subscription Modal -->
        <ModifySubscriptionModal
            :show="showModifyModal"
            :subscription="selectedSubscription"
            @close="showModifyModal = false"
            @updated="handleSubscriptionUpdated"
        />

        <!-- Success Modal -->
        <SuccessModal :show="showSuccessModal" :title="successTitle" :message="successMessage" @close="showSuccessModal = false" />
    </UserLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import ModifySubscriptionModal from '@/components/User/ModifySubscriptionModal.vue';
import SubscriptionCard from '@/components/User/SubscriptionCard.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, CheckCircle, Pause, PiggyBank, Plus, RefreshCw, Search, Truck } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface Subscription {
    id: number;
    meal_plan: {
        id: number;
        name: string;
        type: string;
        price_per_meal: number;
        image: string;
    };
    delivery_address: {
        id: number;
        address_line_1: string;
        city: string;
        province: string;
    };
    start_date: string;
    end_date: string;
    status: string;
    delivery_frequency: string;
    delivery_days: string[];
    preferred_delivery_time: string;
    price_per_meal: number;
    total_price: number;
    discount_amount: number;
    next_delivery_date: string;
    created_at: string;
}

const props = defineProps<{
    subscriptions: { data: Subscription[]; links: any[]; meta: any };
    filters: Record<string, any>;
    stats: {
        active_subscriptions: number;
        paused_subscriptions: number;
        total_deliveries: number;
        monthly_savings: number;
    };
    subscriptionStatuses: Record<string, string>;
}>();

// State
const showPauseModal = ref(false);
const showCancelModal = ref(false);
const showModifyModal = ref(false);
const showSuccessModal = ref(false);
const selectedSubscription = ref<Subscription | null>(null);
const successTitle = ref('');
const successMessage = ref('');

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    plan_type: props.filters.plan_type || '',
});

// Methods
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const refreshData = () => {
    router.reload();
};

const navigateToMealPlans = () => {
    router.visit(route('user.meal-plans.index'));
};

const handleSearch = () => {
    router.get(route('user.subscriptions.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('user.subscriptions.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.plan_type = '';
    router.get(route('user.subscriptions.index'));
};

const handlePause = (subscription: Subscription) => {
    selectedSubscription.value = subscription;
    showPauseModal.value = true;
};

const handleResume = (subscription: Subscription) => {
    router.patch(
        route('user.subscriptions.resume', subscription.id),
        {},
        {
            onSuccess: () => {
                successTitle.value = 'Subscription Resumed';
                successMessage.value = 'Your subscription has been successfully resumed.';
                showSuccessModal.value = true;
                router.reload();
            },
        },
    );
};

const handleCancel = (subscription: Subscription) => {
    selectedSubscription.value = subscription;
    showCancelModal.value = true;
};

const handleModify = (subscription: Subscription) => {
    selectedSubscription.value = subscription;
    showModifyModal.value = true;
};

const handleViewDetails = (subscription: Subscription) => {
    router.visit(route('subscriptions.show', subscription.id));
};

const confirmPause = () => {
    if (selectedSubscription.value) {
        router.patch(
            route('user.subscriptions.pause', selectedSubscription.value.id),
            {},
            {
                onSuccess: () => {
                    showPauseModal.value = false;
                    successTitle.value = 'Subscription Paused';
                    successMessage.value = 'Your subscription has been paused. You can resume it anytime.';
                    showSuccessModal.value = true;
                    router.reload();
                },
            },
        );
    }
};

const confirmCancel = () => {
    if (selectedSubscription.value) {
        router.patch(
            route('user.subscriptions.cancel', selectedSubscription.value.id),
            {},
            {
                onSuccess: () => {
                    showCancelModal.value = false;
                    successTitle.value = 'Subscription Cancelled';
                    successMessage.value = 'Your subscription has been cancelled successfully.';
                    showSuccessModal.value = true;
                    router.reload();
                },
            },
        );
    }
};

const handleSubscriptionUpdated = () => {
    showModifyModal.value = false;
    successTitle.value = 'Subscription Updated';
    successMessage.value = 'Your subscription has been updated successfully.';
    showSuccessModal.value = true;
    router.reload();
};
</script>
