<template>
    <section id="home" class="relative py-20 lg:py-32">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl text-center">
                <!-- Main Heading -->
                <h1 class="mb-6 text-4xl leading-tight font-bold text-gray-900 md:text-6xl">
                    Healthy Meals,
                    <span class="text-green-600">Anytime, Anywhere</span>
                </h1>

                <!-- Subtitle -->
                <p class="mb-8 text-xl leading-relaxed text-gray-600 md:text-2xl">
                    Makanan sehat berkualitas tinggi diantar langsung ke rumah Anda. Nikmati menu variatif yang disiapkan oleh chef profesional.
                </p>

                <!-- CTA Buttons -->
                <CTAButtons @scroll-to-section="$emit('scroll-to-section', $event)" />

                <!-- Welcome Message for Authenticated Users -->
                <WelcomeMessage v-if="$page.props.auth.user" />

                <!-- Stats -->
                <StatsSection />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import CTAButtons from '@/components/CTAButtons.vue';
import StatsSection from '@/components/StatsSection.vue';
import WelcomeMessage from '@/components/WelcomeMessage.vue';

defineEmits<{
    'scroll-to-section': [section: string];
}>();
</script>
