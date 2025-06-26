<template>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md">
        <!-- Image -->
        <div class="relative aspect-video bg-gray-100">
            <img
                v-if="item.image"
                :src="item.image.startsWith('http') ? item.image : `/storage/${item.image}`"
                :alt="item.name"
                class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full items-center justify-center">
                <Utensils class="h-12 w-12 text-gray-400" />
            </div>

            <!-- Status Badge -->
            <div class="absolute top-2 right-2">
                <Badge :class="item.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ item.is_available ? 'Aktif' : 'Tidak Aktif' }}
                </Badge>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-lg font-semibold text-gray-900">{{ item.name }}</h3>
                <Badge variant="outline" class="text-xs">{{ getCategoryLabel(item.category) }}</Badge>
            </div>

            <p class="mb-3 line-clamp-2 text-sm text-gray-600">{{ item.description }}</p>

            <!-- Nutrition Info -->
            <div class="mb-3 grid grid-cols-2 gap-2 text-xs text-gray-500">
                <div>Kalori: {{ item.calories }}</div>
                <div>Protein: {{ item.protein }}g</div>
                <div>Karbo: {{ item.carbs }}g</div>
                <div>Lemak: {{ item.fat }}g</div>
            </div>

            <!-- Price -->
            <div class="mb-4 flex items-center justify-between">
                <span class="text-xl font-bold text-green-600"> Rp {{ formatPrice(item.price) }} </span>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <Button variant="outline" size="sm" @click="$emit('edit', item)" class="flex-1">
                    <Edit class="mr-1 h-4 w-4" />
                    Edit
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    @click="$emit('toggle-status', item)"
                    :class="item.is_available ? 'text-orange-600 hover:text-orange-700' : 'text-green-600 hover:text-green-700'"
                >
                    {{ item.is_available ? 'Nonaktifkan' : 'Aktifkan' }}
                </Button>
                <Button variant="outline" size="sm" @click="$emit('delete', item)" class="text-red-600 hover:text-red-700">
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Edit, Trash2, Utensils } from 'lucide-vue-next';

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    category: string;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
    image: string;
    is_available: boolean;
}

defineProps<{
    item: MenuItem;
}>();

defineEmits<{
    edit: [item: MenuItem];
    delete: [item: MenuItem];
    'toggle-status': [item: MenuItem];
}>();

const getCategoryLabel = (category: string) => {
    const labels: Record<string, string> = {
        diet: 'Diet Plan',
        protein: 'Protein Plan',
        royal: 'Royal Plan',
        vegetarian: 'Vegetarian',
        seafood: 'Seafood',
    };
    return labels[category] || category;
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
