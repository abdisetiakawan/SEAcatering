<template>
    <div class="group overflow-hidden rounded-lg bg-white shadow-sm transition-all hover:shadow-md">
        <!-- Image -->
        <div class="relative aspect-video overflow-hidden bg-gray-100">
            <img
                v-if="item.image"
                :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image"
                :alt="item.name"
                class="h-full w-full object-cover transition-transform group-hover:scale-105"
            />
            <div v-else class="flex h-full items-center justify-center">
                <Utensils class="h-12 w-12 text-gray-400" />
            </div>

            <!-- Availability Badge -->
            <div class="absolute top-2 left-2">
                <Badge v-if="!item.is_available" variant="destructive"> Tidak Tersedia </Badge>
                <Badge v-else-if="item.average_rating >= 4.5" class="bg-yellow-100 text-yellow-800">
                    <Star class="mr-1 h-3 w-3 fill-current" />
                    Populer
                </Badge>
            </div>

            <!-- Category Badge -->
            <div class="absolute top-2 right-2">
                <Badge variant="secondary" class="text-xs"> {{ item.category }} </Badge>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Title and Rating -->
            <div class="mb-2 flex items-start justify-between">
                <h3 class="line-clamp-1 font-semibold text-gray-900">{{ item.name }}</h3>
                <div class="flex items-center space-x-1 text-sm">
                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                    <span class="font-medium">{{ Number(item.average_rating ?? 0).toFixed(1) }} </span>
                    <span class="text-gray-500">({{ item.review_count }})</span>
                </div>
            </div>

            <!-- Description -->
            <p class="mb-3 line-clamp-2 text-sm text-gray-600">{{ item.description }}</p>

            <!-- Nutrition Info -->
            <div class="mb-3 grid grid-cols-4 gap-2 text-xs">
                <div class="text-center">
                    <div class="font-semibold text-orange-600">{{ item.calories }}</div>
                    <div class="text-gray-500">Cal</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-red-600">{{ item.protein }}g</div>
                    <div class="text-gray-500">Protein</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-blue-600">{{ item.carbs }}g</div>
                    <div class="text-gray-500">Carbs</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-green-600">{{ item.fat }}g</div>
                    <div class="text-gray-500">Fat</div>
                </div>
            </div>

            <!-- Price and Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-lg font-bold text-green-600">{{ formatCurrency(item.price) }}</span>
                </div>
                <div class="flex space-x-2">
                    <Button size="sm" variant="outline" @click="$emit('viewDetails', item)">
                        <Eye class="h-4 w-4" />
                    </Button>
                    <Button size="sm" :disabled="!item.is_available" @click="$emit('addToCart', item)" class="bg-green-600 hover:bg-green-700">
                        <ShoppingCart class="mr-1 h-4 w-4" />
                        Add
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Eye, ShoppingCart, Star, Utensils } from 'lucide-vue-next';

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    image?: string;
    category: {
        id: number;
        name: string;
    };
    average_rating: number;
    review_count: number;
    is_available: boolean;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
}

defineProps<{
    item: MenuItem;
}>();

defineEmits<{
    addToCart: [item: MenuItem];
    viewDetails: [item: MenuItem];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>
