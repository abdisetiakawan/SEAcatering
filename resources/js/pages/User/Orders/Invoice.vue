<template>
    <div class="min-h-screen bg-white">
        <!-- Print Header - Hidden on screen -->
        <div class="print-only mb-8">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900">SEA CATERING</h1>
                <p class="text-gray-600">Premium Meal Delivery Service</p>
            </div>
        </div>

        <!-- Screen Header - Hidden on print -->
        <div class="no-print sticky top-0 z-10 border-b border-gray-200 bg-white px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Invoice</h1>
                        <p class="text-sm text-gray-500">{{ order.order_number }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Button @click="downloadPDF" variant="outline" size="sm">
                        <Download class="mr-2 h-4 w-4" />
                        Download PDF
                    </Button>
                    <Button @click="printInvoice" size="sm">
                        <Printer class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8" id="invoice-content">
            <!-- Company Header -->
            <div class="mb-8 flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-600">
                            <span class="text-lg font-bold text-white">SC</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">SEA CATERING</h1>
                            <p class="text-gray-600">Premium Meal Delivery Service</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-600">
                        <p>Jl. Sudirman No. 123, Jakarta Pusat</p>
                        <p>Jakarta 10220, Indonesia</p>
                        <p>Phone: +62 21 1234 5678</p>
                        <p>Email: info@seacatering.com</p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-gray-900">INVOICE</h2>
                    <div class="mt-2 text-sm text-gray-600">
                        <p><span class="font-medium">Invoice #:</span> {{ order.order_number }}</p>
                        <p><span class="font-medium">Date:</span> {{ formatDate(order.created_at) }}</p>
                        <p><span class="font-medium">Due Date:</span> {{ formatDate(order.delivery_date) }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="mb-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Bill To:</h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium text-gray-900">{{ order.user.name }}</p>
                        <p>{{ order.user.email }}</p>
                        <p v-if="order.user.phone">{{ order.user.phone }}</p>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Deliver To:</h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium text-gray-900">{{ order.delivery_address.recipient_name }}</p>
                        <p>{{ order.delivery_address.phone_number }}</p>
                        <p>{{ fullAddress }}</p>
                        <p v-if="order.delivery_address.delivery_instructions" class="mt-1 italic">
                            Note: {{ order.delivery_address.delivery_instructions }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="mb-8">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Details</h3>
                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                    <div>
                        <span class="font-medium text-gray-700">Delivery Date:</span>
                        <p class="text-gray-600">{{ formatDate(order.delivery_date) }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Delivery Time:</span>
                        <p class="text-gray-600">{{ order.delivery_time_slot }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Status:</span>
                        <Badge :variant="getStatusVariant(order.status)" class="ml-1">
                            {{ getStatusLabel(order.status) }}
                        </Badge>
                    </div>
                </div>
                <div v-if="order.special_instructions" class="mt-4">
                    <span class="font-medium text-gray-700">Special Instructions:</span>
                    <p class="text-gray-600">{{ order.special_instructions }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-8 overflow-hidden rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="item in order.order_items" :key="item.id">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img
                                        :src="item.menu_item.image?.startsWith('http') ? item.menu_item.image : '/storage/' + item.menu_item.image"
                                        :alt="item.menu_item.name"
                                        class="mr-4 h-10 w-10 rounded-lg object-cover"
                                    />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ item.menu_item.name }}</div>
                                        <div class="text-sm text-gray-500">{{ item.menu_item.category?.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-900">
                                {{ item.quantity }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-900">
                                {{ formatCurrency(item.unit_price) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                {{ formatCurrency(item.total_price) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="flex justify-end">
                <div class="w-full max-w-sm">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">{{ formatCurrency(order.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery Fee:</span>
                            <span class="font-medium">{{ formatCurrency(order.delivery_fee) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (11%):</span>
                            <span class="font-medium">{{ formatCurrency(order.tax_amount) }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span class="text-green-600">{{ formatCurrency(order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="mt-8 border-t border-gray-200 pt-8">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Payment Information</h3>
                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                    <div>
                        <span class="font-medium text-gray-700">Payment Status:</span>
                        <Badge :variant="getPaymentStatusVariant(order.payment_status)" class="ml-2">
                            {{ getPaymentStatusLabel(order.payment_status) }}
                        </Badge>
                    </div>
                    <div v-if="order.payment">
                        <span class="font-medium text-gray-700">Payment Method:</span>
                        <span class="ml-2 text-gray-600">{{ order.payment.payment_method }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
                <p>Thank you for choosing SEA Catering!</p>
                <p class="mt-2">For any questions about this invoice, please contact us at info@seacatering.com</p>
                <p class="mt-4 text-xs">This is a computer-generated invoice and does not require a signature.</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Download, Printer } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: string; // Ubah dari price ke unit_price dan tipe string
    total_price: string; // Ubah dari total ke total_price dan tipe string
    menu_item: {
        id: number;
        name: string;
        image?: string;
        category?: {
            name: string;
        } | null;
    };
}

interface Order {
    id: number;
    order_number: string;
    delivery_date: string;
    delivery_time_slot: string;
    special_instructions?: string;
    subtotal: string;
    delivery_fee: string;
    tax_amount: string;
    total_amount: string;
    status: string;
    payment_status: string;
    created_at: string;
    user: {
        name: string;
        email: string;
        phone?: string;
    };
    delivery_address: {
        recipient_name: string;
        phone_number: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        delivery_instructions?: string;
    };
    order_items: OrderItem[];
    payment?: {
        payment_method: string;
    } | null; // Bisa null
}

const props = defineProps<{
    order: Order;
}>();

const formatCurrency = (amount: string | number) => {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numAmount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' | null | undefined => {
    const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        confirmed: 'default',
        preparing: 'default',
        ready: 'default',
        out_for_delivery: 'default',
        delivered: 'default',
        cancelled: 'destructive',
    };
    return variants[status] || 'secondary';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        preparing: 'Preparing',
        ready: 'Ready',
        out_for_delivery: 'Out for Delivery',
        delivered: 'Delivered',
        cancelled: 'Cancelled',
    };
    return labels[status] || status;
};

const getPaymentStatusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' | null | undefined => {
    const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        paid: 'default',
        failed: 'destructive',
        refunded: 'secondary',
    };
    return variants[status] || 'secondary';
};
const fullAddress = computed(() => {
    const addr = props.order.delivery_address;
    return `${addr.address_line_1}${addr.address_line_2 ? ', ' + addr.address_line_2 : ''}, ${addr.city}, ${addr.state} ${addr.postal_code}, ${addr.country}`;
});

const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Pending',
        paid: 'Paid',
        failed: 'Failed',
        refunded: 'Refunded',
    };
    return labels[status] || status;
};

const printInvoice = () => {
    window.print();
};

const downloadPDF = () => {
    // This would typically integrate with a PDF generation service
    // For now, we'll trigger the browser's print dialog with PDF option
    window.print();
};

// Add print styles
onMounted(() => {
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .page-break {
                page-break-before: always;
            }
        }
        @media screen {
            .print-only {
                display: none;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>
