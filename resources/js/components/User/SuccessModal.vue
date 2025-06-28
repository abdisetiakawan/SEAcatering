<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="mb-4 flex items-center justify-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <CheckCircle class="h-6 w-6 text-green-600" />
                    </div>
                </div>
                <DialogTitle class="text-center text-lg font-semibold">{{ title }}</DialogTitle>
                <DialogDescription class="text-center text-gray-600">
                    {{ message }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex justify-center space-x-2">
                <Button @click="$emit('close')" class="bg-green-600 hover:bg-green-700">
                    <Check class="mr-2 h-4 w-4" />
                    OK
                </Button>
                <Button v-if="showCartButton" variant="outline" @click="goToCart">
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Lihat Cart
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import { Check, CheckCircle, ShoppingCart } from 'lucide-vue-next';

defineProps<{
    show: boolean;
    title: string;
    message: string;
    showCartButton?: boolean;
}>();

defineEmits<{
    close: [];
}>();

const goToCart = () => {
    router.visit(route('user.cart.index'));
};
</script>
