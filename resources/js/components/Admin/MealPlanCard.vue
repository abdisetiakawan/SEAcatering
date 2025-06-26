<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-md transition-shadow duration-200 hover:shadow-lg">
        <!-- Header Image -->
        <div class="relative h-48 bg-gray-200">
            <img
                v-if="mealPlan.image"
                :src="mealPlan.image.startsWith('http') ? mealPlan.image : '/storage/' + mealPlan.image"
                :alt="mealPlan.name"
                class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full items-center justify-center">
                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    ></path>
                </svg>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-3 right-3">
                <span
                    class="rounded-full px-2 py-1 text-xs font-medium"
                    :class="mealPlan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                    {{ mealPlan.is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <!-- Price Badge -->
            <div class="absolute top-3 left-3">
                <span class="rounded-full bg-blue-600 px-2 py-1 text-xs font-medium text-white"> Rp {{ formatPrice(mealPlan.price_per_meal) }} </span>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Title and Type -->
            <div class="mb-2">
                <h3 class="mb-1 text-lg font-semibold text-gray-900">{{ mealPlan.name }}</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 capitalize">{{ mealPlan.plan_type }}</span>
                    <span class="text-gray-300">•</span>
                </div>
            </div>

            <!-- Description -->
            <p class="mb-3 line-clamp-2 text-sm text-gray-600">{{ mealPlan.description }}</p>

            <!-- Menu Items Count -->
            <div class="mb-3 flex items-center">
                <svg class="mr-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    ></path>
                </svg>
                <span class="text-sm text-gray-600">{{ mealPlan.menu_items_count || 0 }} menu items</span>
            </div>

            <!-- Stats -->
            <div class="mb-4 grid grid-cols-2 gap-4 text-sm">
                <div class="rounded bg-gray-50 p-2 text-center">
                    <div class="font-medium text-gray-900">{{ mealPlan.subscriptions_count || 0 }}</div>
                    <div class="text-gray-600">Subscribers</div>
                </div>
                <div class="rounded bg-gray-50 p-2 text-center">
                    <div class="font-medium text-gray-900">{{ mealPlan.orders_count || 0 }}</div>
                    <div class="text-gray-600">Orders</div>
                </div>
            </div>

            <!-- Dietary Tags -->
            <div v-if="mealPlan.features && mealPlan.features.length > 0" class="mb-4">
                <div class="flex flex-wrap gap-1">
                    <span v-for="tag in mealPlan.features.slice(0, 3)" :key="tag" class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">
                        {{ tag }}
                    </span>
                    <span v-if="mealPlan.features.length > 3" class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">
                        +{{ mealPlan.features.length - 3 }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
                <button
                    @click="$emit('edit', mealPlan)"
                    class="flex-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 transition-colors hover:bg-blue-100"
                >
                    Edit
                </button>
                <button
                    @click="$emit('toggle-status', mealPlan)"
                    class="rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                    :class="
                        mealPlan.is_active
                            ? 'border-red-200 bg-red-50 text-red-600 hover:bg-red-100'
                            : 'border-green-200 bg-green-50 text-green-600 hover:bg-green-100'
                    "
                >
                    {{ mealPlan.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button
                    @click="$emit('delete', mealPlan)"
                    class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-100"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        ></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Footer with timestamps -->
        <div class="border-t bg-gray-50 px-4 py-3 text-xs text-gray-500">
            <div class="flex justify-between">
                <span>Created: {{ formatDate(mealPlan.created_at) }}</span>
                <span>Updated: {{ formatDate(mealPlan.updated_at) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface MealPlan {
    id: number;
    name: string;
    plan_type: string;
    description: string;
    price_per_meal: number;
    is_active: boolean;
    image?: string;
    features?: string[];
    menu_items_count?: number;
    subscriptions_count?: number;
    orders_count?: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    mealPlan: MealPlan;
}

interface Emits {
    (e: 'edit', mealPlan: MealPlan): void;
    (e: 'delete', mealPlan: MealPlan): void;
    (e: 'toggle-status', mealPlan: MealPlan): void;
}

defineProps<Props>();
defineEmits<Emits>();

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
