<template>
    <nav class="fixed top-0 right-0 left-0 z-50 border-b border-gray-200 bg-white shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <!-- Left side -->
                <div class="flex items-center">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center space-x-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-600">
                            <Utensils class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-xl font-bold text-green-600">SEA Catering</span>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden items-center md:ml-10 md:flex md:space-x-6">
                        <Link
                            v-for="item in userNavItems"
                            :key="item.href"
                            :href="route(item.href)"
                            :class="[
                                'relative px-3 py-2 text-sm font-medium transition-colors duration-300',
                                route().current(item.active)
                                    ? 'border-b-2 border-green-600 text-green-600'
                                    : 'text-gray-600 hover:border-b-2 hover:border-green-300 hover:text-green-600',
                            ]"
                        >
                            {{ item.label }}
                        </Link>
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <Button variant="ghost" size="sm" class="relative">
                        <Bell class="h-5 w-5" />
                        <span
                            v-if="notifications > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white"
                        >
                            {{ notifications > 9 ? '9+' : notifications }}
                        </span>
                    </Button>

                    <!-- Shopping Cart -->
                    <div class="relative" ref="cartMenuRef">
                        <Button variant="ghost" size="sm" @click="toggleCartMenu" class="relative">
                            <ShoppingCart class="h-5 w-5" />
                            <span
                                v-if="cartItemsCount > 0"
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-xs text-white"
                            >
                                {{ cartItemsCount > 9 ? '9+' : cartItemsCount }}
                            </span>
                        </Button>

                        <!-- Cart Dropdown -->
                        <div v-if="showCartMenu" class="absolute right-0 z-50 mt-2 w-80 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                            <div class="border-b border-gray-100 px-4 py-2 text-sm font-medium text-gray-900">Shopping Cart</div>

                            <div v-if="cartItems.length === 0" class="p-4 text-center text-gray-500">Your cart is empty</div>

                            <div v-else>
                                <div class="max-h-60 overflow-y-auto">
                                    <div
                                        v-for="item in cartItems"
                                        :key="item.id"
                                        class="flex items-center justify-between px-4 py-2 hover:bg-gray-50"
                                    >
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ item.name }}</p>
                                            <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">Rp {{ formatCurrency(item.price * item.quantity) }}</div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 px-4 py-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="font-medium text-gray-900">Total:</span>
                                        <span class="font-bold text-green-600">Rp {{ formatCurrency(cartTotal) }}</span>
                                    </div>
                                    <Link :href="route('user.cart.index')" class="block">
                                        <Button class="w-full bg-green-600 hover:bg-green-700" size="sm"> View Cart & Checkout </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative" ref="userMenuRef">
                        <Button variant="ghost" size="sm" @click="toggleUserMenu" class="flex items-center space-x-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                <User class="h-4 w-4 text-green-600" />
                            </div>
                            <span class="hidden text-sm font-medium md:block">{{ user?.name }}</span>
                            <ChevronDown class="h-4 w-4" />
                        </Button>

                        <!-- User Dropdown Menu -->
                        <div v-if="showUserMenu" class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                            <Link :href="route('user.profile.index')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <User class="mr-2 h-4 w-4" />
                                Profile
                            </Link>

                            <Link :href="route('user.orders.index')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <Package class="mr-2 h-4 w-4" />
                                My Orders
                            </Link>

                            <Link
                                :href="route('user.subscriptions.index')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <Calendar class="mr-2 h-4 w-4" />
                                My Subscriptions
                            </Link>

                            <Link :href="route('user.addresses.index')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <MapPin class="mr-2 h-4 w-4" />
                                Addresses
                            </Link>

                            <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <Settings class="mr-2 h-4 w-4" />
                                Settings
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
                <MobileNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    <LayoutDashboard class="mr-2 h-4 w-4" />
                    Dashboard
                </MobileNavLink>

                <MobileNavLink :href="route('user.menu.index')" :active="route().current('user.menu.*')">
                    <Utensils class="mr-2 h-4 w-4" />
                    Menu
                </MobileNavLink>

                <MobileNavLink :href="route('user.meal-plans.index')" :active="route().current('user.meal-plans.*')">
                    <Package class="mr-2 h-4 w-4" />
                    Meal Plans
                </MobileNavLink>

                <MobileNavLink :href="route('user.orders.index')" :active="route().current('user.orders.*')">
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    My Orders
                </MobileNavLink>

                <MobileNavLink :href="route('user.subscriptions.index')" :active="route().current('user.subscriptions.*')">
                    <Calendar class="mr-2 h-4 w-4" />
                    My Subscriptions
                </MobileNavLink>

                <MobileNavLink :href="route('user.cart.index')" :active="route().current('user.cart.*')">
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Shopping Cart
                    <span
                        v-if="cartItemsCount > 0"
                        class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-xs text-white"
                    >
                        {{ cartItemsCount }}
                    </span>
                </MobileNavLink>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import MobileNavLink from '@/components/Admin/MobileNavLink.vue';
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, Calendar, ChevronDown, LayoutDashboard, LogOut, MapPin, Menu, Package, Settings, ShoppingCart, User, Utensils } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { route } from 'ziggy-js';

interface CartItem {
    id: number;
    name: string;
    quantity: number;
    price: number;
}

interface Props {
    cartItems?: CartItem[];
    notifications?: number;
}

const props = withDefaults(defineProps<Props>(), {
    cartItems: () => [],
    notifications: 0,
});

// State
const showUserMenu = ref(false);
const showCartMenu = ref(false);
const showMobileMenu = ref(false);
const userMenuRef = ref<HTMLElement>();
const cartMenuRef = ref<HTMLElement>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const cartItemsCount = computed(() => {
    return props.cartItems.reduce((total, item) => total + item.quantity, 0);
});

const cartTotal = computed(() => {
    return props.cartItems.reduce((total, item) => total + item.price * item.quantity, 0);
});

const userNavItems = [
    { label: 'Menu', href: 'user.menu.index', active: 'user.menu.*' },
    { label: 'Meal Plans', href: 'user.meal-plans.index', active: 'user.meal-plans.*' },
    { label: 'My Orders', href: 'user.orders.index', active: 'user.orders.*' },
    { label: 'My Subscriptions', href: 'user.subscriptions.index', active: 'user.subscriptions.*' },
];

// Methods
const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
    showCartMenu.value = false;
    showMobileMenu.value = false;
};

const toggleCartMenu = () => {
    showCartMenu.value = !showCartMenu.value;
    showUserMenu.value = false;
    showMobileMenu.value = false;
};

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
    showUserMenu.value = false;
    showCartMenu.value = false;
};

const closeMenus = (event: Event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
        showUserMenu.value = false;
    }
    if (cartMenuRef.value && !cartMenuRef.value.contains(event.target as Node)) {
        showCartMenu.value = false;
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID').format(amount);
};

onMounted(() => {
    document.addEventListener('click', closeMenus);
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenus);
});
</script>
