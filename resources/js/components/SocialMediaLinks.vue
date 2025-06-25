<template>
    <div class="flex space-x-4">
        <div
            v-for="social in socialLinks"
            :key="social.name"
            class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-gray-800 transition-colors hover:bg-green-600"
        >
            <span class="text-sm font-bold">{{ social.icon }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
const socialLinks = [
    { name: 'Facebook', icon: 'f' },
    { name: 'Instagram', icon: 'ig' },
    { name: 'Twitter', icon: 'tw' },
];
</script>
