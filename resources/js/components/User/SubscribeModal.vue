<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-2xl">
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

                <!-- Subscription Options -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900">Pilih Paket Langganan</h4>
                    <div class="grid gap-3">
                        <label
                            v-for="option in subscriptionOptions"
                            :key="option.duration"
                            class="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors hover:bg-gray-50"
                            :class="{ 'border-green-500 bg-green-50': selectedOption === option.duration }"
                        >
                            <div class="flex items-center space-x-3">
                                <input v-model="selectedOption" :value="option.duration" type="radio" class="h-4 w-4 text-green-600" />
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
                            <Select v-model="subscriptionForm.delivery_time">
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
                    <Select v-model="subscriptionForm.address_id">
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
                    <Button variant="outline" size="sm" @click="showAddAddressModal = true">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Alamat Baru
                    </Button>
                </div>

                <!-- Special Instructions -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Catatan Khusus (Opsional)</label>
                    <textarea
                        v-model="subscriptionForm.special_instructions"
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
                            <span class="text-green-600">{{ formatCurrency(calculateTotal(selectedOption)) }}</span>
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
import { Calendar, CreditCard, Loader2, Plus } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

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

interface UserAddress {
    id: number;
    label: string;
    recipient_name: string;
    phone: string;
    full_address: string;
    city: string;
    postal_code: string;
    province: string;
    notes?: string;
    is_default: boolean;
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
const selectedOption = ref('weekly');

const subscriptionForm = reactive({
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
        description: '7 meals',
        discount: 0,
        meals: 7,
    },
    {
        duration: 'biweekly',
        label: '2 Minggu',
        description: '14 meals',
        discount: 5,
        meals: 14,
    },
    {
        duration: 'monthly',
        label: '1 Bulan',
        description: '30 meals',
        discount: 10,
        meals: 30,
    },
    {
        duration: 'quarterly',
        label: '3 Bulan',
        description: '90 meals',
        discount: 15,
        meals: 90,
    },
];

// Computed
const minDate = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
});

const isFormValid = computed(() => {
    return subscriptionForm.start_date && subscriptionForm.delivery_time && subscriptionForm.address_id && selectedOption.value;
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

const calculateTotal = (duration: string) => {
    if (!props.plan) return 0;

    const option = subscriptionOptions.find((opt) => opt.duration === duration);
    if (!option) return 0;

    const subtotal = props.plan.price_per_meal * option.meals;
    const discount = subtotal * (option.discount / 100);
    return subtotal - discount;
};

const getSelectedOptionLabel = () => {
    const option = subscriptionOptions.find((opt) => opt.duration === selectedOption.value);
    return option ? option.label : '';
};

const getTotalMeals = () => {
    const option = subscriptionOptions.find((opt) => opt.duration === selectedOption.value);
    return option ? option.meals : 0;
};

const getDiscount = () => {
    if (!props.plan) return 0;

    const option = subscriptionOptions.find((opt) => opt.duration === selectedOption.value);
    if (!option) return 0;

    const subtotal = props.plan.price_per_meal * option.meals;
    return subtotal * (option.discount / 100);
};

const subscribe = async () => {
    if (!props.plan || !isFormValid.value) return;

    isLoading.value = true;

    try {
        await router.post(route('user.subscriptions.store'), {
            meal_plan_id: props.plan.id,
            duration: selectedOption.value,
            start_date: subscriptionForm.start_date,
            delivery_time: subscriptionForm.delivery_time,
            address_id: subscriptionForm.address_id,
            special_instructions: subscriptionForm.special_instructions,
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
    subscriptionForm.address_id = address.id.toString();
    // Refresh addresses list
    router.reload({ only: ['userAddresses'] });
};

// Initialize form
if (props.show && !subscriptionForm.start_date) {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    subscriptionForm.start_date = tomorrow.toISOString().split('T')[0];
}
</script>
