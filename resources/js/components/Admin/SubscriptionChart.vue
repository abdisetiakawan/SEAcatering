<template>
    <div class="relative">
        <canvas ref="chartCanvas" class="h-64 w-full"></canvas>
        <div v-if="loading" class="bg-opacity-75 absolute inset-0 flex items-center justify-center bg-white">
            <div class="text-center">
                <div class="mx-auto mb-2 h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"></div>
                <p class="text-sm text-gray-600">Loading chart...</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Chart, registerables } from 'chart.js';
import { nextTick, onMounted, ref, watch } from 'vue';

Chart.register(...registerables);

interface ChartData {
    labels: string[];
    datasets: Array<{
        label: string;
        data: number[];
        borderColor: string;
        backgroundColor: string;
        tension: number;
        fill: boolean;
    }>;
}

const props = defineProps<{
    data: ChartData;
    title?: string;
    loading?: boolean;
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
const chartInstance = ref<Chart | null>(null);
const loading = ref(props.loading || false);

const createChart = async () => {
    if (!chartCanvas.value || !props.data) return;

    await nextTick();

    // Destroy existing chart
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) return;

    chartInstance.value = new Chart(ctx, {
        type: 'line',
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: !!props.title,
                    text: props.title,
                    font: {
                        size: 16,
                        weight: 'bold',
                    },
                },
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y;
                            return `${label}: ${value}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date',
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Subscriptions',
                    },
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false,
            },
        },
    });
};

onMounted(() => {
    createChart();
});

watch(
    () => props.data,
    () => {
        createChart();
    },
    { deep: true },
);

watch(
    () => props.loading,
    (newVal) => {
        loading.value = newVal;
    },
);
</script>
