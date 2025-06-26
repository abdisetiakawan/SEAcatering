<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Top Navigation -->
        <AdminNavbar />

        <!-- Main Content -->
        <main class="pt-16">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import AdminNavbar from '@/components/Admin/AdminNavbar.vue';
</script>
