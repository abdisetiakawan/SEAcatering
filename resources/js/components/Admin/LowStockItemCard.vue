<template>
    <div class="rounded-lg border-l-4 bg-white p-6 shadow-sm transition-shadow duration-200 hover:shadow-md" :class="borderColorClass">
        <div class="flex items-center justify-between">
            <!-- Item Info -->
            <div class="flex flex-1 items-center space-x-4">
                <div class="relative">
                    <img
                        v-if="item.menu_item.image"
                        :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                        :alt="item.menu_item.name"
                        class="h-16 w-16 rounded-lg object-cover"
                    />
                    <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200">
                        <Package class="h-6 w-6 text-gray-400" />
                    </div>

                    <!-- Priority Badge -->
                    <div class="absolute -top-2 -right-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold text-white" :class="priorityBadgeClass">
                            {{ priorityText }}
                        </div>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="mb-1 flex items-center space-x-2">
                        <h3 class="font-semibold text-gray-900">{{ item.menu_item.name }}</h3>
                        <Badge :variant="statusVariant">{{ statusText }}</Badge>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 md:grid-cols-4">
                        <div>
                            <span class="font-medium">Current:</span>
                            <span class="ml-1" :class="stockTextClass"> {{ item.current_stock }} {{ item.unit }} </span>
                        </div>
                        <div>
                            <span class="font-medium">Min Required:</span>
                            <span class="ml-1">{{ item.minimum_stock }} {{ item.unit }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Supplier:</span>
                            <span class="ml-1">{{ item.supplier }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Last Restock:</span>
                            <span class="ml-1">{{ lastRestockText }}</span>
                        </div>
                    </div>

                    <!-- Stock Progress Bar -->
                    <div class="mt-3">
                        <div class="mb-1 flex justify-between text-xs text-gray-500">
                            <span>Stock Level</span>
                            <span>{{ stockPercentage }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="progressBarClass"
                                :style="{ width: `${Math.min(100, stockPercentage)}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                        <span v-if="item.expiry_date" class="flex items-center">
                            <Calendar class="mr-1 h-3 w-3" />
                            Expires: {{ formatDate(item.expiry_date) }}
                        </span>
                        <span class="flex items-center">
                            <DollarSign class="mr-1 h-3 w-3" />
                            Rp {{ formatPrice(item.cost_per_unit) }}/{{ item.unit }}
                        </span>
                        <span v-if="estimatedCost > 0" class="flex items-center text-orange-600">
                            <TrendingUp class="mr-1 h-3 w-3" />
                            Need: Rp {{ formatPrice(estimatedCost) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="ml-4 flex items-center space-x-2">
                <Button size="sm" @click="$emit('restock', item)" class="bg-green-600 hover:bg-green-700">
                    <Package class="mr-1 h-4 w-4" />
                    Restock
                </Button>

                <Button size="sm" variant="outline" @click="$emit('mark-ordered', item)">
                    <ShoppingCart class="mr-1 h-4 w-4" />
                    Mark Ordered
                </Button>

                <div class="relative">
                    <Button size="sm" variant="ghost" @click="showDropdown = !showDropdown">
                        <MoreVertical class="h-4 w-4" />
                    </Button>

                    <div v-if="showDropdown" class="absolute right-0 z-10 mt-2 w-48 rounded-md border bg-white shadow-lg">
                        <div class="py-1">
                            <button
                                @click="
                                    $emit('edit', item);
                                    showDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <Edit class="mr-2 inline h-4 w-4" />
                                Edit Item
                            </button>
                            <button
                                @click="
                                    viewHistory();
                                    showDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <History class="mr-2 inline h-4 w-4" />
                                View History
                            </button>
                            <button
                                @click="
                                    generateReport();
                                    showDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <FileText class="mr-2 inline h-4 w-4" />
                                Generate Report
                            </button>
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
import { Calendar, DollarSign, Edit, FileText, History, MoreVertical, Package, ShoppingCart, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    image?: string;
    category?: string;
}

interface LowStockItem {
    id: number;
    current_stock: number;
    minimum_stock: number;
    maximum_stock: number;
    unit: string;
    cost_per_unit: number;
    supplier: string;
    expiry_date?: string;
    last_restocked_at?: string;
    menu_item: MenuItem;
    days_since_restock: number;
    stock_status: 'critical' | 'low' | 'expiring';
    estimated_days_remaining: number;
}

interface Props {
    item: LowStockItem;
}

interface Emits {
    (e: 'restock', item: LowStockItem): void;
    (e: 'edit', item: LowStockItem): void;
    (e: 'mark-ordered', item: LowStockItem): void;
}

const props = defineProps<Props>();
defineEmits<Emits>();

// State
const showDropdown = ref<boolean>(false);

// Computed
const borderColorClass = computed((): string => {
    switch (props.item.stock_status) {
        case 'critical':
            return 'border-red-500';
        case 'low':
            return 'border-orange-500';
        case 'expiring':
            return 'border-yellow-500';
        default:
            return 'border-gray-300';
    }
});

const priorityBadgeClass = computed((): string => {
    switch (props.item.stock_status) {
        case 'critical':
            return 'bg-red-500';
        case 'low':
            return 'bg-orange-500';
        case 'expiring':
            return 'bg-yellow-500';
        default:
            return 'bg-gray-500';
    }
});

const priorityText = computed((): string => {
    switch (props.item.stock_status) {
        case 'critical':
            return '!';
        case 'low':
            return '⚠';
        case 'expiring':
            return '⏰';
        default:
            return 'i';
    }
});

const statusVariant = computed((): 'default' | 'destructive' | 'outline' | 'secondary' | null | undefined => {
    switch (props.item.stock_status) {
        case 'critical':
            return 'destructive';
        case 'low':
        case 'expiring':
            return 'outline';
        default:
            return 'default';
    }
});

const statusText = computed((): string => {
    switch (props.item.stock_status) {
        case 'critical':
            return 'Critical';
        case 'low':
            return 'Low Stock';
        case 'expiring':
            return 'Expiring Soon';
        default:
            return 'Normal';
    }
});

const stockTextClass = computed((): string => {
    if (props.item.current_stock === 0) return 'text-red-600 font-bold';
    if (props.item.current_stock < props.item.minimum_stock) return 'text-orange-600 font-medium';
    return 'text-gray-900';
});

const stockPercentage = computed((): number => {
    return Math.round((props.item.current_stock / props.item.maximum_stock) * 100);
});

const progressBarClass = computed((): string => {
    if (props.item.current_stock === 0) return 'bg-red-500';
    if (props.item.current_stock < props.item.minimum_stock) return 'bg-orange-500';
    return 'bg-green-500';
});

const lastRestockText = computed((): string => {
    if (!props.item.last_restocked_at) return 'Never';

    const days = props.item.days_since_restock;
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    return `${days} days ago`;
});

const estimatedCost = computed((): number => {
    const needed = Math.max(0, props.item.minimum_stock - props.item.current_stock);
    return needed * props.item.cost_per_unit;
});

// Methods
const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID');
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const viewHistory = (): void => {
    // Implementation for viewing stock history
    console.log('View history for item:', props.item.id);
};

const generateReport = (): void => {
    // Implementation for generating report
    console.log('Generate report for item:', props.item.id);
};
</script>
