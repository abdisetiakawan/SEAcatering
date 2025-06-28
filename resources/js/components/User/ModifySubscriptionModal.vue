<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Modify Subscription</DialogTitle>
                <DialogDescription> Update your subscription preferences </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Delivery Frequency -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Delivery Frequency</label>
                    <Select v-model="form.delivery_frequency">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Select frequency" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="daily">Daily</SelectItem>
                            <SelectItem value="weekly">Weekly</SelectItem>
                            <SelectItem value="monthly">Monthly</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Delivery Days -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Delivery Days</label>
                    <div class="mt-2 grid grid-cols-4 gap-2">
                        <label v-for="day in availableDays" :key="day.value" class="flex items-center space-x-2">
                            <input type="checkbox" :value="day.value" v-model="form.delivery_days" class="rounded border-gray-300" />
                            <span class="text-sm">{{ day.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Preferred Delivery Time -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Preferred Delivery Time</label>
                    <Select v-model="form.preferred_delivery_time">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Select time" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="08:00-10:00">08:00 - 10:00</SelectItem>
                            <SelectItem value="10:00-12:00">10:00 - 12:00</SelectItem>
                            <SelectItem value="12:00-14:00">12:00 - 14:00</SelectItem>
                            <SelectItem value="14:00-16:00">14:00 - 16:00</SelectItem>
                            <SelectItem value="16:00-18:00">16:00 - 18:00</SelectItem>
                            <SelectItem value="18:00-20:00">18:00 - 20:00</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Delivery Address -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Delivery Address</label>
                    <Select v-model="form.delivery_address_id">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Select address" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="address in addresses" :key="address.id" :value="address.id.toString()">
                                {{ address.address_line_1 }}, {{ address.city }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Price Summary -->
                <div class="rounded-lg bg-gray-50 p-4">
                    <h4 class="font-medium text-gray-900">Updated Pricing</h4>
                    <div class="mt-2 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>Price per meal:</span>
                            <span>Rp {{ formatPrice(subscription?.price_per_meal || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Deliveries per week:</span>
                            <span>{{ form.delivery_days.length }}</span>
                        </div>
                        <div class="flex justify-between font-medium">
                            <span>Weekly total:</span>
                            <span>Rp {{ formatPrice((subscription?.price_per_meal || 0) * form.delivery_days.length) }}</span>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('close')"> Cancel </Button>
                    <Button type="submit" :disabled="processing" class="bg-green-600 hover:bg-green-700">
                        <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                        Update Subscription
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

interface Subscription {
    id: number;
    delivery_frequency: string;
    delivery_days: string[];
    preferred_delivery_time: string;
    delivery_address_id: number;
    price_per_meal: number;
}

interface Address {
    id: number;
    address_line_1: string;
    city: string;
    province: string;
}

const props = defineProps<{
    show: boolean;
    subscription: Subscription | null;
}>();

const emit = defineEmits<{
    close: [];
    updated: [];
}>();

const processing = ref(false);
const addresses = ref<Address[]>([]);

const availableDays = [
    { value: 'monday', label: 'Monday' },
    { value: 'tuesday', label: 'Tuesday' },
    { value: 'wednesday', label: 'Wednesday' },
    { value: 'thursday', label: 'Thursday' },
    { value: 'friday', label: 'Friday' },
    { value: 'saturday', label: 'Saturday' },
    { value: 'sunday', label: 'Sunday' },
];

const form = reactive({
    delivery_frequency: '',
    delivery_days: [] as string[],
    preferred_delivery_time: '',
    delivery_address_id: '',
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const handleSubmit = () => {
    if (!props.subscription) return;

    processing.value = true;

    router.patch(route('subscriptions.update', props.subscription.id), form, {
        onSuccess: () => {
            processing.value = false;
            emit('updated');
        },
        onError: () => {
            processing.value = false;
        },
    });
};

// Load addresses when modal opens
watch(
    () => props.show,
    (show) => {
        if (show && props.subscription) {
            // Initialize form with current subscription data
            form.delivery_frequency = props.subscription.delivery_frequency;
            form.delivery_days = [...props.subscription.delivery_days];
            form.preferred_delivery_time = props.subscription.preferred_delivery_time;
            form.delivery_address_id = props.subscription.delivery_address_id.toString();

            // Load user addresses
            router.get(
                route('addresses.index'),
                {},
                {
                    onSuccess: (page: any) => {
                        addresses.value = page.props.addresses || [];
                    },
                },
            );
        }
    },
);
</script>
