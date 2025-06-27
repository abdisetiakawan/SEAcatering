<template>
    <AdminLayout>
        <Head title="Low Stock Alert" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="flex items-center text-2xl font-bold text-gray-900">
                                    <AlertTriangle class="mr-3 h-6 w-6 text-orange-500" />
                                    Low Stock Alert
                                </h1>
                                <p class="mt-1 text-gray-600">Items that need immediate restocking</p>
                            </div>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('admin.inventory.index')"
                                    class="flex items-center rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                                >
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Back to Inventory
                                </Link>
                                <Button @click="refreshData" :disabled="refreshing">
                                    <RefreshCw class="mr-2 h-4 w-4" :class="{ 'animate-spin': refreshing }" />
                                    Refresh
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Summary -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-gradient-to-r from-red-500 to-red-600 p-4 text-white">
                        <div class="text-2xl font-bold">{{ criticalItems.length }}</div>
                        <div class="text-sm opacity-90">Critical (0 stock)</div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 p-4 text-white">
                        <div class="text-2xl font-bold">{{ lowStockItems.length - criticalItems.length }}</div>
                        <div class="text-sm opacity-90">Low Stock</div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-r from-yellow-500 to-yellow-600 p-4 text-white">
                        <div class="text-2xl font-bold">{{ totalValue }}</div>
                        <div class="text-sm opacity-90">Total Value Lost</div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 p-4 text-white">
                        <div class="text-2xl font-bold">{{ lowStockItems.length }}</div>
                        <div class="text-sm opacity-90">Total Items</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Search items..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select v-model="filters.priority" @update:modelValue="handleFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Priorities" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="critical">Critical (0 stock)</SelectItem>
                                    <SelectItem value="low">Low Stock</SelectItem>
                                    <SelectItem value="expiring">Expiring Soon</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="filters.category" @update:modelValue="handleFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div v-if="filteredItems.length > 0" class="space-y-4">
                    <LowStockItemCard
                        v-for="item in filteredItems"
                        :key="item.id"
                        :item="item"
                        @restock="openRestockModal"
                        @edit="editItem"
                        @mark-ordered="markAsOrdered"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <CheckCircle class="mx-auto mb-4 h-16 w-16 text-green-500" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">All Good!</h3>
                    <p class="text-gray-500">No low stock items found. Your inventory levels are healthy.</p>
                    <Link
                        :href="route('admin.inventory.index')"
                        class="mt-4 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        View All Inventory
                    </Link>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedItems.length > 0" class="fixed right-6 bottom-6 rounded-lg border bg-white p-4 shadow-lg">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ selectedItems.length }} items selected</span>
                        <Button size="sm" @click="bulkRestock">
                            <Package class="mr-2 h-4 w-4" />
                            Bulk Restock
                        </Button>
                        <Button size="sm" variant="outline" @click="bulkMarkOrdered">
                            <ShoppingCart class="mr-2 h-4 w-4" />
                            Mark as Ordered
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restock Modal -->
        <RestockModal :show="showRestockModal" :item="selectedItem" @close="showRestockModal = false" @restocked="handleRestocked" />

        <!-- Bulk Restock Modal -->
        <BulkRestockModal
            :show="showBulkRestockModal"
            :items="selectedItemsData"
            @close="showBulkRestockModal = false"
            @restocked="handleBulkRestocked"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import BulkRestockModal from '@/components/Admin/BulkRestockModal.vue';
import LowStockItemCard from '@/components/Admin/LowStockItemCard.vue';
import RestockModal from '@/components/Admin/RestockModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, CheckCircle, Package, RefreshCw, Search, ShoppingCart } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

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
    lowStockItems: LowStockItem[];
}

const props = defineProps<Props>();

// State
const refreshing = ref<boolean>(false);
const showRestockModal = ref<boolean>(false);
const showBulkRestockModal = ref<boolean>(false);
const selectedItem = ref<LowStockItem | null>(null);
const selectedItems = ref<number[]>([]);

const filters = reactive({
    search: '',
    priority: '',
    category: '',
});

// Computed
const criticalItems = computed((): LowStockItem[] => props.lowStockItems.filter((item) => item.current_stock === 0));

const categories = computed((): string[] => {
    const cats = props.lowStockItems.map((item) => item.menu_item.category).filter(Boolean) as string[];
    return [...new Set(cats)].sort();
});

const totalValue = computed((): string => {
    const value = props.lowStockItems.reduce((total, item) => {
        const neededStock = item.minimum_stock - item.current_stock;
        return total + neededStock * item.cost_per_unit;
    }, 0);
    return `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
});

const filteredItems = computed((): LowStockItem[] => {
    let items = [...props.lowStockItems];

    // Search filter
    if (filters.search) {
        const search = filters.search.toLowerCase();
        items = items.filter((item) => item.menu_item.name.toLowerCase().includes(search) || item.supplier.toLowerCase().includes(search));
    }

    // Priority filter
    if (filters.priority) {
        switch (filters.priority) {
            case 'critical':
                items = items.filter((item) => item.current_stock === 0);
                break;
            case 'low':
                items = items.filter((item) => item.current_stock > 0 && item.current_stock < item.minimum_stock);
                break;
            case 'expiring':
                items = items.filter((item) => item.stock_status === 'expiring');
                break;
        }
    }

    // Category filter
    if (filters.category) {
        items = items.filter((item) => item.menu_item.category === filters.category);
    }

    // Sort by priority: critical first, then by stock level
    return items.sort((a, b) => {
        if (a.current_stock === 0 && b.current_stock > 0) return -1;
        if (b.current_stock === 0 && a.current_stock > 0) return 1;
        return a.current_stock - b.current_stock;
    });
});

const selectedItemsData = computed((): LowStockItem[] => props.lowStockItems.filter((item) => selectedItems.value.includes(item.id)));

// Methods
const refreshData = (): void => {
    refreshing.value = true;
    router.reload({
        onFinish: () => {
            refreshing.value = false;
        },
    });
};

const handleSearch = (): void => {
    // Debounce search if needed
};

const handleFilter = (): void => {
    // Filter logic is handled by computed property
};

const openRestockModal = (item: LowStockItem): void => {
    selectedItem.value = item;
    showRestockModal.value = true;
};

const editItem = (item: LowStockItem): void => {
    router.visit(route('admin.inventory.edit', item.id));
};

const markAsOrdered = (item: LowStockItem): void => {
    router.patch(
        route('admin.inventory.mark-ordered', item.id),
        {},
        {
            onSuccess: () => {
                // Handle success
            },
        },
    );
};

const bulkRestock = (): void => {
    showBulkRestockModal.value = true;
};

const bulkMarkOrdered = (): void => {
    router.patch(
        route('admin.inventory.bulk-mark-ordered'),
        {
            item_ids: selectedItems.value,
        },
        {
            onSuccess: () => {
                selectedItems.value = [];
            },
        },
    );
};

const handleRestocked = (): void => {
    showRestockModal.value = false;
    selectedItem.value = null;
    refreshData();
};

const handleBulkRestocked = (): void => {
    showBulkRestockModal.value = false;
    selectedItems.value = [];
    refreshData();
};
</script>
