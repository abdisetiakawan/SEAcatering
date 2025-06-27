<template>
    <AdminLayout>
        <Head title="Inventory Management" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Inventory Management</h1>
                                <p class="mt-1 text-gray-600">Kelola stok dan inventory</p>
                            </div>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('admin.inventory.low-stock-alert')"
                                    class="flex items-center rounded-md bg-orange-600 px-4 py-2 text-white hover:bg-orange-700"
                                >
                                    <AlertTriangle class="mr-2 h-4 w-4" />
                                    Low Stock Alert
                                </Link>
                                <Link
                                    :href="route('admin.inventory.create')"
                                    class="flex items-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                                >
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Inventory Item
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.total_items }}</div>
                        <div class="text-sm text-gray-600">Total Items</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-orange-600">{{ stats.low_stock_items }}</div>
                        <div class="text-sm text-gray-600">Low Stock</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-red-600">{{ stats.out_of_stock_items }}</div>
                        <div class="text-sm text-gray-600">Out of Stock</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.expiring_soon_items }}</div>
                        <div class="text-sm text-gray-600">Expiring Soon</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">Rp {{ formatPrice(stats.total_value) }}</div>
                        <div class="text-sm text-gray-600">Total Value</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Cari inventory..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select :model-value="filters.stock_status" @update:model-value="handleFilter">
                                <SelectTrigger class="w-[200px]">
                                    <SelectValue placeholder="Pilih Status Stok" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in stockStatusOptions" :key="value" :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="filters.supplier" @update:modelValue="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Supplier" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="supplier in suppliers" :key="supplier" :value="supplier">
                                        {{ supplier }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                        <input type="checkbox" @change="toggleSelectAll" class="rounded" />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Expiry Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in inventory.data" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="item.id" v-model="selectedItems" class="rounded" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ item.menu_item.name }}</div>
                                        <div class="text-sm text-gray-500">{{ item.unit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ item.current_stock }}</div>
                                        <div class="text-sm text-gray-500">Min: {{ item.minimum_stock }} | Max: {{ item.maximum_stock }}</div>
                                        <div class="mt-1 h-2 w-full rounded-full bg-gray-200">
                                            <div
                                                class="h-2 rounded-full"
                                                :class="getStockBarColor(item.stock_status)"
                                                :style="{ width: `${Math.min(100, (item.current_stock / item.maximum_stock) * 100)}%` }"
                                            ></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge :variant="getStatusVariant(item.stock_status)">
                                            {{ getStatusLabel(item.stock_status) }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ item.supplier }}
                                        <div class="text-sm text-gray-500">Rp {{ formatPrice(item.cost_per_unit) }}/{{ item.unit }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                        <div v-if="item.expiry_date">
                                            {{ formatDate(item.expiry_date) }}
                                            <div v-if="isExpiringSoon(item.expiry_date)" class="text-xs text-orange-600">Expiring Soon</div>
                                        </div>
                                        <span v-else class="text-gray-500">No expiry</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Button size="sm" variant="outline" @click="openStockModal(item)">
                                                <Package class="h-4 w-4" />
                                            </Button>
                                            <Link :href="route('admin.inventory.edit', item.id)" class="text-indigo-600 hover:text-indigo-900">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                            <Button size="sm" variant="outline" @click="confirmDelete(item)">
                                                <Trash class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedItems.length > 0" class="mt-4 rounded-lg bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ selectedItems.length }} items selected</span>
                        <div class="flex space-x-2">
                            <Button size="sm" @click="openBulkStockModal">
                                <Package class="mr-2 h-4 w-4" />
                                Update Stock
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="inventory.data.length > 0" class="mt-6 rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="inventory.links" />
                </div>
            </div>
        </div>

        <!-- Stock Update Modal -->
        <StockUpdateModal :show="showStockModal" :item="selectedItem" @close="showStockModal = false" @updated="handleStockUpdated" />

        <!-- Bulk Stock Update Modal -->
        <BulkStockUpdateModal
            :show="showBulkStockModal"
            :selectedItems="selectedItems"
            @close="showBulkStockModal = false"
            @updated="handleBulkStockUpdated"
        />

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Delete Inventory Item"
            message="Are you sure you want to delete this inventory item? This action cannot be undone."
            @confirm="deleteItem"
            @cancel="showDeleteModal = false"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import BulkStockUpdateModal from '@/components/Admin/BulkStockUpdateModal.vue';
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import StockUpdateModal from '@/components/Admin/StockUpdateModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, Edit, Package, Plus, RefreshCw, Search, Trash } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface InventoryItem {
    id: number;
    current_stock: number;
    minimum_stock: number;
    maximum_stock: number;
    unit: string;
    cost_per_unit: number;
    supplier: string;
    expiry_date: string | null;
    stock_status: string;
    menu_item: {
        name: string;
    };
}

const props = defineProps<{
    inventory: { data: InventoryItem[]; links: any[]; meta: any };
    filters: Record<string, any>;
    stats: Record<string, number>;
    suppliers: string[];
    stockStatusOptions: Record<string, string>;
}>();

// State
const showStockModal = ref(false);
const showBulkStockModal = ref(false);
const showDeleteModal = ref(false);
const selectedItem = ref<InventoryItem | undefined>(undefined);
const selectedItems = ref<number[]>([]);
const itemToDelete = ref<InventoryItem | undefined>(undefined); // Changed from null to undefined

// Methods
const openStockModal = (item: InventoryItem) => {
    selectedItem.value = item;
    showStockModal.value = true;
};

const openBulkStockModal = () => {
    showBulkStockModal.value = true;
};

const confirmDelete = (item: InventoryItem) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    if (itemToDelete.value) {
        router.delete(route('admin.inventory.destroy', itemToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = undefined; // Changed from null to undefined
            },
        });
    }
};

const handleStockUpdated = () => {
    showStockModal.value = false;
    selectedItem.value = undefined; // Changed from null to undefined
    router.reload();
};

const handleBulkStockUpdated = () => {
    showBulkStockModal.value = false;
    selectedItems.value = [];
    router.reload();
};
const toggleSelectAll = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.checked) {
        selectedItems.value = props.inventory.data.map((item) => item.id);
    } else {
        selectedItems.value = [];
    }
};

const getStatusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        in_stock: 'default',
        low_stock: 'outline',
        out_of_stock: 'destructive',
        expiring_soon: 'secondary',
    };
    return variants[status] || 'default';
};

const getStatusLabel = (status: string) => {
    return props.stockStatusOptions[status] || status;
};

const getStockBarColor = (status: string) => {
    const colors: Record<string, string> = {
        in_stock: 'bg-green-500',
        low_stock: 'bg-yellow-500',
        out_of_stock: 'bg-red-500',
        expiring_soon: 'bg-orange-500',
    };
    return colors[status] || 'bg-gray-500';
};

const isExpiringSoon = (expiryDate: string) => {
    const expiry = new Date(expiryDate);
    const today = new Date();
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 7 && diffDays >= 0;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID');
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const handleSearch = () => {
    router.get(route('admin.inventory.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.inventory.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

interface Filters {
    search: string;
    stock_status: string;
    supplier: string;
}

const filters = reactive<Filters>({
    search: props.filters.search || '',
    stock_status: props.filters.stock_status || '',
    supplier: props.filters.supplier || '',
});
const resetFilters = () => {
    (Object.keys(filters) as (keyof Filters)[]).forEach((key) => {
        filters[key] = '';
    });
    router.get(route('admin.inventory.index'));
};
</script>
