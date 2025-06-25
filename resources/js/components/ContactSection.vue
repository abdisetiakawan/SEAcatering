<template>
    <section id="contact" class="bg-green-600 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center text-white">
                <h2 class="mb-6 text-3xl font-bold md:text-4xl">Siap Memulai Hidup Sehat?</h2>
                <p class="mb-8 text-xl opacity-90">Hubungi kami sekarang untuk konsultasi gratis dan dapatkan penawaran terbaik</p>

                <ContactCard />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import ContactCard from '@/components/ContactCard.vue';
</script>
