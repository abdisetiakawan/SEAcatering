<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Edit Alamat' : 'Tambah Alamat Baru' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update informasi alamat pengiriman' : 'Tambahkan alamat pengiriman baru untuk memudahkan pemesanan' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="saveAddress" class="space-y-6">
                <!-- Address Type Selection -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-gray-700">Tipe Alamat</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div
                            v-for="type in addressTypes"
                            :key="type.value"
                            @click="setAddressType(type.value)"
                            class="relative cursor-pointer rounded-lg border p-4 text-center transition-colors hover:bg-gray-50"
                            :class="form.address_type === type.value ? 'border-green-500 bg-green-50' : 'border-gray-200'"
                        >
                            <div class="flex flex-col items-center space-y-2">
                                <div
                                    class="rounded-full p-2"
                                    :class="form.address_type === type.value ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'"
                                >
                                    <component :is="type.icon" class="h-5 w-5" />
                                </div>
                                <span class="text-sm font-medium">{{ type.label }}</span>
                            </div>
                            <div v-if="form.address_type === type.value" class="absolute -top-2 -right-2 rounded-full bg-green-500 p-1">
                                <Check class="h-3 w-3 text-white" />
                            </div>
                        </div>
                    </div>
                    <p v-if="errors.address_type" class="mt-1 text-sm text-red-600">{{ errors.address_type }}</p>
                </div>

                <!-- Address Label -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Label Alamat *</label>
                    <Input
                        v-model="form.label"
                        placeholder="Contoh: Rumah Utama, Kantor Pusat, Kost"
                        class="mt-1"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.label }"
                        required
                    />
                    <p v-if="errors.label" class="mt-1 text-sm text-red-600">{{ errors.label }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500">Berikan nama yang mudah diingat untuk alamat ini</p>
                </div>

                <!-- Recipient Information -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Penerima *</label>
                        <Input
                            v-model="form.recipient_name"
                            placeholder="Nama lengkap penerima"
                            class="mt-1"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.recipient_name }"
                            required
                        />
                        <p v-if="errors.recipient_name" class="mt-1 text-sm text-red-600">{{ errors.recipient_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon *</label>
                        <Input
                            v-model="form.phone_number"
                            placeholder="08xxxxxxxxxx"
                            class="mt-1"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.phone_number }"
                            required
                        />
                        <p v-if="errors.phone_number" class="mt-1 text-sm text-red-600">{{ errors.phone_number }}</p>
                    </div>
                </div>

                <!-- Address Details -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Alamat Lengkap *</label>
                    <textarea
                        v-model="form.address_line_1"
                        rows="3"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.address_line_1 }"
                        placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan"
                        required
                    ></textarea>
                    <p v-if="errors.address_line_1" class="mt-1 text-sm text-red-600">{{ errors.address_line_1 }}</p>
                </div>

                <!-- Additional Address Line -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Detail Tambahan (Opsional)</label>
                    <Input
                        v-model="form.address_line_2"
                        placeholder="Contoh: Blok A No. 15, Lantai 2"
                        class="mt-1"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.address_line_2 }"
                    />
                    <p v-if="errors.address_line_2" class="mt-1 text-sm text-red-600">{{ errors.address_line_2 }}</p>
                </div>

                <!-- Location Details -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kota *</label>
                        <Input
                            v-model="form.city"
                            placeholder="Nama kota"
                            class="mt-1"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.city }"
                            required
                        />
                        <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provinsi *</label>
                        <Select v-model="form.state">
                            <SelectTrigger class="mt-1" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.state }">
                                <SelectValue placeholder="Pilih provinsi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="province in provinces" :key="province" :value="province">
                                    {{ province }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.state" class="mt-1 text-sm text-red-600">{{ errors.state }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Pos *</label>
                        <Input
                            v-model="form.postal_code"
                            placeholder="12345"
                            class="mt-1"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.postal_code }"
                            required
                        />
                        <p v-if="errors.postal_code" class="mt-1 text-sm text-red-600">{{ errors.postal_code }}</p>
                    </div>
                </div>

                <!-- Country (Hidden, default to Indonesia) -->
                <input type="hidden" v-model="form.country" value="Indonesia" />

                <!-- Delivery Instructions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan Pengiriman (Opsional)</label>
                    <textarea
                        v-model="form.delivery_instructions"
                        rows="2"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.delivery_instructions }"
                        placeholder="Contoh: Rumah cat hijau, sebelah warung Pak Budi, tolong hubungi sebelum sampai"
                    ></textarea>
                    <p v-if="errors.delivery_instructions" class="mt-1 text-sm text-red-600">{{ errors.delivery_instructions }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500">Berikan petunjuk khusus untuk memudahkan kurir menemukan alamat</p>
                </div>

                <!-- Set as Default -->
                <div class="flex items-center space-x-2">
                    <input v-model="form.is_default" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                    <label class="text-sm text-gray-700">Jadikan sebagai alamat utama</label>
                    <p v-if="errors.is_default" class="ml-2 text-sm text-red-600">{{ errors.is_default }}</p>
                </div>

                <!-- General Error Message -->
                <div v-if="hasErrors" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada form</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>Silakan periksa kembali data yang Anda masukkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <DialogFooter class="flex justify-between space-x-2">
                <Button variant="outline" @click="$emit('close')" :disabled="isLoading"> Batal </Button>
                <Button @click="saveAddress" :disabled="!isFormValid || isLoading" class="bg-green-600 hover:bg-green-700">
                    <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                    <Save v-else class="mr-2 h-4 w-4" />
                    {{ isLoading ? 'Menyimpan...' : isEditing ? 'Update Alamat' : 'Simpan Alamat' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router, usePage } from '@inertiajs/vue3';
import { Building2, Check, Home, Landmark, Loader2, Save } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

interface UserAddress {
    id?: number;
    label: string;
    recipient_name: string;
    phone_number: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    delivery_instructions?: string;
    address_type: 'home' | 'office' | 'other';
    is_default: boolean;
}

const props = defineProps<{
    show: boolean;
    address?: UserAddress;
}>();

const emit = defineEmits<{
    close: [];
    saved: [address: UserAddress];
}>();

// Get page props for errors
const page = usePage();

// State
const isLoading = ref(false);
const isEditing = computed(() => !!props.address?.id);

const form = reactive<UserAddress>({
    label: '',
    recipient_name: '',
    phone_number: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'Indonesia',
    delivery_instructions: '',
    address_type: 'home',
    is_default: false,
});

// Address types
const addressTypes = [
    { value: 'home' as const, label: 'Rumah', icon: Home },
    { value: 'office' as const, label: 'Kantor', icon: Building2 },
    { value: 'other' as const, label: 'Lainnya', icon: Landmark },
];

// Indonesian provinces
const provinces = [
    'DKI Jakarta',
    'Jawa Barat',
    'Jawa Tengah',
    'Jawa Timur',
    'Banten',
    'Yogyakarta',
    'Bali',
    'Sumatera Utara',
    'Sumatera Barat',
    'Sumatera Selatan',
    'Riau',
    'Kepulauan Riau',
    'Jambi',
    'Bengkulu',
    'Lampung',
    'Bangka Belitung',
    'Kalimantan Barat',
    'Kalimantan Tengah',
    'Kalimantan Selatan',
    'Kalimantan Timur',
    'Kalimantan Utara',
    'Sulawesi Utara',
    'Sulawesi Tengah',
    'Sulawesi Selatan',
    'Sulawesi Tenggara',
    'Gorontalo',
    'Sulawesi Barat',
    'Maluku',
    'Maluku Utara',
    'Papua',
    'Papua Barat',
    'Papua Selatan',
    'Papua Tengah',
    'Papua Pegunungan',
    'Nusa Tenggara Barat',
    'Nusa Tenggara Timur',
    'Aceh',
];

// Computed properties for errors
const errors = computed(() => page.props.errors || {});
const hasErrors = computed(() => Object.keys(errors.value).length > 0);

const isFormValid = computed(() => {
    return form.label && form.recipient_name && form.phone_number && form.address_line_1 && form.city && form.state && form.postal_code;
});

// Methods
const saveAddress = async () => {
    if (!isFormValid.value) return;

    isLoading.value = true;

    try {
        if (isEditing.value) {
            router.put(route('user.addresses.update', props.address!.id), form, {
                onSuccess: () => {
                    emit('saved', form);
                    emit('close');
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors);
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            });
        } else {
            router.post(route('user.addresses.store'), form, {
                onSuccess: () => {
                    emit('saved', form);
                    emit('close');
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors);
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            });
        }
    } catch (error) {
        console.error('Failed to save address:', error);
        isLoading.value = false;
    }
};

const resetForm = () => {
    Object.assign(form, {
        label: '',
        recipient_name: '',
        phone_number: '',
        address_line_1: '',
        address_line_2: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'Indonesia',
        delivery_instructions: '',
        address_type: 'home',
        is_default: false,
    });
};

const setAddressType = (type: 'home' | 'office' | 'other') => {
    form.address_type = type;
};

// Watch for address changes
watch(
    () => props.address,
    (newAddress) => {
        if (newAddress) {
            Object.assign(form, newAddress);
        } else {
            resetForm();
        }
    },
    { immediate: true },
);

// Reset form when modal closes
watch(
    () => props.show,
    (show) => {
        if (!show && !isEditing.value) {
            resetForm();
        }
    },
);
</script>
