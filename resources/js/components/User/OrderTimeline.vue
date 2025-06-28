<template>
    <div class="flow-root">
        <ul role="list" class="-mb-8">
            <li v-for="(step, stepIdx) in timelineSteps" :key="step.name">
                <div class="relative pb-8">
                    <span
                        v-if="stepIdx !== timelineSteps.length - 1"
                        class="absolute top-4 left-4 -ml-px h-full w-0.5"
                        :class="step.completed ? 'bg-green-600' : 'bg-gray-200'"
                        aria-hidden="true"
                    />
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white" :class="getStepClasses(step)">
                                <component :is="step.icon" class="h-5 w-5" aria-hidden="true" />
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ step.name }}</p>
                                <p class="text-sm text-gray-500">{{ step.description }}</p>
                            </div>
                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                <time v-if="step.datetime" :datetime="step.datetime">{{ formatTime(step.datetime) }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { CheckCircle, Clock, Package, Truck, X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    status: string;
    createdAt: string;
}>();

const timelineSteps = computed(() => {
    const steps = [
        {
            name: 'Pesanan Dibuat',
            description: 'Pesanan Anda telah berhasil dibuat',
            icon: Package,
            completed: true,
            current: props.status === 'pending',
            datetime: props.createdAt,
        },
        {
            name: 'Pesanan Dikonfirmasi',
            description: 'Pesanan Anda sedang diproses',
            icon: CheckCircle,
            completed: ['confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'].includes(props.status),
            current: props.status === 'confirmed',
            datetime: props.status !== 'pending' ? props.createdAt : null,
        },
        {
            name: 'Sedang Dipersiapkan',
            description: 'Makanan Anda sedang dipersiapkan',
            icon: Clock,
            completed: ['preparing', 'ready', 'out_for_delivery', 'delivered'].includes(props.status),
            current: props.status === 'preparing',
            datetime: ['preparing', 'ready', 'out_for_delivery', 'delivered'].includes(props.status) ? props.createdAt : null,
        },
        {
            name: 'Siap Dikirim',
            description: 'Pesanan siap untuk dikirim',
            icon: Package,
            completed: ['ready', 'out_for_delivery', 'delivered'].includes(props.status),
            current: props.status === 'ready',
            datetime: ['ready', 'out_for_delivery', 'delivered'].includes(props.status) ? props.createdAt : null,
        },
        {
            name: 'Dalam Perjalanan',
            description: 'Pesanan sedang dalam perjalanan',
            icon: Truck,
            completed: ['out_for_delivery', 'delivered'].includes(props.status),
            current: props.status === 'out_for_delivery',
            datetime: ['out_for_delivery', 'delivered'].includes(props.status) ? props.createdAt : null,
        },
        {
            name: 'Terkirim',
            description: 'Pesanan telah sampai di tujuan',
            icon: CheckCircle,
            completed: props.status === 'delivered',
            current: props.status === 'delivered',
            datetime: props.status === 'delivered' ? props.createdAt : null,
        },
    ];

    // Handle cancelled status
    if (props.status === 'cancelled') {
        return [
            steps[0], // Order created
            {
                name: 'Pesanan Dibatalkan',
                description: 'Pesanan telah dibatalkan',
                icon: X,
                completed: true,
                current: true,
                datetime: props.createdAt,
            },
        ];
    }

    return steps;
});

const getStepClasses = (step: any) => {
    if (step.completed) {
        return 'bg-green-600 text-white';
    } else if (step.current) {
        return 'bg-blue-600 text-white';
    } else {
        return 'bg-gray-200 text-gray-600';
    }
};

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
