<template>
    <Dialog :open="show" @update:open="$emit('cancel')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <AlertTriangle class="h-5 w-5 text-orange-500" />
                    <span>{{ title }}</span>
                </DialogTitle>
                <DialogDescription>
                    {{ message }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex space-x-2 sm:space-x-0">
                <Button variant="outline" @click="$emit('cancel')"> Cancel </Button>
                <Button @click="$emit('confirm')" class="bg-red-600 hover:bg-red-700"> Confirm </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
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
