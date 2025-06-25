<template>
    <section id="menu" class="bg-gray-50 py-20">
        <div class="container mx-auto px-4">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">Mengapa Memilih SEA Catering?</h2>
                <p class="mx-auto max-w-2xl text-xl text-gray-600">
                    Kami berkomitmen memberikan pengalaman terbaik dengan layanan berkualitas tinggi
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <FeatureCard
                    v-for="feature in features"
                    :key="feature.title"
                    :icon="feature.icon"
                    :title="feature.title"
                    :description="feature.description"
                />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import FeatureCard from '@/components/FeatureCard.vue';
import { ChefHat, Shield, Truck } from 'lucide-vue-next';

const features = [
    {
        icon: ChefHat,
        title: 'Menu Variatif',
        description: 'Pilihan menu sehat yang beragam setiap hari, disiapkan oleh chef berpengalaman dengan bahan-bahan segar pilihan terbaik.',
    },
    {
        icon: Truck,
        title: 'Antar ke Rumah',
        description: 'Layanan antar langsung ke alamat Anda dengan sistem cold chain untuk menjaga kesegaran makanan hingga sampai tujuan.',
    },
    {
        icon: Shield,
        title: 'Kualitas Terjamin',
        description:
            'Bahan segar berkualitas tinggi dengan standar kebersihan yang ketat. Semua makanan telah lulus uji kualitas dan keamanan pangan.',
    },
];
</script>
