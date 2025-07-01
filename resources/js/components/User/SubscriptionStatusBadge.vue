<template>
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusClasses">
        <component :is="statusIcon" class="mr-1 h-3 w-3" />
        {{ statusLabel }}
    </span>
</template>

<script setup lang="ts">
import { CheckCircle, Clock, Pause, X } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    status: string;
}

const props = defineProps<Props>();

const statusConfig = {
    pending: {
        label: 'Pending',
        icon: Clock,
        classes: 'bg-gray-100 text-gray-800',
    },
    active: {
        label: 'Active',
        icon: CheckCircle,
        classes: 'bg-green-100 text-green-800',
    },
    paused: {
        label: 'Paused',
        icon: Pause,
        classes: 'bg-yellow-100 text-yellow-800',
    },
    cancelled: {
        label: 'Cancelled',
        icon: X,
        classes: 'bg-red-100 text-red-800',
    },
    expired: {
        label: 'Expired',
        icon: Clock,
        classes: 'bg-gray-100 text-gray-800',
    },
};

const statusLabel = computed(() => {
    return statusConfig[props.status as keyof typeof statusConfig]?.label || props.status;
});

const statusIcon = computed(() => {
    return statusConfig[props.status as keyof typeof statusConfig]?.icon || Clock;
});

const statusClasses = computed(() => {
    return statusConfig[props.status as keyof typeof statusConfig]?.classes || 'bg-gray-100 text-gray-800';
});
</script>
