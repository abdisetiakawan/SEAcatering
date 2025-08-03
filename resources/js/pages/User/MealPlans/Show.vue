<template>
    <UserLayout>
        <Head :title="mealPlan.name" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="mb-6 flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <Link
                                :href="route('user.meal-plans.index')"
                                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600"
                            >
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Meal Plans
                            </Link>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <ChevronRight class="h-4 w-4 text-gray-400" />
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                    {{ mealPlan.name }}
                                </span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-8 lg:col-span-2">
                        <!-- Hero Section -->
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="relative">
                                <!-- Plan Image -->
                                <div class="aspect-video bg-gray-100">
                                    <img
                                        v-if="mealPlan.image"
                                        :src="mealPlan.image.startsWith('http') ? mealPlan.image : '/storage/' + mealPlan.image"
                                        :alt="mealPlan.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full items-center justify-center">
                                        <Calendar class="h-16 w-16 text-gray-400" />
                                    </div>
                                </div>

                                <!-- Plan Type Badge -->
                                <div class="absolute top-4 left-4">
                                    <Badge :class="getPlanTypeColor(mealPlan.plan_type)" class="text-sm">
                                        {{ formatPlanType(mealPlan.plan_type) }}
                                    </Badge>
                                </div>

                                <!-- Subscribers Badge -->
                                <div class="absolute top-4 right-4">
                                    <Badge class="bg-white text-gray-800">
                                        <Users class="mr-1 h-3 w-3" />
                                        {{ mealPlan.subscribers_count }} subscribers
                                    </Badge>
                                </div>
                            </div>

                            <div class="p-6">
                                <!-- Title and Description -->
                                <div class="mb-6">
                                    <h1 class="mb-2 text-3xl font-bold text-gray-900">{{ mealPlan.name }}</h1>
                                    <p class="text-lg text-gray-600">{{ mealPlan.description }}</p>
                                </div>

                                <!-- Key Stats -->
                                <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                                    <div class="rounded-lg bg-green-50 p-4 text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(mealPlan.price_per_meal) }}</div>
                                        <div class="text-sm text-green-700">Per Meal</div>
                                    </div>
                                    <div class="rounded-lg bg-blue-50 p-4 text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ mealPlan.target_calories }}</div>
                                        <div class="text-sm text-blue-700">Calories</div>
                                    </div>
                                    <div class="rounded-lg bg-purple-50 p-4 text-center">
                                        <div class="text-2xl font-bold text-purple-600">{{ mealPlan.menu_items_count }}</div>
                                        <div class="text-sm text-purple-700">Menu Items</div>
                                    </div>
                                    <div class="rounded-lg bg-orange-50 p-4 text-center">
                                        <div class="text-2xl font-bold text-orange-600">{{ mealPlan.subscribers_count }}</div>
                                        <div class="text-sm text-orange-700">Subscribers</div>
                                    </div>
                                </div>

                                <!-- Features -->
                                <div v-if="mealPlan.features && mealPlan.features.length > 0" class="mb-6">
                                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Plan Features</h3>
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div
                                            v-for="feature in mealPlan.features"
                                            :key="feature"
                                            class="flex items-center space-x-2 rounded-lg bg-gray-50 p-3"
                                        >
                                            <CheckCircle class="h-5 w-5 text-green-600" />
                                            <span class="text-sm text-gray-700">{{ feature }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-xl font-semibold text-gray-900">Menu Items ({{ mealPlan.menu_items_count }})</h2>
                                <p class="mt-1 text-gray-600">Delicious meals included in this plan</p>
                            </div>
                            <div class="p-6">
                                <div v-if="mealPlan.menuItems && mealPlan.menuItems.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div
                                        v-for="item in mealPlan.menuItems"
                                        :key="item.id"
                                        class="flex items-center space-x-4 rounded-lg border p-4 transition-shadow hover:shadow-md"
                                    >
                                        <img
                                            :src="
                                                item.image
                                                    ? item.image.startsWith('http')
                                                        ? item.image
                                                        : '/storage/' + item.image
                                                    : '/placeholder.svg?height=60&width=60'
                                            "
                                            :alt="item.name"
                                            class="h-15 w-15 rounded-lg object-cover"
                                        />
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ item.name }}</h3>
                                            <p class="line-clamp-2 text-sm text-gray-600">{{ item.description }}</p>
                                            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                                <div class="flex items-center">
                                                    <Zap class="mr-1 h-3 w-3" />
                                                    {{ item.calories }} cal
                                                </div>
                                                <div class="flex items-center">
                                                    <Activity class="mr-1 h-3 w-3" />
                                                    {{ item.protein }}g protein
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <Badge variant="outline" class="text-xs">
                                                    {{ item.category }}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-green-600">{{ formatCurrency(item.price) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="py-8 text-center text-gray-500">
                                    <Utensils class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                                    <p>No menu items available for this plan</p>
                                </div>
                            </div>
                        </div>

                        <!-- Nutrition Information -->
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-xl font-semibold text-gray-900">Nutrition Information</h2>
                                <p class="mt-1 text-gray-600">Average nutritional values per meal</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                                    <div class="text-center">
                                        <div class="mb-2 flex justify-center">
                                            <div class="rounded-full bg-orange-100 p-3">
                                                <Zap class="h-6 w-6 text-orange-600" />
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold text-orange-600">{{ mealPlan.target_calories }}</div>
                                        <div class="text-sm text-gray-600">Calories</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="mb-2 flex justify-center">
                                            <div class="rounded-full bg-red-100 p-3">
                                                <Activity class="h-6 w-6 text-red-600" />
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold text-red-600">{{ averageNutrition.protein }}g</div>
                                        <div class="text-sm text-gray-600">Protein</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="mb-2 flex justify-center">
                                            <div class="rounded-full bg-blue-100 p-3">
                                                <Wheat class="h-6 w-6 text-blue-600" />
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold text-blue-600">{{ averageNutrition.carbs }}g</div>
                                        <div class="text-sm text-gray-600">Carbs</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="mb-2 flex justify-center">
                                            <div class="rounded-full bg-green-100 p-3">
                                                <Droplets class="h-6 w-6 text-green-600" />
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold text-green-600">{{ averageNutrition.fat }}g</div>
                                        <div class="text-sm text-gray-600">Fat</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Subscribe Card -->
                        <div class="top-6 overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="border-b border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900">Subscribe to This Plan</h3>
                            </div>
                            <div class="p-6">
                                <!-- Price Display -->
                                <div class="mb-6 text-center">
                                    <div class="text-3xl font-bold text-green-600">{{ formatCurrency(mealPlan.price_per_meal) }}</div>
                                    <div class="text-sm text-gray-600">per meal</div>
                                    <div class="mt-2 text-sm text-gray-500">Starting from {{ formatCurrency(weeklyPrice) }}/week</div>
                                </div>

                                <!-- Quick Plan Info -->
                                <div class="mb-6 space-y-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Target Calories:</span>
                                        <span class="font-medium">{{ mealPlan.target_calories }} cal</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Plan Type:</span>
                                        <span class="font-medium">{{ formatPlanType(mealPlan.plan_type) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Menu Variety:</span>
                                        <span class="font-medium">{{ mealPlan.menu_items_count }} items</span>
                                    </div>
                                </div>

                                <!-- Subscribe Button -->
                                <Button @click="openSubscribeModal" class="w-full bg-green-600 hover:bg-green-700" size="lg">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Subscribe Now
                                </Button>

                                <!-- Additional Info -->
                                <div class="mt-4 space-y-2 text-xs text-gray-500">
                                    <div class="flex items-center">
                                        <CheckCircle class="mr-2 h-3 w-3 text-green-500" />
                                        <span>Cancel anytime</span>
                                    </div>
                                    <div class="flex items-center">
                                        <CheckCircle class="mr-2 h-3 w-3 text-green-500" />
                                        <span>Free delivery on subscriptions</span>
                                    </div>
                                    <div class="flex items-center">
                                        <CheckCircle class="mr-2 h-3 w-3 text-green-500" />
                                        <span>Flexible scheduling</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Benefits -->
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="border-b border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900">Why Choose This Plan?</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="rounded-full bg-green-100 p-2">
                                            <ChefHat class="h-5 w-5 text-green-600" />
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">Chef Curated</h4>
                                            <p class="text-sm text-gray-600">Meals designed by professional chefs</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div class="rounded-full bg-blue-100 p-2">
                                            <Leaf class="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">Fresh Ingredients</h4>
                                            <p class="text-sm text-gray-600">Sourced daily from local suppliers</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div class="rounded-full bg-purple-100 p-2">
                                            <Heart class="h-5 w-5 text-purple-600" />
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">Health Focused</h4>
                                            <p class="text-sm text-gray-600">Nutritionally balanced for your goals</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div class="rounded-full bg-orange-100 p-2">
                                            <Clock class="h-5 w-5 text-orange-600" />
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">Time Saving</h4>
                                            <p class="text-sm text-gray-600">No meal prep or cooking required</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscribe Modal -->
        <SubscribeModal
            :show="showSubscribeModal"
            :plan="mealPlan"
            :user-addresses="userAddresses"
            @close="showSubscribeModal = false"
            @subscribed="handleSubscribed"
        />

        <!-- Success Modal -->
        <SuccessModal :show="showSuccessModal" :title="successModal.title" :message="successModal.message" @close="showSuccessModal = false" />
    </UserLayout>
</template>

<script setup lang="ts">
import SubscribeModal from '@/components/User/SubscribeModal.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowLeft,
    Calendar,
    CheckCircle,
    ChefHat,
    ChevronRight,
    Clock,
    Droplets,
    Heart,
    Leaf,
    Plus,
    Users,
    Utensils,
    Wheat,
    Zap,
} from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    image?: string;
    calories: number;
    protein: number | string;
    carbs: number | string;
    fat: number | string;
    category?:
        | {
              name: string;
          }
        | string;
}

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
    menuItems: MenuItem[];
}

