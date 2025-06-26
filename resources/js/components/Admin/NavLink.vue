<template>
    <Link
        :href="href"
        :class="[
            'inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors',
            active ? 'border-b-2 border-green-600 bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
        ]"
    >
        <slot />
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
    active?: boolean;
}>();
</script>
