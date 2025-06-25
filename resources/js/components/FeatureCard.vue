<template>
    <Card class="p-8 text-center transition-shadow hover:shadow-lg">
        <CardContent class="pt-6">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <component :is="icon" class="h-8 w-8 text-green-600" />
            </div>
            <h3 class="mb-4 text-xl font-semibold">{{ title }}</h3>
            <p class="leading-relaxed text-gray-600">{{ description }}</p>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    icon: any;
    title: string;
    description: string;
}>();
</script>
