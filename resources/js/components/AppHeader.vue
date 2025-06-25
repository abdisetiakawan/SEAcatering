<template>
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div class="container mx-auto px-4">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex cursor-pointer items-center space-x-2" @click="$emit('scroll-to-section', 'home')">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-600">
                        <Utensils class="h-5 w-5 text-white" />
                    </div>
                    <span class="text-2xl font-bold text-green-600">SEA Catering</span>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden items-center space-x-6 md:flex">
                    <NavigationMenu :activeSection="activeSection" :scrollToSection="(section) => $emit('scroll-to-section', section)" />
                    <AuthMenu />
                </nav>

                <!-- Mobile Menu Button -->
                <Button variant="ghost" size="sm" class="md:hidden" @click="$emit('toggle-mobile-menu')">
                    <Menu class="h-5 w-5" />
                </Button>
            </div>

            <!-- Mobile Navigation -->
            <MobileNavigation v-if="isMobileMenuOpen" :activeSection="activeSection" @scroll-to-section="$emit('scroll-to-section', $event)" />
        </div>
    </header>
</template>

<script setup lang="ts">
import AuthMenu from '@/components/AuthMenu.vue';
import MobileNavigation from '@/components/MobileNavigation.vue';
import NavigationMenu from '@/components/NavigationMenu.vue';
import { Button } from '@/components/ui/button';
import { Menu, Utensils } from 'lucide-vue-next';

defineProps<{
    activeSection: string;
    isMobileMenuOpen: boolean;
}>();

defineEmits<{
    'toggle-mobile-menu': [];
    'scroll-to-section': [section: string];
}>();
</script>
