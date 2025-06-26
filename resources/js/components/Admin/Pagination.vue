<template>
    <div class="flex items-center justify-between">
        <!-- Mobile Navigation -->
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-if="prev?.url"
                :href="prev.url"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Previous
            </Link>
            <Link
                v-if="next?.url"
                :href="next.url"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Next
            </Link>
        </div>

        <!-- Desktop Pagination -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ meta?.from }}</span>
                    to
                    <span class="font-medium">{{ meta?.to }}</span>
                    of
                    <span class="font-medium">{{ meta?.total }}</span>
                    results
                </p>
            </div>

            <div>
                <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm">
                    <template v-for="(link, index) in links" :key="index">
                        <component
                            :is="link.url ? Link : 'span'"
                            :href="link.url || undefined"
                            :class="[
                                'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
                                link.active
                                    ? 'z-10 border-green-500 bg-green-50 text-green-600'
                                    : link.url
                                      ? 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'
                                      : 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400',
                                index === 0 ? 'rounded-l-md' : '',
                                index === links.length - 1 ? 'rounded-r-md' : '',
                            ]"
                        >
                            <span v-html="link.label" />
                        </component>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    links: PaginationLink[];
    meta?: {
        from: number;
        to: number;
        total: number;
    };
    prev?: { url: string | null };
    next?: { url: string | null };
}>();
</script>
