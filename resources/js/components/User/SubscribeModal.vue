<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle class="text-xl font-bold">Subscribe to {{ plan?.name }}</DialogTitle>
                <DialogDescription> Pilih paket langganan yang sesuai dengan kebutuhan Anda </DialogDescription>
            </DialogHeader>

            <div v-if="plan" class="space-y-6">
                <!-- Plan Summary -->
                <div class="rounded-lg border bg-gray-50 p-4">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <img v-if="plan.image" :src="plan.image" :alt="plan.name" class="h-16 w-16 rounded-lg object-cover" />
                            <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200">
                                <Calendar class="h-8 w-8 text-gray-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ plan.name }}</h3>
                            <p class="text-sm text-gray-600">{{ plan.description }}</p>
                            <div class="mt-2 flex items-center space-x-4 text-sm">
                                <span class="text-gray-600">{{ plan.target_calories }} cal/meal</span>
                                <span class="text-gray-600">{{ plan.menu_items_count }} menu items</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">{{ formatCurrency(plan.price_per_meal) }}</div>
                            <div class="text-sm text-gray-600">per meal</div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Form -->
                <form @submit.prevent="subscribe" class="space-y-6">
                    <!-- Duration Selection -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Pilih Durasi Langganan</h4>
                        <div class="grid gap-3">
                            <label
                                v-for="option in subscriptionOptions"
                                :key="option.duration"
                                class="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors hover:bg-gray-50"
                                :class="{ 'border-green-500 bg-green-50': form.duration === option.duration }"
                            >
                                <div class="flex items-center space-x-3">
                                    <input v-model="form.duration" :value="option.duration" type="radio" class="h-4 w-4 text-green-600" required />
                                    <div>
                                        <div class="font-medium">{{ option.label }}</div>
                                        <div class="text-sm text-gray-600">{{ option.description }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-green-600">
                                        {{ formatCurrency(calculateTotal(option.duration)) }}
                                    </div>
                                    <div v-if="option.discount > 0" class="text-sm text-green-600">Hemat {{ option.discount }}%</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Days -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Pilih Hari Pengiriman</h4>
                        <div class="grid grid-cols-7 gap-2">
                            <label
                                v-for="day in deliveryDays"
                                :key="day.value"
                                class="flex cursor-pointer flex-col items-center rounded-lg border p-3 text-center transition-colors hover:bg-gray-50"
                                :class="{ 'border-green-500 bg-green-50': form.delivery_days.includes(day.value) }"
                            >
                                <input v-model="form.delivery_days" :value="day.value" type="checkbox" class="mb-2 h-4 w-4 text-green-600" />
                                <span class="text-xs font-medium">{{ day.short }}</span>
                                <span class="text-xs text-gray-600">{{ day.label }}</span>
                            </label>
                        </div>
                        <p class="text-sm text-gray-600">Pilih minimal 1 hari pengiriman. Total: {{ form.delivery_days.length }} hari/minggu</p>
                    </div>

                    <!-- Delivery Schedule -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mulai Tanggal</label>
                            <Input v-model="form.start_date" type="date" :min="minDate" class="mt-1" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu Pengiriman</label>
                            <Select v-model="form.delivery_time" required>
                                <SelectTrigger class="mt-1">
                                    <SelectValue placeholder="Pilih waktu" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="morning">Pagi (08:00 - 12:00)</SelectItem>
                                    <SelectItem value="afternoon">Siang (12:00 - 17:00)</SelectItem>
                                    <SelectItem value="evening">Sore (17:00 - 20:00)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-gray-900">Alamat Pengiriman</h4>
                            <Button type="button" variant="outline" size="sm" @click="showAddAddressModal = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Tambah Alamat
                            </Button>
                        </div>

                        <div v-if="userAddresses.length === 0" class="rounded-lg border border-dashed border-gray-300 p-6 text-center">
                            <MapPin class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                            <p class="text-gray-600">Belum ada alamat tersimpan</p>
                            <Button type="button" variant="outline" size="sm" class="mt-2" @click="showAddAddressModal = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Tambah Alamat Pertama
                            </Button>
                        </div>

                        <Select v-else v-model="form.address_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih alamat pengiriman" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="address in userAddresses" :key="address.id" :value="address.id.toString()">
                                    <div class="text-left">
                                        <div class="font-medium">{{ address.label }}</div>
                                        <div class="text-sm text-gray-600">{{ address.recipient_name }}</div>
                                        <div class="text-sm text-gray-600">{{ address.full_address }}</div>
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Special Instructions -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Catatan Khusus (Opsional)</label>
                        <textarea
                            v-model="form.special_instructions"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none"
                            placeholder="Contoh: Alergi kacang, tidak suka pedas, dll."
                        ></textarea>
                    </div>

                    <!-- Order Summary -->
                    <div class="rounded-lg border bg-gray-50 p-4">
                        <h4 class="mb-3 font-semibold text-gray-900">Ringkasan Pesanan</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Plan:</span>
                                <span>{{ plan.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Durasi:</span>
                                <span>{{ getSelectedOptionLabel() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Hari pengiriman:</span>
                                <span>{{ form.delivery_days.length }} hari/minggu</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Harga per meal:</span>
                                <span>{{ formatCurrency(plan.price_per_meal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total meals:</span>
                                <span>{{ getTotalMeals() }} meals</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>{{ formatCurrency(getSubtotal()) }}</span>
                            </div>
                            <div v-if="getDiscount() > 0" class="flex justify-between text-green-600">
                                <span>Discount ({{ getDiscountPercentage() }}%):</span>
                                <span>-{{ formatCurrency(getDiscount()) }}</span>
                            </div>
                            <hr class="my-2" />
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total:</span>
                                <span class="text-green-600">{{ formatCurrency(calculateTotal(form.duration)) }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <DialogFooter class="flex justify-between space-x-2">
                <Button type="button" variant="outline" @click="$emit('close')"> Batal </Button>
                <Button @click="subscribe" :disabled="!isFormValid || isLoading" class="bg-green-600 hover:bg-green-700">
                    <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                    <CreditCard v-else class="mr-2 h-4 w-4" />
                    {{ isLoading ? 'Memproses...' : 'Subscribe Sekarang' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Add Address Modal -->
    <AddAddressModal :show="showAddAddressModal" @close="showAddAddressModal = false" @saved="handleAddressSaved" />
</template>

<script setup lang="ts">
import AddAddressModal from '@/components/User/AddAddressModal.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { Calendar, CreditCard, Loader2, MapPin, Plus } from 'lucide-vue-next';
import { computed, defineEmits, reactive, ref, watch } from 'vue';

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: number;
    plan_type: string;
    target_calories: number;
    image?: string;
    features: string[];
    menu_items_count: number;
    subscribers_count: number;
    is_active: boolean;
}

export interface UserAddress {
    id: number;
    label: string;
    recipient_name: string;
    phone_number: string;
    address_line_1: string;
    address_line_2?: string;
    full_address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    delivery_instructions?: string;
    address_type: 'residential' | 'commercial' | 'apartment';
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    show: boolean;
    plan: MealPlan | null;
    userAddresses: UserAddress[];
}>();

const emit = defineEmits<{
    close: [];
    subscribed: [];
}>();

// State
const isLoading = ref(false);
const showAddAddressModal = ref(false);

const form = reactive({
    duration: 'weekly',
    delivery_days: [] as string[],
    start_date: '',
    delivery_time: '',
    address_id: '',
    special_instructions: '',
});

// Subscription options
const subscriptionOptions = [
    {
        duration: 'weekly',
        label: '1 Minggu',
        description: '7 hari pengiriman',
        discount: 0,
        multiplier: 1,
    },
    {
        duration: 'biweekly',
        label: '2 Minggu',
        description: '14 hari pengiriman',
        discount: 5,
        multiplier: 2,
    },
    {
        duration: 'monthly',
        label: '1 Bulan',
        description: '4 minggu pengiriman',
        discount: 10,
        multiplier: 4,
    },
    {
        duration: 'quarterly',
        label: '3 Bulan',
        description: '12 minggu pengiriman',
        discount: 15,
        multiplier: 12,
    },
];

// Delivery days
const deliveryDays = [
    { value: 'monday', label: 'Senin', short: 'Sen' },
    { value: 'tuesday', label: 'Selasa', short: 'Sel' },
    { value: 'wednesday', label: 'Rabu', short: 'Rab' },
    { value: 'thursday', label: 'Kamis', short: 'Kam' },
    { value: 'friday', label: 'Jumat', short: 'Jum' },
    { value: 'saturday', label: 'Sabtu', short: 'Sab' },
    { value: 'sunday', label: 'Minggu', short: 'Min' },
];

// Computed
const minDate = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
});

const isFormValid = computed(() => {
    return form.duration && form.delivery_days.length > 0 && form.start_date && form.delivery_time && form.address_id;
});

// Methods
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getSelectedOption = () => {
    return subscriptionOptions.find((opt) => opt.duration === form.duration);
};

const getSelectedOptionLabel = () => {
    const option = getSelectedOption();
    return option ? option.label : '';
};

const getTotalMeals = () => {
    const option = getSelectedOption();
    if (!option) return 0;
    return form.delivery_days.length * option.multiplier;
};

const getSubtotal = () => {
    if (!props.plan) return 0;
    return props.plan.price_per_meal * getTotalMeals();
};

const getDiscountPercentage = () => {
    const option = getSelectedOption();
    return option ? option.discount : 0;
};

const getDiscount = () => {
    const subtotal = getSubtotal();
    const discountPercentage = getDiscountPercentage();
    return subtotal * (discountPercentage / 100);
};

const calculateTotal = (duration: string) => {
    const option = subscriptionOptions.find((opt) => opt.duration === duration);
    if (!option) return 0;
    const subtotal = getSubtotal();
    const discount = getDiscount();
    return subtotal - discount;
};

const subscribe = async () => {
    if (!props.plan || !isFormValid.value) return;

    isLoading.value = true;

    try {
        await router.post(route('user.subscriptions.store'), {
            meal_plan_id: props.plan.id,
            address_id: parseInt(form.address_id),
            duration: form.duration,
            delivery_days: form.delivery_days,
            delivery_time: form.delivery_time,
            start_date: form.start_date,
            special_instructions: form.special_instructions,
        });

        // Reset form
        Object.assign(form, {
            duration: 'weekly',
            delivery_days: [],
            start_date: '',
            delivery_time: '',
            address_id: '',
            special_instructions: '',
        });

        // Emit success event
        emit('subscribed');
    } catch (error) {
        console.error('Subscription failed:', error);
    } finally {
        isLoading.value = false;
    }
};

const handleAddressSaved = (address: any) => {
    showAddAddressModal.value = false;
    form.address_id = address.id.toString();
    router.reload({ only: ['userAddresses'] });
};

// Initialize form when modal opens
watch(
    () => props.show,
    (show) => {
        if (show && !form.start_date) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            form.start_date = tomorrow.toISOString().split('T')[0];

            // Set default address if available
            if (props.userAddresses.length > 0) {
                const defaultAddress = props.userAddresses.find((addr) => addr.is_default) || props.userAddresses[0];
                form.address_id = defaultAddress.id.toString();
            }
        }
    },
);
</script>
