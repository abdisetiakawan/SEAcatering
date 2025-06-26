<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Bulk Restock Items</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Selected Items Summary -->
            <div class="mb-6 rounded-lg bg-gray-50 p-4">
                <h3 class="mb-3 font-medium text-gray-900">Selected Items ({{ items.length }})</h3>
                <div class="max-h-40 space-y-2 overflow-y-auto">
                    <div v-for="item in items" :key="item.id" class="flex items-center justify-between text-sm">
                        <span>{{ item.menu_item?.name }}</span>
                        <span class="text-gray-500">{{ item.current_stock }}/{{ item.minimum_stock }} {{ item.unit }}</span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitBulkRestock">
                <!-- Restock Strategy -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Restock Strategy</label>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <label
                            class="flex cursor-pointer items-center rounded-lg border p-3 hover:bg-gray-50"
                            :class="{ 'border-blue-500 bg-blue-50': form.strategy === 'minimum' }"
                        >
                            <input type="radio" v-model="form.strategy" value="minimum" class="mr-3" />
                            <div>
                                <div class="font-medium">To Minimum</div>
                                <div class="text-sm text-gray-500">Restock to minimum level</div>
                            </div>
                        </label>

                        <label
                            class="flex cursor-pointer items-center rounded-lg border p-3 hover:bg-gray-50"
                            :class="{ 'border-blue-500 bg-blue-50': form.strategy === 'optimal' }"
                        >
                            <input type="radio" v-model="form.strategy" value="optimal" class="mr-3" />
                            <div>
                                <div class="font-medium">Optimal Level</div>
                                <div class="text-sm text-gray-500">Min + 50% buffer</div>
                            </div>
                        </label>

                        <label
                            class="flex cursor-pointer items-center rounded-lg border p-3 hover:bg-gray-50"
                            :class="{ 'border-blue-500 bg-blue-50': form.strategy === 'custom' }"
                        >
                            <input type="radio" v-model="form.strategy" value="custom" class="mr-3" />
                            <div>
                                <div class="font-medium">Custom Amount</div>
                                <div class="text-sm text-gray-500">Same quantity for all</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Custom Quantity (if custom strategy) -->
                <div v-if="form.strategy === 'custom'" class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Quantity to Add (for each item) </label>
                    <Input type="number" v-model="form.customQuantity" min="1" placeholder="Enter quantity" required />
                </div>

                <!-- Supplier Information -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Default Supplier <span class="text-red-500">*</span> </label>
                    <Input type="text" v-model="form.supplier" placeholder="Enter supplier name" required />
                    <p class="mt-1 text-sm text-gray-500">This will be used for all items unless individually specified</p>
                </div>

                <!-- Cost Adjustment -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Cost Adjustment </label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex items-center">
                            <input type="radio" v-model="form.costAdjustment" value="keep" class="mr-2" />
                            <span class="text-sm">Keep Current</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" v-model="form.costAdjustment" value="increase" class="mr-2" />
                            <span class="text-sm">Increase by %</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" v-model="form.costAdjustment" value="set" class="mr-2" />
                            <span class="text-sm">Set New Price</span>
                        </label>
                    </div>

                    <div v-if="form.costAdjustment === 'increase'" class="mt-2">
                        <Input type="number" v-model="form.costPercentage" min="0" max="100" placeholder="Percentage increase" />
                    </div>

                    <div v-if="form.costAdjustment === 'set'" class="mt-2">
                        <Input type="number" v-model="form.newCostPerUnit" min="0" step="0.01" placeholder="New cost per unit" />
                    </div>
                </div>

                <!-- Expiry Date -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Expiry Date (Optional) </label>
                    <Input type="date" v-model="form.expiryDate" :min="tomorrow" />
                    <p class="mt-1 text-sm text-gray-500">Applied to all items if specified</p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Notes </label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Add notes for this bulk restock operation..."
                    ></textarea>
                </div>

                <!-- Summary -->
                <div class="mb-6 rounded-lg bg-blue-50 p-4">
                    <h4 class="mb-2 font-medium text-blue-900">Restock Summary</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                        <div>
                            <span class="text-blue-700">Items:</span>
                            <p class="font-medium">{{ items.length }}</p>
                        </div>
                        <div>
                            <span class="text-blue-700">Total Units:</span>
                            <p class="font-medium">{{ totalUnits }}</p>
                        </div>
                        <div>
                            <span class="text-blue-700">Estimated Cost:</span>
                            <p class="font-medium">Rp {{ formatPrice(estimatedCost) }}</p>
                        </div>
                        <div>
                            <span class="text-blue-700">Strategy:</span>
                            <p class="font-medium capitalize">{{ form.strategy }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="outline" @click="closeModal"> Cancel </Button>
                    <Button type="submit" :disabled="processing || !form.supplier">
                        {{ processing ? 'Processing...' : 'Restock All Items' }}
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface MenuItem {
    name: string;
    image?: string;
}

interface LowStockItem {
    id: number;
    current_stock: number;
    minimum_stock: number;
    maximum_stock: number;
    unit: string;
    cost_per_unit: number;
    supplier: string;
    menu_item?: MenuItem;
}

interface BulkRestockForm {
    strategy: 'minimum' | 'optimal' | 'custom';
    customQuantity: number | string;
    supplier: string;
    costAdjustment: 'keep' | 'increase' | 'set';
    costPercentage: number | string;
    newCostPerUnit: number | string;
    expiryDate: string;
    notes: string;
}

interface Props {
    show: boolean;
    items: LowStockItem[];
}

interface Emits {
    (e: 'close'): void;
    (e: 'restocked'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// State
const processing = ref<boolean>(false);

const form = reactive<BulkRestockForm>({
    strategy: 'minimum',
    customQuantity: '',
    supplier: '',
    costAdjustment: 'keep',
    costPercentage: '',
    newCostPerUnit: '',
    expiryDate: '',
    notes: '',
});

// Computed
const tomorrow = computed((): string => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    return date.toISOString().split('T')[0];
});

const totalUnits = computed((): number => {
    return props.items.reduce((total, item) => {
        let quantity = 0;

        switch (form.strategy) {
            case 'minimum':
                quantity = Math.max(0, item.minimum_stock - item.current_stock);
                break;
            case 'optimal':
                const optimal = item.minimum_stock + Math.floor(item.minimum_stock * 0.5);
                quantity = Math.max(0, optimal - item.current_stock);
                break;
            case 'custom':
                quantity = Number(form.customQuantity) || 0;
                break;
        }

        return total + quantity;
    }, 0);
});

const estimatedCost = computed((): number => {
    return props.items.reduce((total, item) => {
        let quantity = 0;

        switch (form.strategy) {
            case 'minimum':
                quantity = Math.max(0, item.minimum_stock - item.current_stock);
                break;
            case 'optimal':
                const optimal = item.minimum_stock + Math.floor(item.minimum_stock * 0.5);
                quantity = Math.max(0, optimal - item.current_stock);
                break;
            case 'custom':
                quantity = Number(form.customQuantity) || 0;
                break;
        }

        let cost = item.cost_per_unit;

        switch (form.costAdjustment) {
            case 'increase':
                const percentage = Number(form.costPercentage) || 0;
                cost = cost * (1 + percentage / 100);
                break;
            case 'set':
                cost = Number(form.newCostPerUnit) || cost;
                break;
        }

        return total + quantity * cost;
    }, 0);
});

// Methods
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const submitBulkRestock = (): void => {
    processing.value = true;

    router.post(
        route('admin.inventory.bulk-restock'),
        {
            item_ids: props.items.map((item) => item.id),
            strategy: form.strategy,
            custom_quantity: form.customQuantity,
            supplier: form.supplier,
            cost_adjustment: form.costAdjustment,
            cost_percentage: form.costPercentage,
            new_cost_per_unit: form.newCostPerUnit,
            expiry_date: form.expiryDate,
            notes: form.notes,
        },
        {
            onSuccess: () => {
                emit('restocked');
                closeModal();
            },
            onError: () => {
                processing.value = false;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const closeModal = (): void => {
    Object.assign(form, {
        strategy: 'minimum',
        customQuantity: '',
        supplier: '',
        costAdjustment: 'keep',
        costPercentage: '',
        newCostPerUnit: '',
        expiryDate: '',
        notes: '',
    });
    emit('close');
};
</script>
