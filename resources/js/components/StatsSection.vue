<template>
    <div class="mt-16 grid grid-cols-3 gap-8 border-t pt-16">
        <div v-for="stat in stats" :key="stat.number" class="text-center">
            <div class="mb-2 text-3xl font-bold text-green-600 md:text-4xl">
                {{ stat.number }}
            </div>
            <div class="text-gray-600">{{ stat.label }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
const stats = [
    { number: '1000+', label: 'Pelanggan Puas' },
    { number: '50+', label: 'Menu Variatif' },
    { number: '24/7', label: 'Layanan' },
];
</script>
