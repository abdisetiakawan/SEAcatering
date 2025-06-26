<template>
    <nav class="fixed top-0 right-0 left-0 z-50 border-b border-gray-200 bg-white shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <!-- Left side -->
                <div class="flex items-center">
                    <!-- Logo -->
                    <Link :href="route('admin.dashboard')" class="flex items-center space-x-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-600">
                            <Utensils class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-xl font-bold text-green-600">SEA Catering</span>
                        <Badge variant="outline" class="ml-2 text-xs">Admin</Badge>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                            <LayoutDashboard class="mr-2 h-4 w-4" />
                            Dashboard
                        </NavLink>

                        <!-- <NavLink :href="route('admin.menu.index')" :active="route().current('admin.menu.*')">
                            <Utensils class="mr-2 h-4 w-4" />
                            Menu Management
                        </NavLink>

                        <NavLink :href="route('admin.plans.index')" :active="route().current('admin.plans.*')">
                            <Package class="mr-2 h-4 w-4" />
                            Meal Plans
                        </NavLink>

                        <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                            <Users class="mr-2 h-4 w-4" />
                            Users
                        </NavLink>

                        <NavLink :href="route('admin.orders.index')" :active="route().current('admin.orders.*')">
                            <ShoppingCart class="mr-2 h-4 w-4" />
                            Orders
                        </NavLink>

                        <NavLink :href="route('admin.inventory.index')" :active="route().current('admin.inventory.*')">
                            <Package2 class="mr-2 h-4 w-4" />
                            Inventory
                        </NavLink> -->
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <Button variant="ghost" size="sm" class="relative">
                        <Bell class="h-5 w-5" />
                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                            3
                        </span>
                    </Button>

                    <!-- User Menu -->
                    <div class="relative" ref="userMenuRef">
                        <Button variant="ghost" size="sm" @click="toggleUserMenu" class="flex items-center space-x-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                <User class="h-4 w-4 text-green-600" />
                            </div>
                            <span class="hidden text-sm font-medium md:block">{{ $page.props.auth.user.name }}</span>
                            <ChevronDown class="h-4 w-4" />
                        </Button>

                        <!-- Dropdown Menu -->
                        <div v-if="showUserMenu" class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                            <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <Settings class="mr-2 h-4 w-4" />
                                Profile Settings
                            </Link>

                            <Link :href="route('home')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <Globe class="mr-2 h-4 w-4" />
                                View Website
                            </Link>

                            <div class="border-t border-gray-100"></div>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                            >
                                <LogOut class="mr-2 h-4 w-4" />
                                Logout
                            </Link>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <Button variant="ghost" size="sm" class="md:hidden" @click="toggleMobileMenu">
                        <Menu class="h-5 w-5" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-if="showMobileMenu" class="border-t border-gray-200 bg-white md:hidden">
            <div class="space-y-1 px-2 pt-2 pb-3">
                <MobileNavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                    <LayoutDashboard class="mr-2 h-4 w-4" />
                    Dashboard
                </MobileNavLink>

                <MobileNavLink :href="route('admin.menu.index')" :active="route().current('admin.menu.*')">
                    <Utensils class="mr-2 h-4 w-4" />
                    Menu Management
                </MobileNavLink>

                <MobileNavLink :href="route('admin.plans.index')" :active="route().current('admin.plans.*')">
                    <Package class="mr-2 h-4 w-4" />
                    Meal Plans
                </MobileNavLink>

                <MobileNavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                    <Users class="mr-2 h-4 w-4" />
                    Users
                </MobileNavLink>

                <MobileNavLink :href="route('admin.orders.index')" :active="route().current('admin.orders.*')">
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Orders
                </MobileNavLink>

                <MobileNavLink :href="route('admin.inventory.index')" :active="route().current('admin.inventory.*')">
                    <Package2 class="mr-2 h-4 w-4" />
                    Inventory
                </MobileNavLink>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import MobileNavLink from '@/components/Admin/MobileNavLink.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import {
    Bell,
    ChevronDown,
    Globe,
    LayoutDashboard,
    LogOut,
    Menu,
    Package,
    Package2,
    Settings,
    ShoppingCart,
    User,
    Users,
    Utensils,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

// State
const showUserMenu = ref(false);
const showMobileMenu = ref(false);
const userMenuRef = ref<HTMLElement>();

// Methods
const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
    showMobileMenu.value = false;
};

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
    showUserMenu.value = false;
};

const closeMenus = (event: Event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeMenus);
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenus);
});
</script>
