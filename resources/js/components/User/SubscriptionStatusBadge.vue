<template>
    <Badge :class="getStatusClass(status)" class="text-xs">
        <component :is="getStatusIcon(status)" class="mr-1 h-3 w-3" />
        {{ getStatusLabel(status) }}
    </Badge>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { CheckCircle, Clock, Pause, X } from 'lucide-vue-next';

defineProps<{
    status: string;
}>();

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        active: 'bg-green-100 text-green-800 border-green-200',
        paused: 'bg-orange-100 text-orange-800 border-orange-200',
        cancelled: 'bg-red-100 text-red-800 border-red-200',
        expired: 'bg-gray-100 text-gray-800 border-gray-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        active: 'Active',
        paused: 'Paused',
        cancelled: 'Cancelled',
        expired: 'Expired',
    };
    return labels[status] || status;
};

const getStatusIcon = (status: string) => {
    const icons: Record<string, any> = {
        active: CheckCircle,
        paused: Pause,
        cancelled: X,
        expired: Clock,
    };
    return icons[status] || Clock;
};
</script>
