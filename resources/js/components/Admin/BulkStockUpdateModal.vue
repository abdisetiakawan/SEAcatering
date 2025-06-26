<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Bulk Stock Update</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitBulkUpdate">
                <!-- Update Type Selection -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Update Type</label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex items-center">
                            <input type="radio" v-model="form.updateType" value="add" class="mr-2 text-blue-600" />
                            <span class="text-sm">Add Stock</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" v-model="form.updateType" value="subtract" class="mr-2 text-blue-600" />
                            <span class="text-sm">Subtract Stock</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" v-model="form.updateType" value="set" class="mr-2 text-blue-600" />
                            <span class="text-sm">Set Stock</span>
                        </label>
                    </div>
                </div>

                <!-- Filter Options -->
                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Category Filter</label>
                        <select v-model="form.categoryFilter" class="w-full rounded-md border border-gray-300 px-3 py-2">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Stock Level Filter</label>
                        <select v-model="form.stockFilter" class="w-full rounded-md border border-gray-300 px-3 py-2">
                            <option value="">All Items</option>
                            <option value="low">Low Stock Only ( 10)</option>
                            <option value="out">Out of Stock Only</option>
                            <option value="normal">Normal Stock (>= 10)</option>
                        </select>
                    </div>
                </div>

                <!-- Quantity Input -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        {{
                            form.updateType === 'add'
                                ? 'Quantity to Add'
                                : form.updateType === 'subtract'
                                  ? 'Quantity to Subtract'
                                  : 'New Stock Quantity'
                        }}
                    </label>
                    <input type="number" v-model="form.quantity" min="0" class="w-full rounded-md border border-gray-300 px-3 py-2" required />
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Reason</label>
                    <textarea
                        v-model="form.reason"
                        rows="3"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Enter reason for bulk stock update..."
                        required
                    ></textarea>
                </div>

                <!-- Preview -->
                <div class="mb-6 rounded-lg bg-gray-50 p-4">
                    <h3 class="mb-2 font-medium text-gray-900">Preview</h3>
                    <p class="text-sm text-gray-600">
                        This will {{ form.updateType }} {{ form.quantity }}
                        {{ form.updateType === 'set' ? 'as new stock for' : 'stock for' }}
                        {{ filteredItemsCount }} items
                        {{ form.categoryFilter ? `in ${getCategoryName(form.categoryFilter)}` : '' }}
                        {{ form.stockFilter ? `(${getStockFilterText(form.stockFilter)})` : '' }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="processing || !form.quantity || !form.reason"
                        class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ processing ? 'Updating...' : 'Update Stock' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
}

interface InventoryItem {
    id: number;
    current_stock: number;
    category_id: number;
    menu_item?: {
        name: string;
        image?: string;
    };
    ingredient_name?: string;
    unit: string;
    minimum_stock: number;
}

interface BulkUpdateForm {
    updateType: 'add' | 'subtract' | 'set';
    categoryFilter: string;
    stockFilter: string;
    quantity: number | string;
    reason: string;
}

interface Props {
    show: boolean;
    categories?: Category[];
    inventoryItems?: InventoryItem[];
}

interface Emits {
    (e: 'close'): void;
    (e: 'updated'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const processing = ref<boolean>(false);

const form = ref<BulkUpdateForm>({
    updateType: 'add',
    categoryFilter: '',
    stockFilter: '',
    quantity: '',
    reason: '',
});

const filteredItemsCount = computed((): number => {
    let items = props.inventoryItems || [];

    if (form.value.categoryFilter) {
        items = items.filter((item) => item.category_id == Number(form.value.categoryFilter));
    }

    if (form.value.stockFilter) {
        switch (form.value.stockFilter) {
            case 'low':
                items = items.filter((item) => item.current_stock < 10 && item.current_stock > 0);
                break;
            case 'out':
                items = items.filter((item) => item.current_stock <= 0);
                break;
            case 'normal':
                items = items.filter((item) => item.current_stock >= 10);
                break;
        }
    }

    return items.length;
});

const getCategoryName = (categoryId: string): string => {
    const category = props.categories?.find((c) => c.id == Number(categoryId));
    return category ? category.name : '';
};

const getStockFilterText = (filter: string): string => {
    const texts: Record<string, string> = {
        low: 'Low Stock Items',
        out: 'Out of Stock Items',
        normal: 'Normal Stock Items',
    };
    return texts[filter] || '';
};

const submitBulkUpdate = (): void => {
    processing.value = true;

    router.post('/admin/inventory/bulk-update', form.value, {
        onSuccess: () => {
            emit('updated');
            closeModal();
        },
        onError: () => {
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const closeModal = (): void => {
    form.value = {
        updateType: 'add',
        categoryFilter: '',
        stockFilter: '',
        quantity: '',
        reason: '',
    };
    emit('close');
};

watch(
    () => props.show,
    (newVal: boolean) => {
        if (!newVal) {
            closeModal();
        }
    },
);
</script>
