<template>
    <AdminLayout>
        <Head title="Add Inventory Item" />

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Add New Inventory Item</h1>
                                <p class="mt-1 text-gray-600">Create a new inventory item for tracking stock</p>
                            </div>
                            <Link
                                :href="route('admin.inventory.index')"
                                class="flex items-center rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                            >
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Inventory
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submitForm" class="space-y-6 p-6">
                        <!-- Menu Item Selection -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Menu Item <span class="text-red-500">*</span> </label>
                                <Select v-model="form.menu_item_id">
                                    <SelectTrigger class="w-full">
                                        {{ selectedMenuItem?.name || 'Select menu item' }}
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="item in menuItems" :key="item.id" :value="item.id.toString()">
                                            {{ item.name }} - {{ item.category }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <p v-if="errors.menu_item_id" class="mt-1 text-sm text-red-600">
                                    {{ errors.menu_item_id }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">Select the menu item this inventory will track</p>
                            </div>
                        </div>

                        <!-- Stock Information -->
                        <div class="border-t pt-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Stock Information</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Current Stock <span class="text-red-500">*</span>
                                    </label>
                                    <Input
                                        type="number"
                                        v-model="form.current_stock"
                                        min="0"
                                        :class="{ 'border-red-500': errors.current_stock }"
                                        placeholder="0"
                                        required
                                    />
                                    <p v-if="errors.current_stock" class="mt-1 text-sm text-red-600">
                                        {{ errors.current_stock }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Minimum Stock <span class="text-red-500">*</span>
                                    </label>
                                    <Input
                                        type="number"
                                        v-model="form.minimum_stock"
                                        min="0"
                                        :class="{ 'border-red-500': errors.minimum_stock }"
                                        placeholder="5"
                                        required
                                    />
                                    <p v-if="errors.minimum_stock" class="mt-1 text-sm text-red-600">
                                        {{ errors.minimum_stock }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">Alert when stock falls below this</p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Maximum Stock <span class="text-red-500">*</span>
                                    </label>
                                    <Input
                                        type="number"
                                        v-model="form.maximum_stock"
                                        min="1"
                                        :class="{ 'border-red-500': errors.maximum_stock }"
                                        placeholder="100"
                                        required
                                    />
                                    <p v-if="errors.maximum_stock" class="mt-1 text-sm text-red-600">
                                        {{ errors.maximum_stock }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">Maximum storage capacity</p>
                                </div>
                            </div>
                        </div>

                        <!-- Unit and Pricing -->
                        <div class="border-t pt-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Unit & Pricing</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700"> Unit <span class="text-red-500">*</span> </label>
                                    <Select v-model="form.unit">
                                        <SelectTrigger class="w-full">
                                            {{ form.unit || 'Select unit' }}
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pcs">Pieces (pcs)</SelectItem>
                                            <SelectItem value="kg">Kilogram (kg)</SelectItem>
                                            <SelectItem value="g">Gram (g)</SelectItem>
                                            <SelectItem value="l">Liter (l)</SelectItem>
                                            <SelectItem value="ml">Milliliter (ml)</SelectItem>
                                            <SelectItem value="box">Box</SelectItem>
                                            <SelectItem value="pack">Pack</SelectItem>
                                            <SelectItem value="bottle">Bottle</SelectItem>
                                            <SelectItem value="can">Can</SelectItem>
                                        </SelectContent>
                                    </Select>

                                    <p v-if="errors.unit" class="mt-1 text-sm text-red-600">
                                        {{ errors.unit }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Cost per Unit <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute top-3 left-3 text-gray-500">Rp</span>
                                        <Input
                                            type="number"
                                            v-model="form.cost_per_unit"
                                            min="0"
                                            step="0.01"
                                            :class="{ 'border-red-500': errors.cost_per_unit, 'pl-10': true }"
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>
                                    <p v-if="errors.cost_per_unit" class="mt-1 text-sm text-red-600">
                                        {{ errors.cost_per_unit }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Information -->
                        <div class="border-t pt-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Supplier Information</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Supplier Name <span class="text-red-500">*</span>
                                    </label>
                                    <Input
                                        type="text"
                                        v-model="form.supplier"
                                        :class="{ 'border-red-500': errors.supplier }"
                                        placeholder="Enter supplier name"
                                        required
                                    />
                                    <p v-if="errors.supplier" class="mt-1 text-sm text-red-600">
                                        {{ errors.supplier }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700"> Expiry Date </label>
                                    <Input type="date" v-model="form.expiry_date" :min="tomorrow" :class="{ 'border-red-500': errors.expiry_date }" />
                                    <p v-if="errors.expiry_date" class="mt-1 text-sm text-red-600">
                                        {{ errors.expiry_date }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">Leave empty if no expiry date</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="border-t pt-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Status</h3>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Status <span class="text-red-500">*</span> </label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="w-full">
                                        {{ form.status === 'active' ? 'Active' : 'Inactive' }}
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>

                                <p v-if="errors.status" class="mt-1 text-sm text-red-600">
                                    {{ errors.status }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">Inactive items won't appear in stock calculations</p>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div v-if="form.menu_item_id && form.current_stock && form.cost_per_unit" class="border-t pt-6">
                            <div class="rounded-lg bg-gray-50 p-4">
                                <h4 class="mb-2 font-medium text-gray-900">Summary</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                                    <div>
                                        <span class="text-gray-500">Selected Item:</span>
                                        <p class="font-medium">{{ selectedMenuItem?.name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Initial Stock:</span>
                                        <p class="font-medium">{{ form.current_stock }} {{ form.unit }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total Value:</span>
                                        <p class="font-medium">Rp {{ formatPrice(totalValue) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Stock Status:</span>
                                        <p class="font-medium" :class="stockStatusClass">{{ stockStatus }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 border-t pt-6">
                            <Link
                                :href="route('admin.inventory.index')"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <Button type="submit" :disabled="processing || !isFormValid" class="px-6 py-2">
                                <Package class="mr-2 h-4 w-4" />
                                {{ processing ? 'Creating...' : 'Create Inventory Item' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Package } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    category: string;
    price: number;
}

interface InventoryForm {
    menu_item_id: number | string;
    current_stock: number | string;
    minimum_stock: number | string;
    maximum_stock: number | string;
    unit: string;
    cost_per_unit: number | string;
    supplier: string;
    expiry_date: string;
    status: 'active' | 'inactive';
}

interface ValidationErrors {
    [key: string]: string;
}

interface Props {
    menuItems: MenuItem[];
    errors?: ValidationErrors;
}

const props = defineProps<Props>();

// State
const processing = ref<boolean>(false);
const errors = ref<ValidationErrors>(props.errors || {});

const form = reactive<InventoryForm>({
    menu_item_id: '',
    current_stock: '',
    minimum_stock: '',
    maximum_stock: '',
    unit: '',
    cost_per_unit: '',
    supplier: '',
    expiry_date: '',
    status: 'active',
});

// Computed
const tomorrow = computed((): string => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    return date.toISOString().split('T')[0];
});

const selectedMenuItem = computed((): MenuItem | undefined => {
    return props.menuItems.find((item) => item.id === Number(form.menu_item_id));
});

const totalValue = computed((): number => {
    const stock = Number(form.current_stock) || 0;
    const cost = Number(form.cost_per_unit) || 0;
    return stock * cost;
});

const stockStatus = computed((): string => {
    const current = Number(form.current_stock) || 0;
    const minimum = Number(form.minimum_stock) || 0;

    if (current === 0) return 'Out of Stock';
    if (current < minimum) return 'Low Stock';
    return 'In Stock';
});

const stockStatusClass = computed((): string => {
    const current = Number(form.current_stock) || 0;
    const minimum = Number(form.minimum_stock) || 0;

    if (current === 0) return 'text-red-600';
    if (current < minimum) return 'text-yellow-600';
    return 'text-green-600';
});

const isFormValid = computed((): boolean => {
    return !!(
        form.menu_item_id &&
        form.current_stock !== '' &&
        form.minimum_stock !== '' &&
        form.maximum_stock !== '' &&
        form.unit &&
        form.cost_per_unit !== '' &&
        form.supplier &&
        form.status
    );
});

// Methods
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const submitForm = (): void => {
    processing.value = true;
    errors.value = {};

    router.post(route('admin.inventory.store'), form, {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (formErrors) => {
            errors.value = formErrors;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
