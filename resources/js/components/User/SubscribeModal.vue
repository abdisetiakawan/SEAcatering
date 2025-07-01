<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
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
                                <span class="text-gray-600">{{ plan.menu_items_count || 0 }} menu items</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">{{ formatCurrency(plan.price_per_meal) }}</div>
                            <div class="text-sm text-gray-600">per meal</div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Options -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Pilih Durasi Langganan</h4>
                    <div class="grid gap-3">
                        <label
                            v-for="option in subscriptionOptions"
                            :key="option.duration"
                            class="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors hover:bg-gray-50"
                            :class="{
                                'border-green-500 bg-green-50': selectedDuration === option.duration,
                                'border-gray-200': selectedDuration !== option.duration,
                            }"
                        >
                            <div class="flex items-center space-x-3">
                                <input v-model="selectedDuration" :value="option.duration" type="radio" class="h-4 w-4 text-green-600" />
                                <div>
                                    <div class="font-medium">{{ option.label }}</div>
                                    <div class="text-sm text-gray-600">{{ option.description }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-green-600">
                                    {{ formatCurrency(calculateTotal(option.duration)) }}
                                </div>
                                <div v-if="option.discount > 0" class="text-sm text-green-600">Save {{ option.discount }}%</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Frequency Selection -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Frekuensi Pengiriman</h4>
                    <div class="grid grid-cols-3 gap-3">
                        <label
                            v-for="freq in frequencyOptions"
                            :key="freq.value"
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-3 transition-colors hover:bg-gray-50"
                            :class="{
                                'border-green-500 bg-green-50': subscriptionForm.frequency === freq.value,
                                'border-gray-200': subscriptionForm.frequency !== freq.value,
                            }"
                        >
                            <input v-model="subscriptionForm.frequency" :value="freq.value" type="radio" class="sr-only" />
                            <div class="text-center">
                                <div class="font-medium">{{ freq.label }}</div>
                                <div class="text-sm text-gray-600">{{ freq.description }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Delivery Days Selection -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Hari Pengiriman</h4>
                    <div class="grid grid-cols-7 gap-2">
                        <label
                            v-for="day in dayOptions"
                            :key="day.value"
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-2 transition-colors hover:bg-gray-50"
                            :class="{
                                'border-green-500 bg-green-50': subscriptionForm.delivery_days.includes(day.value),
                                'border-gray-200': !subscriptionForm.delivery_days.includes(day.value),
                            }"
                        >
                            <input :value="day.value" type="checkbox" class="sr-only" @change="toggleDeliveryDay(day.value)" />
                            <div class="text-center">
                                <div class="text-xs font-medium">{{ day.short }}</div>
                                <div class="text-xs text-gray-600">{{ day.label }}</div>
                            </div>
                        </label>
                    </div>
                    <p class="text-sm text-gray-600">Pilih minimal 1 hari untuk pengiriman</p>
                </div>

                <!-- Meals per Day -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Jumlah Makanan per Hari</h4>
                    <div class="grid grid-cols-5 gap-3">
                        <label
                            v-for="count in [1, 2, 3, 4, 5]"
                            :key="count"
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-3 transition-colors hover:bg-gray-50"
                            :class="{
                                'border-green-500 bg-green-50': subscriptionForm.meals_per_day === count,
                                'border-gray-200': subscriptionForm.meals_per_day !== count,
                            }"
                        >
                            <input v-model.number="subscriptionForm.meals_per_day" :value="count" type="radio" class="sr-only" />
                            <div class="text-center">
                                <div class="font-medium">{{ count }}</div>
                                <div class="text-xs text-gray-600">meal{{ count > 1 ? 's' : '' }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Delivery Schedule -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Jadwal Pengiriman</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mulai Tanggal</label>
                            <Input v-model="subscriptionForm.start_date" type="date" :min="minDate" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu Pengiriman</label>
                            <Select v-model="subscriptionForm.delivery_time_preference">
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
                </div>

                <!-- Delivery Address -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Alamat Pengiriman</h4>

                    <!-- Debug info -->
                    <div v-if="$page.props.app?.debug" class="rounded bg-gray-100 p-2 text-xs text-gray-500">
                        Debug: {{ userAddresses?.length || 0 }} addresses found
                        <pre>{{ JSON.stringify(userAddresses, null, 2) }}</pre>
                    </div>

                    <div v-if="userAddresses && userAddresses.length > 0">
                        <Select v-model="subscriptionForm.user_address_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih alamat pengiriman" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="address in userAddresses" :key="address.id" :value="address.id.toString()">
                                    <div class="text-left">
                                        <div class="font-medium">{{ address.label }}</div>
                                        <div class="text-sm text-gray-600">{{ address.full_address }}</div>
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                        <div class="flex items-center">
                            <AlertTriangle class="mr-2 h-5 w-5 text-yellow-600" />
                            <div>
                                <h5 class="font-medium text-yellow-800">No Address Found</h5>
                                <p class="text-sm text-yellow-700">You need to add a delivery address first.</p>
                            </div>
                        </div>
                    </div>

                    <Button variant="outline" size="sm" @click="showAddAddressModal = true">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Alamat Baru
                    </Button>
                </div>

                <!-- Payment Method Selection -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Metode Pembayaran</h4>
                    <div class="grid gap-3">
                        <label
                            v-for="(label, method) in paymentMethods"
                            :key="method"
                            class="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors hover:bg-gray-50"
                            :class="{
                                'border-green-500 bg-green-50': subscriptionForm.payment_method === method,
                                'border-gray-200': subscriptionForm.payment_method !== method,
                            }"
                        >
                            <div class="flex items-center space-x-3">
                                <input v-model="subscriptionForm.payment_method" :value="method" type="radio" class="sr-only" />
                                <div class="flex items-center space-x-3">
                                    <component :is="getPaymentIcon(method)" class="h-5 w-5 text-gray-600" />
                                    <div>
                                        <div class="font-medium">{{ label }}</div>
                                        <div class="text-sm text-gray-600">
                                            <span v-if="method === 'bank_transfer'">Transfer via virtual account</span>
                                            <span v-else-if="method === 'credit_card'">Pay with credit/debit card</span>
                                            <span v-else-if="method === 'e_wallet'">Pay with GoPay, OVO, DANA</span>
                                            <span v-else-if="method === 'cash'">Pay when order is delivered</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <div
                                    v-if="subscriptionForm.payment_method === method"
                                    class="flex h-4 w-4 items-center justify-center rounded-full bg-green-500"
                                >
                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                </div>
                                <div v-else class="h-4 w-4 rounded-full border-2 border-gray-300"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Special Requirements -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Catatan Khusus (Opsional)</label>
                    <textarea
                        v-model="subscriptionForm.special_requirements"
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
                            <span>{{ getSelectedDurationLabel() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Frekuensi:</span>
                            <span>{{ getSelectedFrequencyLabel() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Makanan per hari:</span>
                            <span>{{ subscriptionForm.meals_per_day }} meals</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Hari pengiriman:</span>
                            <span>{{ subscriptionForm.delivery_days.length }} hari/minggu</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Harga per meal:</span>
                            <span>{{ formatCurrency(plan.price_per_meal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total meals:</span>
                            <span>{{ getTotalMeals() }} meals</span>
                        </div>
                        <div v-if="getDiscount() > 0" class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-{{ formatCurrency(getDiscount()) }}</span>
                        </div>
                        <hr class="my-2" />
                        <div class="flex justify-between font-semibold">
                            <span>Total:</span>
                            <span class="text-green-600">{{ formatCurrency(calculateTotal(selectedDuration)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex justify-between space-x-2">
                <Button variant="outline" @click="$emit('close')"> Batal </Button>
                <Button @click="subscribe" :disabled="!isFormValid || isLoading" class="bg-green-600 hover:bg-green-700">
                    <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                    <CreditCard v-else class="mr-2 h-4 w-4" />
                    {{ isLoading ? 'Processing...' : 'Subscribe Now' }}
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
import { AlertTriangle, Banknote, Building2, Calendar, CreditCard, Loader2, Plus, Smartphone } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: number;
    plan_type: string;
    target_calories: number;
    image?: string;
    features: string[];
    menu_items_count?: number;
    subscribers_count: number;
    is_active: boolean;
}

interface UserAddress {
    id: number;
    label: string;
    recipient_name: string;
    phone: string;
    full_address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    is_default: boolean;
    address_type: string;
    delivery_instructions?: string;
}

const props = defineProps<{
    show: boolean;
    plan: MealPlan | null;
    userAddresses?: UserAddress[];
}>();

const emit = defineEmits<{
    close: [];
    subscribed: [];
}>();

// State
const isLoading = ref(false);
const showAddAddressModal = ref(false);
const selectedDuration = ref('monthly');

const subscriptionForm = reactive({
    start_date: '',
    frequency: 'weekly',
    delivery_days: [] as string[],
    delivery_time_preference: '',
    user_address_id: '',
    meals_per_day: 1,
    payment_method: '',
    special_requirements: '',
});

// Watch for props changes
watch(
    () => props.show,
    (newValue) => {
        if (newValue && !subscriptionForm.start_date) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            subscriptionForm.start_date = tomorrow.toISOString().split('T')[0];
        }

        // Debug log
        console.log('Modal opened, userAddresses:', props.userAddresses);
    },
);

// Subscription duration options
const subscriptionOptions = [
    {
        duration: 'weekly',
        label: '1 Minggu',
        description: '7 hari',
        discount: 0,
        weeks: 1,
    },
    {
        duration: 'monthly',
        label: '1 Bulan',
        description: '4 minggu',
        discount: 5,
        weeks: 4,
    },
    {
        duration: 'quarterly',
        label: '3 Bulan',
        description: '12 minggu',
        discount: 10,
        weeks: 12,
    },
    {
        duration: 'yearly',
        label: '1 Tahun',
        description: '52 minggu',
        discount: 15,
        weeks: 52,
    },
];

// Frequency options
const frequencyOptions = [
    {
        value: 'daily',
        label: 'Harian',
        description: 'Setiap hari',
    },
    {
        value: 'weekly',
        label: 'Mingguan',
        description: 'Per minggu',
    },
    {
        value: 'monthly',
        label: 'Bulanan',
        description: 'Per bulan',
    },
];

// Day options
const dayOptions = [
    { value: 'monday', label: 'Senin', short: 'Sen' },
    { value: 'tuesday', label: 'Selasa', short: 'Sel' },
    { value: 'wednesday', label: 'Rabu', short: 'Rab' },
    { value: 'thursday', label: 'Kamis', short: 'Kam' },
    { value: 'friday', label: 'Jumat', short: 'Jum' },
    { value: 'saturday', label: 'Sabtu', short: 'Sab' },
    { value: 'sunday', label: 'Minggu', short: 'Min' },
];

// Payment methods
const paymentMethods = {
    credit_card: 'Credit Card',
    bank_transfer: 'Bank Transfer',
    e_wallet: 'E-Wallet',
    cash: 'Cash on Delivery',
};

// Computed
const minDate = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
});

const isFormValid = computed(() => {
    return (
        subscriptionForm.start_date &&
        subscriptionForm.frequency &&
        subscriptionForm.delivery_days.length > 0 &&
        subscriptionForm.delivery_time_preference &&
        subscriptionForm.user_address_id &&
        subscriptionForm.meals_per_day > 0 &&
        subscriptionForm.payment_method &&
        selectedDuration.value
    );
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

const getPaymentIcon = (method: string) => {
    const icons = {
        credit_card: CreditCard,
        bank_transfer: Building2,
        e_wallet: Smartphone,
        cash_on_delivery: Banknote,
    };
    return icons[method as keyof typeof icons] || CreditCard;
};

const toggleDeliveryDay = (day: string) => {
    const index = subscriptionForm.delivery_days.indexOf(day);
    if (index > -1) {
        subscriptionForm.delivery_days.splice(index, 1);
    } else {
        subscriptionForm.delivery_days.push(day);
    }
};

const calculateTotal = (duration: string) => {
    if (!props.plan) return 0;

    const option = subscriptionOptions.find((opt) => opt.duration === duration);
    if (!option) return 0;

    const mealsPerWeek = subscriptionForm.delivery_days.length * subscriptionForm.meals_per_day;
    const totalWeeks = option.weeks;
    const totalMeals = mealsPerWeek * totalWeeks;
    const subtotal = props.plan.price_per_meal * totalMeals;
    const discount = subtotal * (option.discount / 100);

    return subtotal - discount;
};

const getSelectedDurationLabel = () => {
    const option = subscriptionOptions.find((opt) => opt.duration === selectedDuration.value);
    return option ? option.label : '';
};

const getSelectedFrequencyLabel = () => {
    const freq = frequencyOptions.find((f) => f.value === subscriptionForm.frequency);
    return freq ? freq.label : '';
};

const getTotalMeals = () => {
    const option = subscriptionOptions.find((opt) => opt.duration === selectedDuration.value);
    if (!option) return 0;

    const mealsPerWeek = subscriptionForm.delivery_days.length * subscriptionForm.meals_per_day;
    return mealsPerWeek * option.weeks;
};

const getDiscount = () => {
    if (!props.plan) return 0;

    const option = subscriptionOptions.find((opt) => opt.duration === selectedDuration.value);
    if (!option) return 0;

    const mealsPerWeek = subscriptionForm.delivery_days.length * subscriptionForm.meals_per_day;
    const totalWeeks = option.weeks;
    const totalMeals = mealsPerWeek * totalWeeks;
    const subtotal = props.plan.price_per_meal * totalMeals;

    return subtotal * (option.discount / 100);
};

const subscribe = async () => {
    if (!props.plan || !isFormValid.value) return;

    isLoading.value = true;

    try {
        const option = subscriptionOptions.find((opt) => opt.duration === selectedDuration.value);
        if (!option) throw new Error('Invalid duration selected');

        await router.post(route('user.subscriptions.store'), {
            meal_plan_id: props.plan.id,
            duration: selectedDuration.value,
            duration_weeks: option.weeks,
            start_date: subscriptionForm.start_date,
            frequency: subscriptionForm.frequency,
            delivery_days: subscriptionForm.delivery_days,
            delivery_time_preference: subscriptionForm.delivery_time_preference,
            user_address_id: parseInt(subscriptionForm.user_address_id),
            meals_per_day: subscriptionForm.meals_per_day,
            payment_method: subscriptionForm.payment_method,
            special_requirements: subscriptionForm.special_requirements,
        });

        // Emit success event
        emit('subscribed');
    } catch (error) {
        console.error('Subscription failed:', error);
    } finally {
        isLoading.value = false;
    }
};

const handleAddressSaved = (address: UserAddress) => {
    showAddAddressModal.value = false;
    subscriptionForm.user_address_id = address.id.toString();
    // Refresh the page to get updated addresses
    router.reload({ only: ['userAddresses'] });
};
</script>
