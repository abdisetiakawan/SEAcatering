<template>
    <div class="min-h-screen bg-white">
        <!-- Navigation Header -->
        <AppHeader
            :activeSection="activeSection"
            :isMobileMenuOpen="isMobileMenuOpen"
            @toggle-mobile-menu="toggleMobileMenu"
            @scroll-to-section="scrollToSection"
        />

        <!-- Hero Section -->
        <HeroSection @scroll-to-section="scrollToSection" />

        <!-- Features Section -->
        <FeaturesSection />

        <!-- About Section -->
        <AboutSection />

        <!-- Contact Section -->
        <ContactSection />

        <!-- Footer -->
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import AboutSection from '@/components/AboutSection.vue';
import AppFooter from '@/components/AppFooter.vue';
import AppHeader from '@/components/AppHeader.vue';
import ContactSection from '@/components/ContactSection.vue';
import FeaturesSection from '@/components/FeaturesSection.vue';
import HeroSection from '@/components/HeroSection.vue';
import { onMounted, onUnmounted, ref } from 'vue';

// State
const isMobileMenuOpen = ref(false);
const activeSection = ref('home');

// Functions
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
        const headerHeight = 64;
        const elementPosition = element.offsetTop - headerHeight;

        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth',
        });
    }
    isMobileMenuOpen.value = false;
};

const handleScroll = () => {
    const sections = ['home', 'menu', 'about', 'contact'];
    const scrollPosition = window.scrollY + 100;

    for (const section of sections) {
        const element = document.getElementById(section);
        if (element) {
            const offsetTop = element.offsetTop;
            const offsetBottom = offsetTop + element.offsetHeight;

            if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
                activeSection.value = section;
                break;
            }
        }
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
/* Custom animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Smooth transitions */
* {
    transition: all 0.3s ease;
}

/* Smooth scrolling for the entire page */
html {
    scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #16a34a;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #15803d;
}

/* Navigation active state animations */
.nav-link {
    position: relative;
    overflow: hidden;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #16a34a;
    transition: width 0.3s ease;
}

.nav-link.active::after {
    width: 100%;
}
</style>
