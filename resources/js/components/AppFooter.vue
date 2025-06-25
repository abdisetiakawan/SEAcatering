<template>
    <footer class="bg-gray-900 py-12 text-white">
        <div class="container mx-auto px-4">
            <div class="grid gap-8 md:grid-cols-4">
                <!-- Company Info -->
                <div class="col-span-2">
                    <div class="mb-4 flex items-center space-x-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-600">
                            <Utensils class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-2xl font-bold">SEA Catering</span>
                    </div>
                    <p class="mb-4 leading-relaxed text-gray-400">
                        Penyedia layanan catering sehat terpercaya di Indonesia. Komitmen kami adalah memberikan makanan bergizi untuk hidup yang
                        lebih sehat.
                    </p>
                    <SocialMediaLinks />
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="mb-4 font-semibold">Menu</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li v-for="menu in menuItems" :key="menu">
                            <a href="#" class="transition-colors hover:text-white">{{ menu }}</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="mb-4 font-semibold">Kontak</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center space-x-2">
                            <Phone class="h-4 w-4" />
                            <span>08123456789</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <Mail class="h-4 w-4" />
                            <span>info@seacatering.com</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <MapPin class="h-4 w-4" />
                            <span>Jakarta, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; 2024 SEA Catering. All rights reserved. Made with ❤️ for healthy living.</p>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import SocialMediaLinks from '@/components/SocialMediaLinks.vue';
import { Mail, MapPin, Phone, Utensils } from 'lucide-vue-next';

const menuItems = ['Diet Plan', 'Protein Plan', 'Royal Plan', 'Custom Menu'];
</script>
