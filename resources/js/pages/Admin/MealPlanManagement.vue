<template>
    <AdminLayout>
        <Head title="Meal Plan Management" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Meal Plan Management</h1>
                                <p class="mt-1 text-gray-600">Kelola paket meal plan</p>
                            </div>
                            <Link
                                :href="route('admin.meal-plans.create')"
                                class="flex items-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Buat Meal Plan Baru
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Cari meal plan..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select v-model="filters.plan_type" @change="handleFilter">
                                <option value="">Semua Tipe</option>
                                <option v-for="(label, value) in planTypes" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </Select>
                            <Select v-model="filters.status" @change="handleFilter">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </Select>
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Meal Plans Grid -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <MealPlanCard
                        v-for="plan in mealPlans.data"
                        :key="plan.id"
                        :plan="plan"
                        @edit="editPlan"
                        @delete="confirmDelete"
                        @toggle-status="toggleStatus"
                    />
                </div>

                <!-- Empty State -->
                <div v-if="mealPlans.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <Utensils class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">Belum ada meal plan</h3>
                    <p class="mb-6 text-gray-500">Mulai dengan membuat meal plan pertama Anda</p>
                    <Link
                        :href="route('admin.meal-plans.create')"
                        class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Meal Plan Baru
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="mealPlans.data.length > 0" class="rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="mealPlans.links" />
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Hapus Meal Plan"
            message="Apakah Anda yakin ingin menghapus meal plan ini? Tindakan ini tidak dapat dibatalkan."
            @confirm="deletePlan"
            @cancel="showDeleteModal = false"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue';
import MealPlanCard from '@/components/Admin/MealPlanCard.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, RefreshCw, Search, Utensils } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: number;
    target_calories: number;
    plan_type: string;
    is_active: boolean;
    image: string;
    features: string[];
    menu_items_count: number;
    subscriptions_count: number;
    created_at: string;
}

interface MealPlansResponse {
    data: MealPlan[];
    links: any[];
    meta: any;
}

const props = defineProps<{
    mealPlans: MealPlansResponse;
    filters: Record<string, any>;
    planTypes: Record<string, string>;
}>();

// State
const showDeleteModal = ref(false);
const planToDelete = ref<MealPlan | null>(null);

const filters = reactive({
    search: props.filters.search || '',
    plan_type: props.filters.plan_type || '',
    status: props.filters.status || '',
});

// Methods
const editPlan = (plan: MealPlan) => {
    router.visit(route('admin.meal-plans.edit', plan.id));
};

const confirmDelete = (plan: MealPlan) => {
    planToDelete.value = plan;
    showDeleteModal.value = true;
};

const deletePlan = () => {
    if (planToDelete.value) {
        router.delete(route('admin.meal-plans.destroy', planToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                planToDelete.value = null;
            },
        });
    }
};

const toggleStatus = (plan: MealPlan) => {
    router.patch(route('admin.meal-plans.toggle-status', plan.id));
};

const handleSearch = () => {
    router.get(route('admin.meal-plans.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.meal-plans.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.plan_type = '';
    filters.status = '';
    router.get(route('admin.meal-plans.index'));
};
</script>
