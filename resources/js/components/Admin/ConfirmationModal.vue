<template>
    <Modal :show="show" @close="$emit('cancel')" max-width="md">
        <div class="p-6">
            <div class="mb-4 flex items-center">
                <div class="flex-shrink-0">
                    <AlertTriangle class="h-6 w-6 text-red-600" />
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-500">{{ message }}</p>
            </div>

            <div class="flex justify-end space-x-3">
                <Button variant="outline" @click="$emit('cancel')"> Batal </Button>
                <Button @click="$emit('confirm')" class="bg-red-600 text-white hover:bg-red-700"> Hapus </Button>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

defineProps<{
    show: boolean;
    title: string;
    message: string;
}>();

defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>
