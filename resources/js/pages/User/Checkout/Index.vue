<template>
    <UserLayout>
        <Head title="Checkout" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
                                <p class="mt-1 text-gray-600">Selesaikan pesanan Anda</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ cartItems.length }} item</p>
                                <p class="text-lg font-semibold text-green-600">{{ formatCurrency(finalTotal) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Checkout Form -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Delivery Address -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Alamat Pengiriman</h2>
                            </div>
                            <div class="p-6">
                                <div v-if="addresses.length === 0" class="py-8 text-center">
                                    <MapPin class="mx-auto h-12 w-12 text-gray-300" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada alamat</h3>
                                    <p class="mt-1 text-sm text-gray-500">Tambahkan alamat pengiriman terlebih dahulu</p>
                                    <div class="mt-6">
                                        <Link
                                            :href="route('user.addresses.index')"
                                            class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                                        >
                                            <Plus class="mr-2 h-4 w-4" />
                                            Tambah Alamat
                                        </Link>
                                    </div>
                                </div>
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="address in addresses"
                                        :key="address.id"
                                        class="relative cursor-pointer rounded-lg border p-4 transition-colors"
                                        :class="
                                            form.delivery_address_id === address.id
                                                ? 'border-green-500 bg-green-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                        "
                                        @click="form.delivery_address_id = address.id"
                                    >
                                        <div class="flex items-start">
                                            <input
                                                type="radio"
                                                :value="address.id"
                                                v-model="form.delivery_address_id"
                                                class="mt-1 h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500"
                                            />
                                            <div class="ml-3 flex-1">
                                                <div class="flex items-center">
                                                    <h3 class="text-sm font-medium text-gray-900">{{ address.label }}</h3>
                                                    <Badge v-if="address.is_default" variant="secondary" class="ml-2">Default</Badge>
                                                </div>
                                                <p class="text-sm text-gray-600">{{ address.recipient_name }} • {{ address.phone_number }}</p>
                                                <p class="text-sm text-gray-500">{{ address.full_address }}</p>
                                                <p v-if="address.delivery_instructions" class="mt-1 text-xs text-gray-400">
                                                    {{ address.delivery_instructions }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <InputError :message="form.errors.delivery_address_id" class="mt-2" />
                            </div>
                        </div>

                        <!-- Delivery Schedule -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Jadwal Pengiriman</h2>
                            </div>
                            <div class="space-y-4 p-6">
                                <!-- Delivery Date -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Tanggal Pengiriman</label>
                                    <Input type="date" v-model="form.delivery_date" :min="minDeliveryDate" :max="maxDeliveryDate" class="w-full" />
                                    <InputError :message="form.errors.delivery_date" class="mt-1" />
                                </div>

                                <!-- Delivery Time -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Waktu Pengiriman</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div
                                            v-for="(label, value) in timeSlots"
                                            :key="value"
                                            class="relative cursor-pointer rounded-lg border p-3 transition-colors"
                                            :class="
                                                form.delivery_time_slot === value
                                                    ? 'border-green-500 bg-green-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            "
                                            @click="form.delivery_time_slot = value"
                                        >
                                            <div class="flex items-center">
                                                <input
                                                    type="radio"
                                                    :value="value"
                                                    v-model="form.delivery_time_slot"
                                                    class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500"
                                                />
                                                <label class="ml-2 cursor-pointer text-sm font-medium text-gray-900">
                                                    {{ label }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.delivery_time_slot" class="mt-1" />
                                </div>

                                <!-- Special Instructions -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Catatan Khusus (Opsional)</label>
                                    <textarea
                                        v-model="form.special_instructions"
                                        rows="3"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                        placeholder="Contoh: Tolong antarkan ke security, rumah pagar hijau, dll."
                                    ></textarea>
                                    <InputError :message="form.errors.special_instructions" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Metode Pembayaran</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div
                                        v-for="(method, key) in paymentMethods"
                                        :key="key"
                                        class="relative cursor-pointer rounded-lg border p-4 transition-colors"
                                        :class="
                                            form.payment_method === key ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'
                                        "
                                        @click="form.payment_method = key"
                                    >
                                        <div class="flex items-start">
                                            <input
                                                type="radio"
                                                :value="key"
                                                v-model="form.payment_method"
                                                class="mt-1 h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500"
                                            />
                                            <div class="ml-3 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <component :is="getPaymentIcon(method.icon)" class="mr-2 h-5 w-5 text-gray-600" />
                                                        <h3 class="text-sm font-medium text-gray-900">{{ method.name }}</h3>
                                                    </div>
                                                    <span v-if="method.fee > 0" class="text-xs text-gray-500">
                                                        +{{ formatCurrency(method.fee) }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500">{{ method.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <InputError :message="form.errors.payment_method" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="space-y-6">
                        <!-- Cart Items -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Pesanan Anda</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div v-for="item in cartItems" :key="item.id" class="flex items-center space-x-4">
                                        <img
                                            :src="item.menu_item.image.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                                            :alt="item.menu_item.name"
                                            class="h-15 w-15 rounded-lg object-cover"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900">{{ item.menu_item.name }}</h3>
                                            <p class="text-sm text-gray-500">{{ item.menu_item.category }}</p>
                                            <div class="mt-1 flex items-center">
                                                <span class="text-xs text-gray-400">{{ item.quantity }}x</span>
                                                <span class="ml-2 text-sm font-medium text-gray-900">{{ formatCurrency(item.price) }}</span>
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ formatCurrency(item.subtotal) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Summary -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Pembayaran</h2>
                            </div>
                            <div class="space-y-3 p-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium">{{ formatCurrency(summary.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Biaya Pengiriman</span>
                                    <span class="font-medium" :class="summary.delivery_fee === 0 ? 'text-green-600' : ''">
                                        {{ summary.delivery_fee === 0 ? 'GRATIS' : formatCurrency(summary.delivery_fee) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Pajak (11%)</span>
                                    <span class="font-medium">{{ formatCurrency(summary.tax_amount) }}</span>
                                </div>
                                <div v-if="paymentFee > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-600">Biaya Pembayaran</span>
                                    <span class="font-medium">{{ formatCurrency(paymentFee) }}</span>
                                </div>
                                <div v-if="summary.delivery_fee === 0" class="rounded bg-green-50 p-2 text-xs text-green-600">
                                    🎉 Selamat! Anda mendapat gratis ongkir untuk pembelian di atas
                                    {{ formatCurrency(summary.free_delivery_threshold) }}
                                </div>
                                <div class="border-t pt-3">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total</span>
                                        <span class="text-green-600">{{ formatCurrency(finalTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <Button
                            @click="submitOrder"
                            :disabled="!canPlaceOrder || form.processing"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-50"
                            size="lg"
                        >
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <CreditCard v-else class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Memproses...' : 'Buat Pesanan' }}
                        </Button>

                        <p class="text-center text-xs text-gray-500">Dengan melanjutkan, Anda menyetujui syarat dan ketentuan kami</p>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Banknote, Building2, CreditCard, Loader2, MapPin, Plus, Smartphone } from 'lucide-vue-next';
import { computed } from 'vue';

interface CartItem {
    id: number;
    menu_item: {
        id: number;
        name: string;
        image: string;
        category: string;
        calories: number;
        protein: number;
    };
    quantity: number;
    price: number;
    subtotal: number;
}

interface Address {
    id: number;
    label: string;
    recipient_name: string;
    phone_number: string;
    full_address: string;
    delivery_instructions?: string;
    is_default: boolean;
}

interface Summary {
    subtotal: number;
    delivery_fee: number;
    tax_amount: number;
    total_amount: number;
    free_delivery_threshold: number;
}

interface PaymentMethod {
    name: string;
    description: string;
    icon: string;
    fee: number;
}

const props = defineProps<{
    cartItems: CartItem[];
    addresses: Address[];
    summary: Summary;
    paymentMethods: Record<string, PaymentMethod>;
    timeSlots: Record<string, string>;
    minDeliveryDate: string;
    maxDeliveryDate: string;
}>();

const form = useForm({
    delivery_address_id: props.addresses.find((addr) => addr.is_default)?.id || props.addresses[0]?.id || null,
    delivery_date: props.minDeliveryDate,
    delivery_time_slot: '',
    payment_method: '',
    special_instructions: '',
});

const paymentFee = computed(() => {
    if (!form.payment_method || !props.paymentMethods[form.payment_method]) {
        return 0;
    }
    return props.paymentMethods[form.payment_method].fee;
});

const finalTotal = computed(() => {
    return props.summary.total_amount + paymentFee.value;
});

const canPlaceOrder = computed(() => {
    return form.delivery_address_id && form.delivery_date && form.delivery_time_slot && form.payment_method && props.cartItems.length > 0;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getPaymentIcon = (iconName: string) => {
    const icons = {
        'building-2': Building2,
        smartphone: Smartphone,
        'credit-card': CreditCard,
        banknote: Banknote,
    };
    return icons[iconName as keyof typeof icons] || CreditCard;
};

const submitOrder = () => {
    if (!canPlaceOrder.value) return;

    form.post(route('user.checkout.store'), {
        onSuccess: () => {
            // Will be redirected to order details page
        },
        onError: (errors) => {
            console.error('Checkout errors:', errors);
        },
    });
};
</script>
