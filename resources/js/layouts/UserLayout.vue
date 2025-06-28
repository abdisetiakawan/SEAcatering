<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Top Navigation -->
        <UserNavbar />

        <!-- Main Content -->
        <main class="pt-16">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import UserNavbar from '@/components/User/UserNavbar.vue';
</script>
