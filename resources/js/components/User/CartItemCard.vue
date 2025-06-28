<template>
    <div class="p-6 hover:bg-gray-50">
        <div class="flex items-start space-x-4">
            <!-- Item Image -->
            <div class="flex-shrink-0">
                <img
                    :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                    :alt="item.menu_item.name"
                    class="h-20 w-20 rounded-lg object-cover"
                />
            </div>

            <!-- Item Details -->
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ item.menu_item.name }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ item.menu_item.description }}</p>

                        <!-- Category Badge -->
                        <div class="mt-2">
                            <Badge variant="outline" class="text-xs">
                                {{ item.menu_item.category }}
                            </Badge>
                            <Badge v-if="!item.menu_item.is_available" variant="destructive" class="ml-2 text-xs"> Unavailable </Badge>
                        </div>

                        <!-- Nutrition Info -->
                        <div class="mt-3 flex items-center space-x-4 text-xs text-gray-500">
                            <div class="flex items-center">
                                <Zap class="mr-1 h-3 w-3" />
                                {{ item.total_calories }} cal
                            </div>
                            <div class="flex items-center">
                                <Activity class="mr-1 h-3 w-3" />
                                {{ item.total_protein }}g protein
                            </div>
                        </div>

                        <!-- Added Date -->
                        <div class="mt-2 text-xs text-gray-400">Added {{ formatDate(item.created_at) }}</div>
                    </div>

                    <!-- Remove Button -->
                    <Button @click="removeItem(item.id)" variant="ghost" size="sm" class="text-red-600 hover:bg-red-50 hover:text-red-700">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Quantity and Price -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-600">Quantity:</span>
                        <div class="flex items-center space-x-2">
                            <Button
                                @click="updateQuantity(item.quantity - 1)"
                                variant="outline"
                                size="sm"
                                class="h-8 w-8 p-0"
                                :disabled="item.quantity <= 1 || !item.menu_item.is_available"
                            >
                                <Minus class="h-3 w-3" />
                            </Button>
                            <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
                            <Button
                                @click="updateQuantity(item.quantity + 1)"
                                variant="outline"
                                size="sm"
                                class="h-8 w-8 p-0"
                                :disabled="item.quantity >= 10 || !item.menu_item.is_available"
                            >
                                <Plus class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ formatCurrency(item.price) }} × {{ item.quantity }}</div>
                        <div class="text-lg font-semibold text-green-600">
                            {{ formatCurrency(item.subtotal) }}
                        </div>
                    </div>
                </div>

                <!-- Unavailable Warning -->
                <div v-if="!item.menu_item.is_available" class="mt-3 rounded-md bg-red-50 p-3">
                    <div class="flex">
                        <AlertTriangle class="h-5 w-5 text-red-400" />
                        <div class="ml-3">
                            <p class="text-sm text-red-800">
                                This item is currently unavailable. Please remove it from your cart or it will be automatically removed at checkout.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Activity, AlertTriangle, Minus, Plus, Trash2, Zap } from 'lucide-vue-next';

interface CartItem {
    id: number;
    menu_item_id: number;
    quantity: number;
    price: number;
    subtotal: number;
    total_calories: number;
    total_protein: number;
    created_at: string;
    menu_item: {
        id: number;
        name: string;
        description: string;
        image: string;
        category: string;
        calories: number;
        protein: number;
        carbs: number;
        fat: number;
        is_available: boolean;
    };
}

const props = defineProps<{
    item: CartItem;
}>();

const emit = defineEmits<{
    updateQuantity: [cartId: number, quantity: number];
    removeItem: [cartId: number];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const updateQuantity = (newQuantity: number) => {
    if (newQuantity >= 1 && newQuantity <= 10) {
        emit('updateQuantity', props.item.id, newQuantity);
    }
};

const removeItem = (cartId: number) => {
    emit('removeItem', cartId);
};
</script>
