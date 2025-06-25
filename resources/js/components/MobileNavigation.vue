<template>
    <div class="border-t py-4 md:hidden">
        <nav class="flex flex-col space-y-4">
            <!-- Main Navigation -->
            <a
                v-for="item in navigationItems"
                :key="item.id"
                :href="`#${item.id}`"
                @click.prevent="$emit('scroll-to-section', item.id)"
                :class="[
                    'text-sm font-medium transition-colors',
                    activeSection === item.id ? 'border-l-2 border-green-600 pl-3 font-semibold text-green-600' : 'pl-3 text-gray-600',
                ]"
            >
                {{ item.label }}
            </a>

            <!-- Mobile Authentication -->
            <div class="mt-4 border-t pt-4">
                <template v-if="$page.props.auth.user">
                    <div class="space-y-3">
                        <div class="pl-3 text-sm font-medium text-gray-600">Halo, {{ $page.props.auth.user.name }}</div>
                        <Link
                            :href="route('dashboard')"
                            class="flex w-full items-center rounded-sm border border-green-600 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50"
                        >
                            <LayoutDashboard class="mr-2 h-4 w-4" />
                            Dashboard
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center rounded-sm border border-transparent px-4 py-2 text-sm font-medium text-red-600 hover:border-red-200"
                        >
                            <LogOut class="mr-2 h-4 w-4" />
                            Logout
                        </Link>
                    </div>
                </template>
                <template v-else>
                    <div class="space-y-3">
                        <Link
                            :href="route('login')"
                            class="block w-full rounded-sm border border-transparent px-4 py-2 text-center text-sm font-medium text-gray-600 hover:border-gray-200"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="block w-full rounded-sm border border-green-600 px-4 py-2 text-center text-sm font-medium text-green-600 hover:bg-green-50"
                        >
                            Register
                        </Link>
                    </div>
                </template>
            </div>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, LogOut } from 'lucide-vue-next';

defineProps<{
    activeSection: string;
}>();

defineEmits<{
    'scroll-to-section': [section: string];
}>();

const navigationItems = [
    { id: 'home', label: 'Home' },
    { id: 'menu', label: 'Menu' },
    { id: 'about', label: 'Tentang' },
    { id: 'contact', label: 'Kontak' },
];
</script>
