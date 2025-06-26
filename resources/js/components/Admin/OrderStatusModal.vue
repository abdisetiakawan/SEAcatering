<template>
    <Modal :show="show" @close="closeModal" max-width="lg">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Update Order Status</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Order Info -->
            <div v-if="order" class="mb-6 rounded-lg bg-gray-50 p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-medium text-gray-900">Order #{{ order.id }}</h3>
                        <p class="text-sm text-gray-600">{{ order.subscription.user.name }}</p>
                        <p class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900">Rp {{ formatPrice(order.total_amount) }}</p>
                        <p class="text-sm" :class="getStatusClass(order.status)">
                            {{ getStatusText(order.status) }}
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitUpdate">
                <!-- Current Status -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Current Status</label>
                    <div class="rounded-md bg-gray-100 p-3">
                        <span class="font-medium" :class="getStatusClass(order?.status)">
                            {{ getStatusText(order?.status) }}
                        </span>
                    </div>
                </div>

                <!-- New Status -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">New Status</label>
                    <select v-model="form.status" class="w-full rounded-md border border-gray-300 px-3 py-2" required>
                        <option value="">Select new status...</option>
                        <option v-for="status in getAvailableStatuses(order?.status)" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <!-- Driver Assignment (for preparing/delivering status) -->
                <div v-if="form.status === 'preparing' || form.status === 'delivering'" class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        {{ form.status === 'preparing' ? 'Assign Chef' : 'Assign Driver' }}
                    </label>
                    <select v-model="form.assignedTo" class="w-full rounded-md border border-gray-300 px-3 py-2">
                        <option value="">Select {{ form.status === 'preparing' ? 'chef' : 'driver' }}...</option>
                        <option v-for="user in getAssignableUsers(form.status)" :key="user.id" :value="user.id">
                            {{ user.name }} {{ user.phone ? `(${user.phone})` : '' }}
                        </option>
                    </select>
                </div>

                <!-- Delivery Time (for delivering status) -->
                <div v-if="form.status === 'delivering'" class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Estimated Delivery Time</label>
                    <input type="datetime-local" v-model="form.estimatedDeliveryTime" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                </div>

                <!-- Cancellation Reason (for cancelled status) -->
                <div v-if="form.status === 'cancelled'" class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Cancellation Reason</label>
                    <select v-model="form.cancellationReason" class="mb-2 w-full rounded-md border border-gray-300 px-3 py-2">
                        <option value="">Select reason...</option>
                        <option value="customer_request">Customer Request</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="payment_failed">Payment Failed</option>
                        <option value="delivery_issue">Delivery Issue</option>
                        <option value="other">Other</option>
                    </select>
                    <textarea
                        v-if="form.cancellationReason === 'other' || form.customCancellationReason"
                        v-model="form.customCancellationReason"
                        rows="3"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Enter cancellation reason..."
                    ></textarea>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Notes (Optional)</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                        placeholder="Add any additional notes..."
                    ></textarea>
                </div>

                <!-- Send Notification -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" v-model="form.sendNotification" class="mr-2 text-blue-600" />
                        <span class="text-sm text-gray-700">Send notification to customer</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="processing || !form.status"
                        class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ processing ? 'Updating...' : 'Update Status' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivering' | 'delivered' | 'cancelled';

interface User {
    id: number;
    name: string;
    phone?: string;
}

interface Order {
    id: number;
    order_number: string;
    delivery_date: string;
    delivery_time_slot: string;
    total_amount: number;
    status: OrderStatus;
    created_at: string;
    subscription: {
        user: {
            name: string;
            email: string;
        };
        meal_plan: {
            name: string;
        };
    };
}

interface StatusOption {
    value: OrderStatus;
    label: string;
}

interface OrderStatusForm {
    status: OrderStatus | '';
    assignedTo: number | string;
    estimatedDeliveryTime: string;
    cancellationReason: string;
    customCancellationReason: string;
    notes: string;
    sendNotification: boolean;
}

interface Props {
    show: boolean;
    order?: Order;
    drivers?: User[];
    chefs?: User[];
}

interface Emits {
    (e: 'close'): void;
    (e: 'updated'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const processing = ref<boolean>(false);

const form = ref<OrderStatusForm>({
    status: '',
    assignedTo: '',
    estimatedDeliveryTime: '',
    cancellationReason: '',
    customCancellationReason: '',
    notes: '',
    sendNotification: true,
});

const statusOptions: StatusOption[] = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'preparing', label: 'Preparing' },
    { value: 'ready', label: 'Ready for Delivery' },
    { value: 'delivering', label: 'Out for Delivery' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'cancelled', label: 'Cancelled' },
];

const getStatusText = (status?: OrderStatus): string => {
    if (!status) return '';
    const option = statusOptions.find((opt) => opt.value === status);
    return option ? option.label : status;
};

const getStatusClass = (status?: OrderStatus): string => {
    if (!status) return 'text-gray-600';

    const classes: Record<OrderStatus, string> = {
        pending: 'text-yellow-600',
        confirmed: 'text-blue-600',
        preparing: 'text-purple-600',
        ready: 'text-indigo-600',
        delivering: 'text-orange-600',
        delivered: 'text-green-600',
        cancelled: 'text-red-600',
    };
    return classes[status] || 'text-gray-600';
};

const getAvailableStatuses = (currentStatus?: OrderStatus): StatusOption[] => {
    if (!currentStatus) return [];

    const statusFlow: Record<OrderStatus, OrderStatus[]> = {
        pending: ['confirmed', 'cancelled'],
        confirmed: ['preparing', 'cancelled'],
        preparing: ['ready', 'cancelled'],
        ready: ['delivering', 'cancelled'],
        delivering: ['delivered', 'cancelled'],
        delivered: [],
        cancelled: [],
    };

    const availableStatuses = statusFlow[currentStatus] || [];
    return statusOptions.filter((option) => availableStatuses.includes(option.value));
};

const getAssignableUsers = (status: OrderStatus | ''): User[] => {
    if (status === 'preparing') {
        return props.chefs || [];
    } else if (status === 'delivering') {
        return props.drivers || [];
    }
    return [];
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const submitUpdate = (): void => {
    processing.value = true;

    router.post(`/admin/orders/${props.order?.id}/update-status`, form.value, {
        onSuccess: () => {
            emit('updated');
            closeModal();
        },
        onError: () => {
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const closeModal = (): void => {
    form.value = {
        status: '',
        assignedTo: '',
        estimatedDeliveryTime: '',
        cancellationReason: '',
        customCancellationReason: '',
        notes: '',
        sendNotification: true,
    };
    emit('close');
};

watch(
    () => props.show,
    (newVal: boolean) => {
        if (!newVal) {
            closeModal();
        }
    },
);
</script>
