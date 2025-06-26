<template>
    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
        <template v-if="$page.props.auth.user">
            <Link
                :href="route('admin.subscriptions.index')"
                class="inline-flex items-center justify-center rounded-lg bg-green-600 px-8 py-4 text-lg font-medium text-white transition-colors hover:bg-green-700"
            >
                <Utensils class="mr-2 h-5 w-5" />
                Kelola Langganan
            </Link>
            <Link
                :href="$page.props.auth.user.role === 'admin' ? route('admin.dashboard') : route('dashboard')"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-8 py-4 text-lg font-medium text-gray-700 transition-colors hover:bg-gray-50"
            >
                <LayoutDashboard class="mr-2 h-5 w-5" />
                Dashboard Saya
            </Link>
        </template>
        <template v-else>
            <Link
                :href="route('register')"
                class="inline-flex items-center justify-center rounded-lg bg-green-600 px-8 py-4 text-lg font-medium text-white transition-colors hover:bg-green-700"
            >
                <Utensils class="mr-2 h-5 w-5" />
                Mulai Berlangganan
            </Link>
            <a
                href="#menu"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-8 py-4 text-lg font-medium text-gray-700 transition-colors hover:bg-gray-50"
                @click.prevent="$emit('scroll-to-section', 'menu')"
            >
                <Play class="mr-2 h-5 w-5" />
                Lihat Menu
            </a>
        </template>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, Play, Utensils } from 'lucide-vue-next';

defineEmits<{
    'scroll-to-section': [section: string];
}>();
</script>
