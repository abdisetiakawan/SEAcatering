<template>
    <UserLayout>
        <Head title="Menu" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Menu SEA Catering</h1>
                                <p class="mt-1 text-gray-600">Pilih dari berbagai menu sehat dan lezat</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <Link
                                    :href="route('user.meal-plans.index')"
                                    class="flex items-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                                >
                                    <Calendar class="mr-2 h-4 w-4" />
                                    Lihat Meal Plans
                                </Link>
                                <Link
                                    :href="route('user.cart.index')"
                                    class="flex items-center rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                                >
                                    <ShoppingCart class="mr-2 h-4 w-4" />
                                    Cart ({{ cartItemsCount }})
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">{{ stats.total_menu_items }}</div>
                        <div class="text-sm text-gray-600">Menu Items</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.categories_count }}</div>
                        <div class="text-sm text-gray-600">Categories</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-purple-600">{{ stats.average_rating.toFixed(1) }}/5</div>
                        <div class="text-sm text-gray-600">Average Rating</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-orange-600">{{ stats.popular_items }}</div>
                        <div class="text-sm text-gray-600">Popular Items</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Cari menu..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select v-model="filters.category">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Semua Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="category in categories" :key="category.name" :value="category.name.toString()">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="filters.price_range" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Range Harga" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Harga</SelectItem>
                                    <SelectItem value="0-25000">Rp 25,000</SelectItem>
                                    <SelectItem value="25000-50000">Rp 25,000 - 50,000</SelectItem>
                                    <SelectItem value="50000-100000">Rp 50,000 - 100,000</SelectItem>
                                    <SelectItem value="100000+">> Rp 100,000</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="filters.sort" @update:model-value="handleFilter">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Urutkan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="name">Nama A-Z</SelectItem>
                                    <SelectItem value="price_asc">Harga Terendah</SelectItem>
                                    <SelectItem value="price_desc">Harga Tertinggi</SelectItem>
                                    <SelectItem value="rating">Rating Tertinggi</SelectItem>
                                    <SelectItem value="popular">Paling Populer</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Grid -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <MenuItemCard v-for="item in menuItems.data" :key="item.id" :item="item" @add-to-cart="addToCart" @view-details="viewDetails" />
                </div>

                <!-- Empty State -->
                <div v-if="menuItems.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <Utensils class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">Tidak ada menu ditemukan</h3>
                    <p class="mb-6 text-gray-500">Coba ubah filter atau kata kunci pencarian</p>
                    <Button @click="resetFilters" class="bg-green-600 hover:bg-green-700">
                        <RefreshCw class="mr-2 h-4 w-4" />
                        Reset Filter
                    </Button>
                </div>

                <!-- Pagination -->
                <div v-if="menuItems.data.length > 0" class="rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="menuItems.links" />
                </div>
            </div>
        </div>

        <!-- Add to Cart Success Modal -->
        <SuccessModal
            :show="showSuccessModal"
            title="Berhasil Ditambahkan!"
            :message="`${selectedItem?.name} telah ditambahkan ke cart`"
            @close="showSuccessModal = false"
        />
    </UserLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Admin/Pagination.vue';
import MenuItemCard from '@/components/User/MenuItemCard.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, RefreshCw, Search, ShoppingCart, Utensils } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    image?: string;
    category: {
        id: number;
        name: string;
    };
    average_rating: number;
    review_count: number;
    is_available: boolean;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
}

interface Category {
    id: number;
    name: string;
}

interface Stats {
    total_menu_items: number;
    categories_count: number;
    average_rating: number;
    popular_items: number;
}

const props = defineProps<{
    menuItems: { data: MenuItem[]; links: any[]; meta: any };
    categories: Category[];
    stats: Stats;
    filters: Record<string, any>;
    cartItemsCount: number;
}>();

const showSuccessModal = ref(false);
const selectedItem = ref<MenuItem | null>(null);

const filters = reactive({
    search: props.filters.search || '',
    category: props.filters.category || 'all',
    price_range: props.filters.price_range || 'all',
    sort: props.filters.sort || 'name',
});

const addToCart = (item: MenuItem) => {
    router.post(
        route('user.cart.add'),
        {
            menu_item_id: item.id,
            quantity: 1,
        },
        {
            preserveState: true,
            onSuccess: () => {
                selectedItem.value = item;
                showSuccessModal.value = true;
            },
        },
    );
};

const viewDetails = (item: MenuItem) => {
    router.visit(route('user.menu.show', item.id));
};

const handleSearch = () => {
    router.get(route('user.menu.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('user.menu.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.category = 'all';
    filters.price_range = 'all';
    filters.sort = 'name';
    router.get(route('user.menu.index'));
};

watch(() => filters.category, handleFilter);
watch(() => filters.price_range, handleFilter);
watch(() => filters.sort, handleFilter);
</script>
