<template>
    <AdminLayout>
        <Head title="Menu Management" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Menu Management</h1>
                                <p class="mt-1 text-gray-600">Kelola menu makanan dan minuman</p>
                            </div>
                            <Button @click="openCreateModal" class="bg-green-600 hover:bg-green-700">
                                <Plus class="mr-2 h-4 w-4" />
                                Tambah Menu Baru
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Cari menu..." class="pl-10" @input="handleSearch" />
                            </div>

                            <!-- Filter Kategori -->
                            <Select v-model="filters.category">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="all">Semua Kategori</SelectItem>
                                        <SelectItem value="diet">Diet Plan</SelectItem>
                                        <SelectItem value="protein">Protein Plan</SelectItem>
                                        <SelectItem value="royal">Royal Plan</SelectItem>
                                        <SelectItem value="vegetarian">Vegetarian</SelectItem>
                                        <SelectItem value="seafood">Seafood</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <!-- Filter Status -->
                            <Select v-model="filters.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="all">Semua Status</SelectItem>
                                        <SelectItem value="active">Aktif</SelectItem>
                                        <SelectItem value="inactive">Tidak Aktif</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <!-- Reset Button -->
                            <Button variant="outline" class="w-full" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Grid -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <MenuItemCard
                        v-for="item in menuItems.data"
                        :key="item.id"
                        :item="item"
                        @edit="(item: any) => openEditModal(item)"
                        @delete="(item: any) => confirmDelete(item)"
                        @toggle-status="(item: any) => toggleItemStatus(item)"
                    />
                </div>

                <!-- Empty State -->
                <div v-if="menuItems.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <Utensils class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">Belum ada menu</h3>
                    <p class="mb-6 text-gray-500">Mulai dengan menambahkan menu pertama Anda</p>
                    <Button @click="openCreateModal" class="bg-green-600 hover:bg-green-700">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Menu Baru
                    </Button>
                </div>

                <!-- Pagination -->
                <div v-if="menuItems.data.length > 0" class="rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="menuItems.links" />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <MenuFormModal :show="showModal" :item="selectedItem" :is-editing="isEditing" @close="closeModal" @saved="handleSaved" />

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Hapus Menu"
            message="Apakah Anda yakin ingin menghapus menu ini? Tindakan ini tidak dapat dibatalkan."
            @confirm="deleteItem"
            @cancel="showDeleteModal = false"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue';
import MenuFormModal from '@/components/Admin/MenuFormModal.vue';
import MenuItemCard from '@/components/Admin/MenuItemCard.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, RefreshCw, Search, Utensils } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';
defineProps({
    menuItems: {
        type: Object,
        required: true,
    },
});

const toggleItemStatus = (item: MenuItem) => {
    router.patch(
        route('admin.menu.toggle-status', item.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['menuItems'] });
            },
        },
    );
};

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    category: string;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
    ingredients: string;
    allergens: string[];
    image: string;
    is_available: boolean;
    created_at: string;
    updated_at: string;
}

// State
const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref<MenuItem | null>(null);
const itemToDelete = ref<MenuItem | null>(null);

const filters = reactive({
    search: '',
    category: '',
    status: '',
});

// Methods
const openCreateModal = () => {
    selectedItem.value = null;
    isEditing.value = false;
    showModal.value = true;
};

const openEditModal = (item: MenuItem) => {
    selectedItem.value = item;
    isEditing.value = true;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedItem.value = null;
    isEditing.value = false;
};

const confirmDelete = (item: MenuItem) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    if (itemToDelete.value) {
        router.delete(route('admin.menu.destroy', itemToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            },
        });
    }
};

const handleSearch = () => {
    router.get(route('admin.menu.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.menu.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.category = '';
    filters.status = '';
    router.get(route('admin.menu.index'));
};

const handleSaved = () => {
    closeModal();
    router.reload();
};
watch(() => filters.category, handleFilter);
watch(() => filters.status, handleFilter);
</script>
