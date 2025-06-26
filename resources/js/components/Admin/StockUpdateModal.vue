<template>
    <Modal :show="show" @close="closeModal" max-width="lg">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Update Stock</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div v-if="item" class="mb-6">
                <div class="flex items-center space-x-4">
                    <img
                        v-if="item.menu_item?.image"
                        :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                        :alt="item.menu_item.name"
                        class="h-16 w-16 rounded-lg object-cover"
                    />
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-900">{{ item.menu_item?.name || item.ingredient_name }}</h3>
                        <p class="text-sm text-gray-500">Current Stock: {{ item.current_stock }} {{ item.unit }}</p>
                        <p class="text-sm" :class="getStockStatusClass(item.current_stock, item.minimum_stock)">
                            {{ getStockStatus(item.current_stock, item.minimum_stock) }}
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitUpdate">
                <!-- Update Type -->
                <div class="mb-4">
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

                <!-- Quantity -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        {{
                            form.updateType === 'add'
                                ? 'Quantity to Add'
                                : form.updateType === 'subtract'
                                  ? 'Quantity to Subtract'
                                  : 'New Stock Quantity'
                        }}
                    </label>
                    <div class="flex space-x-2">
                        <input
                            type="number"
                            v-model="form.quantity"
                            min="0"
                            :max="form.updateType === 'subtract' ? item?.current_stock : undefined"
                            class="flex-1 rounded-md border border-gray-300 px-3 py-2"
                            required
                        />
                        <span class="flex items-center rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-600">
                            {{ item?.unit || 'pcs' }}
                        </span>
                    </div>
                    <div v-if="form.updateType !== 'set'" class="mt-2 text-sm text-gray-600">
                        New stock will be: {{ calculateNewStock() }} {{ item?.unit || 'pcs' }}
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Reason</label>
                    <select v-model="form.reason" class="mb-2 w-full rounded-md border border-gray-300 px-3 py-2">
                        <option value="">Select reason...</option>
                        <option v-if="form.updateType === 'add'" value="restock">Restock</option>
                        <option v-if="form.updateType === 'add'" value="purchase">New Purchase</option>
                        <option v-if="form.updateType === 'add'" value="return">Return/Refund</option>
                        <option v-if="form.updateType === 'subtract'" value="sold">Sold</option>
                        <option v-if="form.updateType === 'subtract'" value="damaged">Damaged/Expired</option>
                        <option v-if="form.updateType === 'subtract'" value="waste">Waste</option>
                        <option v-if="form.updateType === 'set'" value="audit">Stock Audit</option>
                        <option v-if="form.updateType === 'set'" value="correction">Correction</option>
                        <option value="other">Other</option>
                    </select>
                    <textarea
                        v-if="form.reason === 'other' || form.customReason"
                        v-model="form.customReason"
                        rows="2"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Enter custom reason..."
                    ></textarea>
                </div>

                <!-- Supplier (for restock) -->
                <div v-if="form.reason === 'restock' || form.reason === 'purchase'" class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Supplier</label>
                    <input
                        type="text"
                        v-model="form.supplier"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Enter supplier name..."
                    />
                </div>

                <!-- Cost (for purchases) -->
                <div v-if="form.reason === 'restock' || form.reason === 'purchase'" class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Cost per Unit (Optional)</label>
                    <div class="flex space-x-2">
                        <span class="flex items-center rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-600"> Rp </span>
                        <input
                            type="number"
                            v-model="form.costPerUnit"
                            min="0"
                            step="0.01"
                            class="flex-1 rounded-md border border-gray-300 px-3 py-2"
                            placeholder="0.00"
                        />
                    </div>
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
import { ref, watch } from 'vue';

// Updated interface to match the InventoryItem from parent component
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
        image?: string;
    };
    ingredient_name?: string;
}

interface StockUpdateForm {
    updateType: 'add' | 'subtract' | 'set';
    quantity: number | string;
    reason: string;
    customReason: string;
    supplier: string;
    costPerUnit: number | string;
}

interface Props {
    show: boolean;
    item?: InventoryItem; // Changed from 'item?: InventoryItem' to allow undefined but not null
}

interface Emits {
    (e: 'close'): void;
    (e: 'updated'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const processing = ref<boolean>(false);

const form = ref<StockUpdateForm>({
    updateType: 'add',
    quantity: '',
    reason: '',
    customReason: '',
    supplier: '',
    costPerUnit: '',
});

const calculateNewStock = (): number => {
    if (!props.item || !form.value.quantity) return props.item?.current_stock || 0;

    const current = props.item.current_stock || 0;
    const quantity = Number(form.value.quantity) || 0;

    switch (form.value.updateType) {
        case 'add':
            return current + quantity;
        case 'subtract':
            return Math.max(0, current - quantity);
        case 'set':
            return quantity;
        default:
            return current;
    }
};

const getStockStatus = (current: number, minimum: number): string => {
    if (current <= 0) return 'Out of Stock';
    if (current < minimum) return 'Low Stock';
    return 'In Stock';
};

const getStockStatusClass = (current: number, minimum: number): string => {
    if (current <= 0) return 'text-red-600';
    if (current < minimum) return 'text-yellow-600';
    return 'text-green-600';
};

const submitUpdate = (): void => {
    if (!props.item) return;

    processing.value = true;

    const data = {
        ...form.value,
        item_id: props.item.id,
    };

    router.post(`/admin/inventory/${props.item.id}/update-stock`, data, {
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
        quantity: '',
        reason: '',
        customReason: '',
        supplier: '',
        costPerUnit: '',
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
