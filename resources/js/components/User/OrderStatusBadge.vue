<template>
    <Badge :class="getStatusClass(status)">
        <component :is="getStatusIcon(status)" class="mr-1 h-3 w-3" />
        {{ getStatusLabel(status) }}
    </Badge>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { CheckCircle, ChefHat, Clock, Package, Truck, XCircle } from 'lucide-vue-next';

defineProps<{
    status: string;
}>();

const getStatusClass = (status: string) => {
    const classes = {
        pending: 'bg-orange-100 text-orange-800',
        confirmed: 'bg-blue-100 text-blue-800',
        preparing: 'bg-yellow-100 text-yellow-800',
        ready: 'bg-purple-100 text-purple-800',
        out_for_delivery: 'bg-indigo-100 text-indigo-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status: string) => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        preparing: 'Preparing',
        ready: 'Ready',
        out_for_delivery: 'Out for Delivery',
        delivered: 'Delivered',
        cancelled: 'Cancelled',
    };
    return labels[status as keyof typeof labels] || status;
};

const getStatusIcon = (status: string) => {
    const icons = {
        pending: Clock,
        confirmed: Package,
        preparing: ChefHat,
        ready: CheckCircle,
        out_for_delivery: Truck,
        delivered: CheckCircle,
        cancelled: XCircle,
    };
    return icons[status as keyof typeof icons] || Clock;
};
</script>
