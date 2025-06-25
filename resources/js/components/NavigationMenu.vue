<script setup lang="ts">
const props = defineProps<{
    activeSection: string;
    scrollToSection: (section: string) => void;
}>();

const sections = ['home', 'menu', 'about', 'contact'];
const sectionLabels: Record<string, string> = {
    home: 'Home',
    menu: 'Menu',
    about: 'Tentang',
    contact: 'Kontak',
};
</script>

<template>
    <div class="flex items-center space-x-6">
        <template v-for="section in sections" :key="section">
            <a
                :href="'#' + section"
                @click.prevent="props.scrollToSection(section)"
                :class="[
                    'relative px-1 py-2 text-sm font-medium transition-all duration-300 hover:text-green-600',
                    props.activeSection === section ? 'font-semibold text-green-600' : 'text-gray-600',
                ]"
            >
                {{ sectionLabels[section] }}
                <span
                    :class="[
                        'absolute bottom-0 left-0 h-0.5 bg-green-600 transition-all duration-300',
                        props.activeSection === section ? 'w-full' : 'w-0',
                    ]"
                ></span>
            </a>
        </template>
    </div>
</template>
