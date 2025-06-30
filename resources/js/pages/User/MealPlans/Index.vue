<template>
    <UserLayout>
        <Head title="Meal Plans" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Meal Plans</h1>
                                <p class="mt-1 text-gray-600">Pilih paket langganan yang sesuai dengan kebutuhan Anda</p>
                            </div>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('user.menu.index')"
                                    class="flex items-center rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                                >
                                    <Utensils class="mr-2 h-4 w-4" />
                                    Browse Menu
                                </Link>
                                <Link
                                    :href="route('user.subscriptions.index')"
                                    class="flex items-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                                >
                                    <Package class="mr-2 h-4 w-4" />
                                    My Subscriptions
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">{{ stats.total_plans }}</div>
                        <div class="text-sm text-gray-600">Available Plans</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.active_subscribers }}</div>
                        <div class="text-sm text-gray-600">Active Subscribers</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.starting_price) }}</div>
                        <div class="text-sm text-gray-600">Starting From</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-orange-600">{{ stats.avg_rating.toFixed(1) }}/5</div>
                        <div class="text-sm text-gray-600">Average Rating</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <Select v-model="filters.plan_type" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Tipe Plan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Tipe</SelectItem>
                                    <SelectItem v-for="type in planTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="filters.price_range" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Range Harga" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Harga</SelectItem>
                                    <SelectItem value="0-30000"> Rp 30,000</SelectItem>
                                    <SelectItem value="30000-50000">Rp 30,000 - 50,000</SelectItem>
                                    <SelectItem value="50000-80000">Rp 50,000 - 80,000</SelectItem>
                                    <SelectItem value="80000+"> Rp 80,000</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="filters.calories" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Target Kalori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kalori</SelectItem>
                                    <SelectItem value="0-400"> 400 cal</SelectItem>
                                    <SelectItem value="400-600">400 - 600 cal</SelectItem>
                                    <SelectItem value="600-800">600 - 800 cal</SelectItem>
                                    <SelectItem value="800+"> 800 cal</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Meal Plans Grid -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <MealPlanCard
                        v-for="plan in mealPlans.data"
                        :key="plan.id"
                        :plan="plan"
                        @subscribe="subscribeToPlan"
                        @view-details="viewPlanDetails"
                    />
                </div>

                <!-- Empty State -->
                <div v-if="mealPlans.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <Calendar class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">Tidak ada meal plan ditemukan</h3>
                    <p class="mb-6 text-gray-500">Coba ubah filter untuk melihat plan lainnya</p>
                    <Button @click="resetFilters" class="bg-green-600 hover:bg-green-700">
                        <RefreshCw class="mr-2 h-4 w-4" />
                        Reset Filter
                    </Button>
                </div>

                <!-- Pagination -->
                <div v-if="mealPlans.data.length > 0" class="rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="mealPlans.links" />
                </div>
            </div>
        </div>

        <!-- Subscribe Modal -->
        <SubscribeModal
            :show="showSubscribeModal"
            :plan="selectedPlan"
            :user-addresses="userAddresses"
            @close="showSubscribeModal = false"
            @subscribed="handleSubscribed"
        />

        <!-- Success Modal -->
        <SuccessModal :show="showSuccessModal" :title="successModal.title" :message="successModal.message" @close="showSuccessModal = false" />
    </UserLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import MealPlanCard from '@/components/User/MealPlanCard.vue';
import SubscribeModal from '@/components/User/SubscribeModal.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Package, RefreshCw, Utensils } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: number;
    plan_type: string;
    target_calories: number;
    image?: string;
    features: string[];
    menu_items_count: number;
    subscribers_count: number;
    is_active: boolean;
}

interface PlanType {
    value: string;
    label: string;
}

interface Stats {
    total_plans: number;
    active_subscribers: number;
    starting_price: number;
    avg_rating: number;
}

export interface UserAddress {
    id: number;
    label: string;
    recipient_name: string;
    phone_number: string;
    address_line_1: string;
    address_line_2?: string;
    full_address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    delivery_instructions?: string;
    address_type: 'residential' | 'commercial' | 'apartment';
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    mealPlans: { data: MealPlan[]; links: any[]; meta: any };
    planTypes: PlanType[];
    stats: Stats;
    filters: Record<string, any>;
    userAddresses: UserAddress[];
}>();

// State
const showSubscribeModal = ref(false);
const showSuccessModal = ref(false);
const selectedPlan = ref<MealPlan | null>(null);

const successModal = reactive({
    title: '',
    message: '',
});

const filters = reactive({
    plan_type: props.filters.plan_type || 'all',
    price_range: props.filters.price_range || 'all',
    calories: props.filters.calories || 'all',
});

// Methods
const subscribeToPlan = (plan: MealPlan) => {
    selectedPlan.value = plan;
    showSubscribeModal.value = true;
};

const viewPlanDetails = (plan: MealPlan) => {
    router.visit(route('user.meal-plans.show', plan.id));
};

const handleFilter = () => {
    router.get(route('meal-plans.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.plan_type = 'all';
    filters.price_range = 'all';
    filters.calories = 'all';
    router.get(route('meal-plans.index'));
};

const handleSubscribed = () => {
    showSubscribeModal.value = false;
    selectedPlan.value = null;

    successModal.title = 'Langganan Berhasil!';
    successModal.message =
        'Terima kasih telah berlangganan. Anda akan menerima konfirmasi melalui email dan dapat melihat detail langganan di halaman "My Subscriptions".';
    showSuccessModal.value = true;

    // Redirect to subscriptions after a delay
    setTimeout(() => {
        router.visit(route('user.subscriptions.index'));
    }, 3000);
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>
