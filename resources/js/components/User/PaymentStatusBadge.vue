<template>
    <Badge :class="getPaymentStatusClass(status)" variant="outline">
        <component :is="getPaymentStatusIcon(status)" class="mr-1 h-3 w-3" />
        {{ getPaymentStatusLabel(status) }}
    </Badge>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { CheckCircle, Clock, CreditCard, X } from 'lucide-vue-next';

defineProps<{
    status: string;
}>();

const getPaymentStatusClass = (status: string) => {
    const classes = {
        pending: 'border-orange-200 text-orange-700',
        paid: 'border-green-200 text-green-700',
        failed: 'border-red-200 text-red-700',
        refunded: 'border-blue-200 text-blue-700',
    };
    return classes[status as keyof typeof classes] || 'border-gray-200 text-gray-700';
};

const getPaymentStatusLabel = (status: string) => {
    const labels = {
        pending: 'Payment Pending',
        paid: 'Paid',
        failed: 'Payment Failed',
        refunded: 'Refunded',
    };
    return labels[status as keyof typeof labels] || status;
};

const getPaymentStatusIcon = (status: string) => {
    const icons = {
        pending: Clock,
        paid: CheckCircle,
        failed: X,
        refunded: CreditCard,
    };
    return icons[status as keyof typeof icons] || Clock;
};
</script>
