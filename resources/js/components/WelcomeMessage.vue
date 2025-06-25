<template>
    <div class="mt-8 rounded-lg border border-green-200 bg-green-50 p-4">
        <p class="text-green-800">
            <CheckCircle class="mr-2 inline h-5 w-5" />
            Selamat datang kembali, {{ $page.props.auth.user.name }}! Siap untuk melanjutkan perjalanan hidup sehat Anda?
        </p>
    </div>
</template>

<script setup lang="ts">
import { CheckCircle } from 'lucide-vue-next';
</script>
