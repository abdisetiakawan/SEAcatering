<template>
    <section id="about" class="py-20">
        <div class="container mx-auto px-4">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <h2 class="mb-6 text-3xl font-bold text-gray-900 md:text-4xl">
                        Tentang SEA Catering
                    </h2>
                    <p class="mb-6 text-lg leading-relaxed text-gray-600">
                        SEA Catering adalah perusahaan catering sehat terdepan di Indonesia yang berkomitmen
                        menyediakan makanan bergizi tinggi untuk gaya hidup sehat Anda.
                    </p>
                    <p class="mb-8 text-lg leading-relaxed text-gray-600">
                        Dengan pengalaman lebih dari 5 tahun, kami telah melayani ribuan pelanggan di seluruh
                        Indonesia dengan standar kualitas internasional.
                    </p>

                    <div class="space-y-4">
                        <div
                            v-for="feature in features"
                            :key="feature"
                            class="flex items-center space-x-3"
                        >
                            <CheckCircle class="h-6 w-6 text-green-600" />
                            <span class="text-gray-700">{{ feature }}</span>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="flex aspect-square items-center justify-center rounded-2xl bg-green-100">
                        <div class="text-center">
                            <Utensils class="mx-auto mb-4 h-24 w-24 text-green-600" />
                            <p class="text-lg font-semibold text-green-600">Healthy Food</p>
                            <p class="text-green-600">Made with Love</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { CheckCircle, Utensils } from 'lucide-vue-next';

const features = [
    'Bahan organik dan segar',
    'Menu disesuaikan kebutuhan nutrisi',
    'Pengiriman tepat waktu',
    'Harga terjangkau dan transparan',
];
</script>
