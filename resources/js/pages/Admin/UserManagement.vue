<template>
    <AdminLayout>
        <Head title="User Management" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                                <p class="mt-1 text-gray-600">Kelola semua pengguna</p>
                            </div>
                            <Button @click="openCreateModal" class="bg-green-600 hover:bg-green-700">
                                <Plus class="mr-2 h-4 w-4" />
                                Tambah User Baru
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.total_users }}</div>
                        <div class="text-sm text-gray-600">Total Users</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">{{ stats.active_subscribers }}</div>
                        <div class="text-sm text-gray-600">Active Subscribers</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-purple-600">{{ stats.admin_users }}</div>
                        <div class="text-sm text-gray-600">Admin Users</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-orange-600">{{ stats.unverified_users }}</div>
                        <div class="text-sm text-gray-600">Unverified</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-indigo-600">{{ stats.new_users_today }}</div>
                        <div class="text-sm text-gray-600">New Today</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-gray-400" />
                                <Input v-model="filters.search" placeholder="Cari user..." class="pl-10" @input="handleSearch" />
                            </div>
                            <Select v-model="filters.subscription_status" @update:modelValue="handleFilter">
                                <SelectTrigger class="w-[220px]">
                                    <SelectValue placeholder="Subscription Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Subscription</SelectItem>
                                    <SelectItem value="active">Active Subscriber</SelectItem>
                                    <SelectItem value="inactive">No Active Subscription</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="filters.role" @update:modelValue="handleFilter">
                                <SelectTrigger class="w-[220px]">
                                    <SelectValue placeholder="Role Pengguna" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Role</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                    <SelectItem value="user">Regular User</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                        <input type="checkbox" @change="toggleSelectAll" class="rounded" />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Subscriptions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="user.id" v-model="selectedUsers" class="rounded" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300">
                                                    <User class="h-6 w-6 text-gray-600" />
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center text-sm font-medium text-gray-900">
                                                    {{ user.name }}
                                                    <Shield v-if="user.role === 'admin'" class="ml-2 h-4 w-4 text-purple-600" />
                                                </div>
                                                <div class="text-sm text-gray-500">{{ user.email }}</div>
                                                <div v-if="user.phone" class="text-sm text-gray-500">{{ user.phone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col space-y-1">
                                            <Badge v-if="user.email_verified_at" variant="default"> Verified </Badge>
                                            <Badge v-else variant="outline"> Unverified </Badge>
                                            <Badge v-if="user.role === 'admin'" variant="secondary"> Admin </Badge>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                        <div v-if="user.subscriptions.length > 0">
                                            <div v-for="subscription in user.subscriptions" :key="subscription.id" class="mb-1">
                                                <Badge :variant="subscription.status === 'active' ? 'default' : 'secondary'">
                                                    {{ subscription.meal_plan.name }}
                                                </Badge>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-500">No subscriptions</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Link :href="route('admin.users.show', user.id)" class="text-indigo-600 hover:text-indigo-900">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                            <Button size="sm" variant="outline" @click="editUser(user)">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="toggleRole(user)">
                                                <Shield class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedUsers.length > 0" class="mt-4 rounded-lg bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ selectedUsers.length }} users selected</span>
                        <div class="flex space-x-2">
                            <Button size="sm" @click="bulkAction('verify_email')"> Verify Email </Button>
                            <Button size="sm" @click="bulkAction('make_admin')"> Make Admin </Button>
                            <Button size="sm" variant="destructive" @click="bulkAction('delete')"> Delete </Button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="users.data.length > 0" class="mt-6 rounded-lg bg-white p-4 shadow-sm">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Create/Edit User Modal -->
        <UserFormModal :show="showUserModal" :user="selectedUser" :is-editing="isEditing" @close="closeUserModal" @saved="handleUserSaved" />

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Delete Users"
            :message="`Are you sure you want to delete ${selectedUsers.length} user(s)? This action cannot be undone.`"
            @confirm="confirmBulkDelete"
            @cancel="showDeleteModal = false"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import UserFormModal from '@/components/Admin/UserFormModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Plus, RefreshCw, Search, Shield, User } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

type UserRole = 'user' | 'customer' | 'chef' | 'driver' | 'admin';
type Gender = 'male' | 'female' | 'other' | '';
type VehicleType = 'motorcycle' | 'car' | 'van' | '';
interface UserData {
    id: number;
    name: string;
    email: string;
    phone?: string;
    role: UserRole;
    email_verified_at?: string | null;
    is_active: boolean;
    date_of_birth?: string | null;
    gender?: Gender;
    address?: string | null;
    admin_notes?: string | null;
    created_at: string;
    updated_at?: string;
    // Driver specific fields
    license_number?: string | null;
    vehicle_type?: VehicleType;
    vehicle_plate?: string | null;
    is_available?: boolean;
    subscriptions: Array<{
        id: number;
        status: string;
        meal_plan: {
            name: string;
        };
    }>;
}

const props = defineProps<{
    users: { data: UserData[]; links: any[]; meta: any };
    filters: Record<string, any>;
    stats: Record<string, number>;
}>();

// State
const showUserModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedUser = ref<UserData | null>(null);
const selectedUsers = ref<number[]>([]);

// Methods
const openCreateModal = () => {
    selectedUser.value = null;
    isEditing.value = false;
    showUserModal.value = true;
};

const editUser = (user: UserData) => {
    selectedUser.value = user;
    isEditing.value = true;
    showUserModal.value = true;
};

const closeUserModal = () => {
    showUserModal.value = false;
    selectedUser.value = null;
    isEditing.value = false;
};

const handleUserSaved = () => {
    closeUserModal();
    router.reload();
};

const toggleRole = (user: UserData) => {
    router.patch(route('admin.users.toggle-role', user.id));
};

const toggleSelectAll = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.checked) {
        selectedUsers.value = props.users.data.map((user) => user.id);
    } else {
        selectedUsers.value = [];
    }
};

const bulkAction = (action: string) => {
    if (action === 'delete') {
        showDeleteModal.value = true;
    } else {
        router.post(
            route('admin.users.bulk-action'),
            {
                user_ids: selectedUsers.value,
                action: action,
            },
            {
                onSuccess: () => {
                    selectedUsers.value = [];
                },
            },
        );
    }
};

const confirmBulkDelete = () => {
    router.post(
        route('admin.users.bulk-action'),
        {
            user_ids: selectedUsers.value,
            action: 'delete',
        },
        {
            onSuccess: () => {
                selectedUsers.value = [];
                showDeleteModal.value = false;
            },
        },
    );
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID');
};

const handleSearch = () => {
    router.get(route('admin.users.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.users.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

interface Filters {
    search: string;
    subscription_status: string;
    role: string;
}

const filters = reactive<Filters>({
    search: props.filters.search || '',
    subscription_status: props.filters.subscription_status || '',
    role: props.filters.role || '',
});

const resetFilters = () => {
    (Object.keys(filters) as (keyof Filters)[]).forEach((key) => {
        filters[key] = '';
    });
    router.get(route('admin.users.index'));
};
</script>