interface UserAddress {
    id: number;
    label: string;
    recipient_name: string;
    phone: string;
    full_address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    is_default: boolean;
    address_type: string;
    delivery_instructions?: string;
}

const props = defineProps<{
    mealPlan: MealPlan;
    userAddresses: UserAddress[];
}>();

// State
const showSubscribeModal = ref(false);
const showSuccessModal = ref(false);

const successModal = reactive({
    title: '',
    message: '',
});

const weeklyPrice = computed(() => {
    return props.mealPlan.price_per_meal * 7;
});

const averageNutrition = computed(() => {
    if (!props.mealPlan.menuItems || props.mealPlan.menuItems.length === 0) {
        return { protein: 0, carbs: 0, fat: 0 };
    }

    const total = props.mealPlan.menuItems.reduce(
        (acc, item) => ({
            protein: acc.protein + parseFloat(item.protein as string),
            carbs: acc.carbs + parseFloat(item.carbs as string),
            fat: acc.fat + parseFloat(item.fat as string),
        }),
        { protein: 0, carbs: 0, fat: 0 },
    );

    const count = props.mealPlan.menuItems.length;
    return {
        protein: Math.round(total.protein / count),
        carbs: Math.round(total.carbs / count),
        fat: Math.round(total.fat / count),
    };
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

const formatPlanType = (type: string) => {
    const types: Record<string, string> = {
        diet: 'Diet Plan',
        protein: 'Protein Plan',
        royal: 'Royal Plan',
        vegetarian: 'Vegetarian Plan',
        seafood: 'Seafood Plan',
    };
    return types[type] || type;
};

const getPlanTypeColor = (planType: string) => {
    const colors: Record<string, string> = {
        diet: 'bg-green-100 text-green-800',
        protein: 'bg-blue-100 text-blue-800',
        royal: 'bg-purple-100 text-purple-800',
        vegetarian: 'bg-orange-100 text-orange-800',
        seafood: 'bg-cyan-100 text-cyan-800',
    };
    return colors[planType] || 'bg-gray-100 text-gray-800';
};

const openSubscribeModal = () => {
    showSubscribeModal.value = true;
};

const handleSubscribed = () => {
    showSubscribeModal.value = false;
    successModal.title = 'Subscription Successful!';
    successModal.message =
        'Thank you for subscribing! You will receive a confirmation email shortly and can manage your subscription in "My Subscriptions".';
    showSuccessModal.value = true;
};
</script>
