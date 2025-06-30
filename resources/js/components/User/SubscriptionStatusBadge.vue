<template>
    <span :class="badgeClasses">
        {{ statusText }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: string;
}>();

const statusText = computed(() => {
    const statusMap: Record<string, string> = {
        active: 'Active',
        paused: 'Paused',
        cancelled: 'Cancelled',
        expired: 'Expired',
    };
    return statusMap[props.status] || props.status;
});

const badgeClasses = computed(() => {
    const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';

    const statusClasses: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        paused: 'bg-yellow-100 text-yellow-800',
        cancelled: 'bg-red-100 text-red-800',
        expired: 'bg-gray-100 text-gray-800',
    };

    return `${baseClasses} ${statusClasses[props.status] || 'bg-gray-100 text-gray-800'}`;
});
</script>
