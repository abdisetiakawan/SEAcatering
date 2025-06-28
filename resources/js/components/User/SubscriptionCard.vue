<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-4 flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <img
                        :src="subscription.meal_plan.image || '/placeholder.svg?height=60&width=60'"
                        :alt="subscription.meal_plan.name"
                        class="h-15 w-15 rounded-lg object-cover"
                    />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ subscription.meal_plan.name }}</h3>
                        <p class="text-sm text-gray-600">{{ formatPlanType(subscription.meal_plan.type) }}</p>
                        <div class="mt-1 flex items-center space-x-2">
                            <SubscriptionStatusBadge :status="subscription.status" />
                            <span class="text-sm text-gray-500">•</span>
                            <span class="text-sm text-gray-500">{{ formatFrequency(subscription.delivery_frequency) }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">Rp {{ formatPrice(subscription.price_per_meal) }}</p>
                    <p class="text-sm text-gray-600">per meal</p>
                    <p v-if="subscription.discount_amount > 0" class="text-sm text-green-600">
                        Save Rp {{ formatPrice(subscription.discount_amount) }}
                    </p>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg bg-gray-50 p-4">
                    <div class="flex items-center space-x-2">
                        <MapPin class="h-4 w-4 text-gray-500" />
                        <span class="text-sm font-medium text-gray-700">Delivery Address</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ subscription.delivery_address.address_line_1 }}<br />
                        {{ subscription.delivery_address.city }}, {{ subscription.delivery_address.province }}
                    </p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <div class="flex items-center space-x-2">
                        <Clock class="h-4 w-4 text-gray-500" />
                        <span class="text-sm font-medium text-gray-700">Next Delivery</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ formatDate(subscription.next_delivery_date) }}<br />
                        {{ subscription.preferred_delivery_time }}
                    </p>
                </div>
            </div>

            <!-- Delivery Days -->
            <div class="mb-4">
                <p class="mb-2 text-sm font-medium text-gray-700">Delivery Days</p>
                <div class="flex flex-wrap gap-2">
                    <Badge v-for="day in subscription.delivery_days" :key="day" variant="outline" class="text-xs">
                        {{ formatDay(day) }}
                    </Badge>
                </div>
            </div>

            <!-- Subscription Period -->
            <div class="mb-4 rounded-lg bg-blue-50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-900">Subscription Period</p>
                        <p class="text-sm text-blue-700">{{ formatDate(subscription.start_date) }} - {{ formatDate(subscription.end_date) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-blue-900">Total Value</p>
                        <p class="text-sm text-blue-700">Rp {{ formatPrice(subscription.total_price) }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
                <Button @click="$emit('view-details', subscription)" variant="outline" size="sm">
                    <Eye class="mr-2 h-4 w-4" />
                    View Details
                </Button>

                <Button v-if="subscription.status === 'active'" @click="$emit('pause', subscription)" variant="outline" size="sm">
                    <Pause class="mr-2 h-4 w-4" />
                    Pause
                </Button>

                <Button
                    v-if="subscription.status === 'paused'"
                    @click="$emit('resume', subscription)"
                    variant="outline"
                    size="sm"
                    class="border-green-200 text-green-700 hover:bg-green-50"
                >
                    <Play class="mr-2 h-4 w-4" />
                    Resume
                </Button>

                <Button v-if="['active', 'paused'].includes(subscription.status)" @click="$emit('modify', subscription)" variant="outline" size="sm">
                    <Settings class="mr-2 h-4 w-4" />
                    Modify
                </Button>

                <Button
                    v-if="['active', 'paused'].includes(subscription.status)"
                    @click="$emit('cancel', subscription)"
                    variant="outline"
                    size="sm"
                    class="border-red-200 text-red-700 hover:bg-red-50"
                >
                    <X class="mr-2 h-4 w-4" />
                    Cancel
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import SubscriptionStatusBadge from '@/components/User/SubscriptionStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Clock, Eye, MapPin, Pause, Play, Settings, X } from 'lucide-vue-next';

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
}

defineProps<{
    subscription: Subscription;
}>();

defineEmits<{
    'view-details': [subscription: Subscription];
    pause: [subscription: Subscription];
    resume: [subscription: Subscription];
    cancel: [subscription: Subscription];
    modify: [subscription: Subscription];
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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

const formatFrequency = (frequency: string) => {
    const frequencies: Record<string, string> = {
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
    };
    return frequencies[frequency] || frequency;
};

const formatDay = (day: string) => {
    const days: Record<string, string> = {
        monday: 'Mon',
        tuesday: 'Tue',
        wednesday: 'Wed',
        thursday: 'Thu',
        friday: 'Fri',
        saturday: 'Sat',
        sunday: 'Sun',
    };
    return days[day] || day;
};
</script>
