<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Edit Status Subscription</DialogTitle>
                <DialogDescription> Ubah status subscription {{ subscription?.subscription_number }} </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updateStatus" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status Saat Ini</label>
                    <div class="mt-1">
                        <Badge :class="getStatusClass(subscription?.status || '')">
                            {{ subscription?.status_label }}
                        </Badge>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status Baru</label>
                    <Select v-model="form.status" required>
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Pilih status baru" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="paused">Dijeda</SelectItem>
                            <SelectItem value="cancelled">Dibatalkan</SelectItem>
                            <SelectItem value="expired">Berakhir</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div v-if="needsReason">
                    <label class="block text-sm font-medium text-gray-700">Alasan</label>
                    <textarea
                        v-model="form.reason"
                        rows="3"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none"
                        placeholder="Masukkan alasan perubahan status..."
                        :required="needsReason"
                    ></textarea>
                </div>
            </form>

            <DialogFooter class="flex justify-between space-x-2">
                <Button type="button" variant="outline" @click="$emit('close')"> Batal </Button>
                <Button @click="updateStatus" :disabled="!form.status || isLoading" class="bg-blue-600 hover:bg-blue-700">
                    <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                    Update Status
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { computed, defineEmits, reactive, ref, watch } from 'vue';

interface Subscription {
    id: number;
    subscription_number: string;
    status: string;
    status_label: string;
}

const props = defineProps<{
    show: boolean;
    subscription: Subscription | null;
}>();

const emit = defineEmits<{
    close: [];
    updated: [];
}>();

// State
const isLoading = ref(false);

const form = reactive({
    status: '',
    reason: '',
});

// Computed
const needsReason = computed(() => {
    return ['paused', 'cancelled'].includes(form.status);
});

// Methods
const getStatusClass = (status: string) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        paused: 'bg-yellow-100 text-yellow-800',
        cancelled: 'bg-red-100 text-red-800',
        expired: 'bg-gray-100 text-gray-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const updateStatus = async () => {
    if (!props.subscription || !form.status) return;

    isLoading.value = true;

    try {
        await router.patch(route('admin.subscriptions.update-status', props.subscription.id), {
            status: form.status,
            reason: form.reason,
        });

        emit('updated');
    } catch (error) {
        console.error('Failed to update status:', error);
    } finally {
        isLoading.value = false;
    }
};

// Reset form when modal opens
watch(
    () => props.show,
    (show) => {
        if (show) {
            form.status = '';
            form.reason = '';
        }
    },
);
</script>
