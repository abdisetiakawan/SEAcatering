<template>
    <div class="rounded-lg border border-gray-200 p-4 transition-shadow hover:shadow-md">
        <div class="flex items-start space-x-3">
            <!-- Item Image -->
            <div class="flex-shrink-0">
                <img
                    :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image"
                    :alt="item.name"
                    class="h-15 w-15 rounded-lg object-cover"
                />
            </div>

            <!-- Item Details -->
            <div class="min-w-0 flex-1">
                <h4 class="line-clamp-1 text-sm font-semibold text-gray-900">{{ item.name }}</h4>
                <p class="mt-1 line-clamp-2 text-xs text-gray-600">{{ item.description }}</p>

                <!-- Rating and Category -->
                <div class="mt-2 flex items-center space-x-2">
                    <div class="flex items-center">
                        <Star class="h-3 w-3 fill-current text-yellow-400" />
                        <span class="ml-1 text-xs text-gray-600">{{ item.rating }}</span>
                    </div>
                    <Badge variant="outline" class="text-xs">
                        {{ item.category }}
                    </Badge>
                </div>

                <!-- Price and Calories -->
                <div class="mt-2 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-green-600">{{ formatCurrency(item.price) }}</div>
                        <div class="text-xs text-gray-500">{{ item.calories }} cal</div>
                    </div>
                    <Button @click="addToCart(item)" size="sm" class="bg-green-600 px-3 py-1 text-xs hover:bg-green-700" :disabled="isAdding">
                        <Plus class="mr-1 h-3 w-3" />
                        {{ isAdding ? 'Adding...' : 'Add' }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Star } from 'lucide-vue-next';
import { ref } from 'vue';

interface RecommendedItem {
    id: number;
    name: string;
    description: string;
    price: number;
    image: string;
    category: string;
    rating: number;
    calories: number;
}

defineProps<{
    item: RecommendedItem;
}>();

const emit = defineEmits<{
    addToCart: [item: RecommendedItem];
}>();

const isAdding = ref(false);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const addToCart = async (item: RecommendedItem) => {
    isAdding.value = true;
    try {
        emit('addToCart', item);
    } finally {
        setTimeout(() => {
            isAdding.value = false;
        }, 1000);
    }
};
</script>
