<template>
    <Modal :show="show" @close="closeModal" max-width="lg">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Restock Item</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <div v-if="item" class="mb-6">
                <div class="flex items-center space-x-4 rounded-lg bg-gray-50 p-4">
                    <img
                        v-if="item.menu_item?.image"
                        :src="item.menu_item.image"
                        :alt="item.menu_item.name"
                        class="h-16 w-16 rounded-lg object-cover"
                    />
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-900">{{ item.menu_item?.name }}</h3>
                        <div class="mt-2 grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                Current: <span class="font-medium text-red-600">{{ item.current_stock }} {{ item.unit }}</span>
                            </div>
                            <div>
                                Minimum: <span class="font-medium">{{ item.minimum_stock }} {{ item.unit }}</span>
                            </div>
                            <div>
                                Recommended: <span class="font-medium text-green-600">{{ recommendedQuantity }} {{ item.unit }}</span>
                            </div>
                            <div>
                                Cost/Unit: <span class="font-medium">Rp {{ formatPrice(item.cost_per_unit) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitRestock">
                <!-- Restock Quantity -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Restock Quantity <span class="text-red-500">*</span> </label>
                    <div class="flex space-x-2">
                        <Input
                            type="number"
                            v-model="form.quantity"
                            min="1"
                            :max="item?.maximum_stock"
                            class="flex-1"
                            placeholder="Enter quantity"
                            required
                        />
                        <span class="flex items-center rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-600">
                            {{ item?.unit }}
                        </span>
                        <Button type="button" variant="outline" size="sm" @click="useRecommended"> Use Recommended </Button>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">New stock will be: {{ newStockLevel }} {{ item?.unit }}</div>
                </div>

                <!-- Supplier -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Supplier <span class="text-red-500">*</span> </label>
                    <Input type="text" v-model="form.supplier" placeholder="Enter supplier name" required />
                </div>

                <!-- Cost Information -->
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700"> Cost per Unit </label>
                        <div class="relative">
                            <span class="absolute top-3 left-3 text-gray-500">Rp</span>
                            <Input type="number" v-model="form.cost_per_unit" min="0" step="0.01" class="pl-10" placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700"> Total Cost </label>
                        <div class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-900">
                            Rp {{ formatPrice(totalCost) }}
                        </div>
                    </div>
                </div>

                <!-- Expiry Date -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Expiry Date (Optional) </label>
                    <Input type="date" v-model="form.expiry_date" :min="tomorrow" />
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Notes </label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Add any notes about this restock..."
                    ></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="outline" @click="closeModal"> Cancel </Button>
                    <Button type="submit" :disabled="processing || !form.quantity || !form.supplier">
                        {{ processing ? 'Restocking...' : 'Restock Item' }}
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
import { computed, reactive, ref, watch } from 'vue';

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

interface RestockForm {
    quantity: number | string;
    supplier: string;
    cost_per_unit: number | string;
    expiry_date: string;
    notes: string;
}

interface Props {
    show: boolean;
    item?: LowStockItem;
}

interface Emits {
    (e: 'close'): void;
    (e: 'restocked'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// State
const processing = ref<boolean>(false);

const form = reactive<RestockForm>({
    quantity: '',
    supplier: '',
    cost_per_unit: '',
    expiry_date: '',
    notes: '',
});

// Computed
const tomorrow = computed((): string => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    return date.toISOString().split('T')[0];
});

const recommendedQuantity = computed((): number => {
    if (!props.item) return 0;
    return Math.max(0, props.item.minimum_stock - props.item.current_stock + 10);
});

const newStockLevel = computed((): number => {
    if (!props.item) return 0;
    return props.item.current_stock + (Number(form.quantity) || 0);
});

const totalCost = computed((): number => {
    const quantity = Number(form.quantity) || 0;
    const cost = Number(form.cost_per_unit) || props.item?.cost_per_unit || 0;
    return quantity * cost;
});

// Methods
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const useRecommended = (): void => {
    form.quantity = recommendedQuantity.value;
};

const submitRestock = (): void => {
    if (!props.item) return;

    processing.value = true;

    router.patch(
        route('admin.inventory.update-stock', props.item.id),
        {
            type: 'add',
            quantity: form.quantity,
            supplier: form.supplier,
            cost_per_unit: form.cost_per_unit,
            expiry_date: form.expiry_date,
            notes: form.notes,
            reason: 'restock',
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
        quantity: '',
        supplier: '',
        cost_per_unit: '',
        expiry_date: '',
        notes: '',
    });
    emit('close');
};

// Watchers
watch(
    () => props.show,
    (newVal: boolean) => {
        if (newVal && props.item) {
            form.supplier = props.item.supplier;
            form.cost_per_unit = props.item.cost_per_unit;
            form.quantity = recommendedQuantity.value;
        }
    },
);
</script>
