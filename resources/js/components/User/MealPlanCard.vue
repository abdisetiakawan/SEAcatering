<template>
    <div class="group overflow-hidden rounded-lg bg-white shadow-sm transition-all hover:shadow-md">
        <!-- Image -->
        <div class="relative aspect-video overflow-hidden bg-gray-100">
            <img v-if="plan.image" :src="plan.image" :alt="plan.name" class="h-full w-full object-cover transition-transform group-hover:scale-105" />
            <div v-else class="flex h-full items-center justify-center">
                <Calendar class="h-12 w-12 text-gray-400" />
            </div>

            <!-- Plan Type Badge -->
            <div class="absolute top-2 left-2">
                <Badge :class="getPlanTypeColor(plan.plan_type)">
                    {{ plan.plan_type.charAt(0).toUpperCase() + plan.plan_type.slice(1) }}
                </Badge>
            </div>

            <!-- Popular Badge -->
            <div v-if="plan.subscribers_count > 100" class="absolute top-2 right-2">
                <Badge class="bg-yellow-100 text-yellow-800">
                    <Star class="mr-1 h-3 w-3 fill-current" />
                    Popular
                </Badge>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Title -->
            <h3 class="mb-2 text-xl font-bold text-gray-900">{{ plan.name }}</h3>

            <!-- Description -->
            <p class="mb-4 line-clamp-3 text-sm text-gray-600">{{ plan.description }}</p>

            <!-- Plan Details -->
            <div class="mb-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Target Kalori:</span>
                    <span class="font-medium">{{ plan.target_calories }} cal/meal</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Menu Items:</span>
                    <span class="font-medium">{{ plan.menu_items_count }} items</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subscribers:</span>
                    <span class="font-medium">{{ plan.subscribers_count }} orang</span>
                </div>
            </div>

            <!-- Features -->
            <div v-if="plan.features && plan.features.length > 0" class="mb-4">
                <div class="flex flex-wrap gap-1">
                    <Badge v-for="feature in plan.features.slice(0, 3)" :key="feature" variant="outline" class="text-xs">
                        {{ feature }}
                    </Badge>
                    <Badge v-if="plan.features.length > 3" variant="outline" class="text-xs"> +{{ plan.features.length - 3 }} more </Badge>
                </div>
            </div>

            <!-- Price -->
            <div class="mb-4 flex items-baseline">
                <span class="text-2xl font-bold text-green-600">{{ formatCurrency(plan.price_per_meal) }}</span>
                <span class="ml-1 text-sm text-gray-600">/meal</span>
            </div>

            <!-- Actions -->
            <div class="space-y-2">
                <Button class="w-full bg-green-600 hover:bg-green-700" @click="$emit('subscribe', plan)" :disabled="!plan.is_active">
                    <Plus class="mr-2 h-4 w-4" />
                    Subscribe Now
                </Button>
                <Button variant="outline" class="w-full" @click="$emit('viewDetails', plan)">
                    <Eye class="mr-2 h-4 w-4" />
                    View Details
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar, Eye, Plus, Star } from 'lucide-vue-next';

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

defineProps<{
    plan: MealPlan;
}>();

defineEmits<{
    subscribe: [plan: MealPlan];
    viewDetails: [plan: MealPlan];
}>();

const getPlanTypeColor = (planType: string) => {
    const colors = {
        diet: 'bg-green-100 text-green-800',
        protein: 'bg-blue-100 text-blue-800',
        royal: 'bg-purple-100 text-purple-800',
        vegetarian: 'bg-orange-100 text-orange-800',
        seafood: 'bg-cyan-100 text-cyan-800',
    };
    return colors[planType as keyof typeof colors] || 'bg-gray-100 text-gray-800';
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
