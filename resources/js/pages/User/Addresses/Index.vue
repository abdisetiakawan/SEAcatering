<template>
    <UserLayout>
        <Head title="Alamat Saya" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Alamat Saya</h1>
                                <p class="mt-1 text-gray-600">Kelola alamat pengiriman untuk pesanan Anda</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total Alamat</p>
                                    <p class="text-lg font-semibold text-green-600">{{ addresses.length }}</p>
                                </div>
                                <Button @click="openAddModal" class="bg-green-600 hover:bg-green-700">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Tambah Alamat
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-green-100 p-3">
                                <Home class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Alamat Rumah</p>
                                <p class="text-2xl font-bold text-green-600">{{ homeAddresses }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-blue-100 p-3">
                                <Building class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Alamat Kantor</p>
                                <p class="text-2xl font-bold text-blue-600">{{ officeAddresses }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-purple-100 p-3">
                                <MapPin class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Alamat Lainnya</p>
                                <p class="text-2xl font-bold text-purple-600">{{ otherAddresses }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="searchQuery" placeholder="Cari alamat..." class="pl-10" />
                            </div>
                            <Select v-model="filterType">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="residential">Rumah</SelectItem>
                                    <SelectItem value="office">Kantor</SelectItem>
                                    <SelectItem value="apartment">Apartemen</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Addresses Grid -->
                <div v-if="filteredAddresses.length > 0" class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <AddressCard
                        v-for="address in filteredAddresses"
                        :key="address.id"
                        :address="address"
                        @edit="openEditModal"
                        @delete="confirmDelete"
                        @set-default="setAsDefault"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <MapPin class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">
                        {{ searchQuery || filterType ? 'Alamat tidak ditemukan' : 'Belum ada alamat tersimpan' }}
                    </h3>
                    <p class="mb-6 text-gray-500">
                        {{ searchQuery || filterType ? 'Coba ubah filter pencarian Anda' : 'Tambahkan alamat pertama untuk memudahkan pengiriman' }}
                    </p>
                    <Button v-if="!searchQuery && filterType !== 'all'" @click="openAddModal" class="bg-green-600 hover:bg-green-700">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Alamat Pertama
                    </Button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Address Modal -->
        <AddAddressModal :show="showAddressModal" :address="selectedAddress" @close="closeAddressModal" @saved="handleAddressSaved" />

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Hapus Alamat"
            :message="`Apakah Anda yakin ingin menghapus alamat '${addressToDelete?.label}'? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="deleteAddress"
            @cancel="showDeleteModal = false"
        />

        <!-- Success Modal -->
        <SuccessModal :show="showSuccessModal" :title="successTitle" :message="successMessage" @close="showSuccessModal = false" />
    </UserLayout>
</template>

<script setup lang="ts">
import AddAddressModal from '@/components/User/AddAddressModal.vue';
import AddressCard from '@/components/User/AddressCard.vue';
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Building, Home, MapPin, Plus, RefreshCw, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface UserAddress {
    id: number;
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
    address_type: 'residential' | 'commercial' | 'apartment';
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    addresses: UserAddress[];
}>();

// State
const showAddressModal = ref(false);
const showDeleteModal = ref(false);
const showSuccessModal = ref(false);
const selectedAddress = ref<UserAddress | undefined>(undefined);
const addressToDelete = ref<UserAddress | undefined>(undefined);
const searchQuery = ref('');
const filterType = ref('all');
const successTitle = ref('');
const successMessage = ref('');

// Computed
const homeAddresses = computed(() => props.addresses.filter((addr) => addr.address_type === 'residential').length);

const officeAddresses = computed(() => props.addresses.filter((addr) => addr.address_type === 'commercial').length);

const otherAddresses = computed(() => props.addresses.filter((addr) => addr.address_type === 'apartment').length);

const filteredAddresses = computed(() => {
    let filtered = props.addresses;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (address) =>
                address.label.toLowerCase().includes(query) ||
                address.recipient_name.toLowerCase().includes(query) ||
                address.address_line_1.toLowerCase().includes(query) ||
                address.city.toLowerCase().includes(query),
        );
    }

    // Filter by type
    if (filterType.value !== 'all') {
        filtered = filtered.filter((address) => address.address_type === filterType.value);
    }

    // Sort by default first, then by created date
    return filtered.sort((a, b) => {
        if (a.is_default && !b.is_default) return -1;
        if (!a.is_default && b.is_default) return 1;
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });
});

// Methods
const openAddModal = () => {
    selectedAddress.value = undefined;
    showAddressModal.value = true;
};

const openEditModal = (address: UserAddress) => {
    selectedAddress.value = address;
    showAddressModal.value = true;
};

const closeAddressModal = () => {
    showAddressModal.value = false;
    selectedAddress.value = undefined;
};

const confirmDelete = (address: UserAddress) => {
    addressToDelete.value = address;
    showDeleteModal.value = true;
};

const deleteAddress = () => {
    if (!addressToDelete.value) return;

    router.delete(route('user.addresses.destroy', addressToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            addressToDelete.value = undefined;
            showSuccess('Alamat Dihapus', 'Alamat berhasil dihapus dari daftar Anda.');
        },
        onError: () => {
            showDeleteModal.value = false;
            addressToDelete.value = undefined;
        },
    });
};

const setAsDefault = (address: UserAddress) => {
    router.patch(
        route('user.addresses.set-default', address.id),
        {},
        {
            onSuccess: () => {
                showSuccess('Alamat Utama Diperbarui', `${address.label} telah dijadikan alamat utama.`);
            },
        },
    );
};

const handleAddressSaved = () => {
    closeAddressModal();
    const isEditing = !!selectedAddress.value;
    showSuccess(
        isEditing ? 'Alamat Diperbarui' : 'Alamat Ditambahkan',
        isEditing ? 'Alamat berhasil diperbarui.' : 'Alamat baru berhasil ditambahkan.',
    );
};

const resetFilters = () => {
    searchQuery.value = '';
    filterType.value = 'all';
};

const showSuccess = (title: string, message: string) => {
    successTitle.value = title;
    successMessage.value = message;
    showSuccessModal.value = true;
};
</script>
