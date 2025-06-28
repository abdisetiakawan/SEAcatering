<template>
    <UserLayout>
        <Head title="Invoice" />

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Print Header (hidden on screen) -->
                <div class="mb-8 hidden print:block">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-gray-900">SEA CATERING</h1>
                        <p class="text-gray-600">Premium Meal Delivery Service</p>
                        <p class="text-sm text-gray-500">Jl. Sudirman No. 123, Jakarta | Tel: (021) 123-4567</p>
                    </div>
                </div>

                <!-- Invoice Content -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg print:shadow-none">
                    <!-- Header -->
                    <div class="border-b border-gray-200 p-6 print:border-b-2">
                        <div class="flex items-center justify-between print:block">
                            <div class="print:mb-6 print:text-center">
                                <h1 class="text-2xl font-bold text-gray-900 print:text-3xl">INVOICE</h1>
                                <p class="mt-1 text-gray-600">Order #{{ order.order_number }}</p>
                            </div>
                            <div class="text-right print:text-left">
                                <div class="flex space-x-2 print:hidden">
                                    <Button @click="printInvoice" variant="outline">
                                        <Printer class="mr-2 h-4 w-4" />
                                        Print
                                    </Button>
                                    <Button @click="downloadPDF" variant="outline">
                                        <Download class="mr-2 h-4 w-4" />
                                        Download PDF
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 print:grid-cols-2">
                            <!-- Company Info -->
                            <div class="print:hidden">
                                <h3 class="mb-3 text-lg font-semibold text-gray-900">From:</h3>
                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-900">SEA Catering</p>
                                    <p>Jl. Sudirman No. 123</p>
                                    <p>Jakarta Pusat, 10110</p>
                                    <p>Indonesia</p>
                                    <p class="mt-2">
                                        <span class="font-medium">Phone:</span> (021) 123-4567<br />
                                        <span class="font-medium">Email:</span> info@seacatering.com
                                    </p>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-gray-900">Bill To:</h3>
                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-900">{{ order.customer_name }}</p>
                                    <p>{{ order.customer_email }}</p>
                                    <div v-if="order.delivery_address" class="mt-2">
                                        <p class="font-medium text-gray-700">Delivery Address:</p>
                                        <p>{{ order.delivery_address.recipient_name }}</p>
                                        <p>{{ order.delivery_address.phone_number }}</p>
                                        <p>{{ order.delivery_address.full_address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Meta -->
                        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3 print:grid-cols-3">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Invoice Date:</p>
                                <p class="text-sm text-gray-900">{{ formatDate(order.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Delivery Date:</p>
                                <p class="text-sm text-gray-900">{{ formatDate(order.delivery_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Order Type:</p>
                                <Badge :variant="order.order_type === 'subscription' ? 'default' : 'secondary'">
                                    {{ order.order_type === 'subscription' ? 'Subscription' : 'Direct Order' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mt-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 print:bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Item</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Qty</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Price</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="item in order.order_items" :key="item.id">
                                            <td class="px-4 py-4">
                                                <div class="flex items-center print:block">
                                                    <img
                                                        v-if="item.menu_item.image"
                                                        :src="item.menu_item.image"
                                                        :alt="item.menu_item.name"
                                                        class="mr-4 h-12 w-12 rounded-lg object-cover print:hidden"
                                                    />
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ item.menu_item.name }}</p>
                                                        <p class="text-sm text-gray-500">{{ item.menu_item.category }}</p>
                                                        <div
                                                            v-if="item.menu_item.calories || item.menu_item.protein"
                                                            class="mt-1 text-xs text-gray-400"
                                                        >
                                                            <span v-if="item.menu_item.calories">{{ item.menu_item.calories }} cal</span>
                                                            <span v-if="item.menu_item.protein" class="ml-2"
                                                                >{{ item.menu_item.protein }}g protein</span
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-center text-sm text-gray-900">{{ item.quantity }}</td>
                                            <td class="px-4 py-4 text-right text-sm text-gray-900">{{ formatCurrency(item.price) }}</td>
                                            <td class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                                {{ formatCurrency(item.total_price) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="mt-8 flex justify-end">
                            <div class="w-full max-w-md">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">{{ formatCurrency(order.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Delivery Fee:</span>
                                        <span class="font-medium" :class="order.delivery_fee === 0 ? 'text-green-600' : ''">
                                            {{ order.delivery_fee === 0 ? 'FREE' : formatCurrency(order.delivery_fee) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tax (11%):</span>
                                        <span class="font-medium">{{ formatCurrency(order.tax_amount) }}</span>
                                    </div>
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between text-lg font-semibold">
                                            <span>Total:</span>
                                            <span class="text-green-600">{{ formatCurrency(order.total_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Status Info -->
                        <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 print:grid-cols-2">
                            <div>
                                <h4 class="mb-2 text-sm font-medium text-gray-700">Order Status:</h4>
                                <Badge :variant="getStatusVariant(order.status)">
                                    {{ getStatusLabel(order.status) }}
                                </Badge>
                            </div>
                            <div>
                                <h4 class="mb-2 text-sm font-medium text-gray-700">Payment Status:</h4>
                                <Badge :variant="order.payment_status === 'completed' ? 'default' : 'secondary'">
                                    {{ order.payment_status === 'completed' ? 'Paid' : 'Pending' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div v-if="order.special_instructions" class="mt-6">
                            <h4 class="mb-2 text-sm font-medium text-gray-700">Special Instructions:</h4>
                            <p class="rounded-md bg-gray-50 p-3 text-sm text-gray-600">{{ order.special_instructions }}</p>
                        </div>

                        <!-- Footer -->
                        <div class="mt-8 border-t border-gray-200 pt-6 print:border-t-2">
                            <div class="text-center text-sm text-gray-500">
                                <p class="font-medium">Thank you for choosing SEA Catering!</p>
                                <p class="mt-1">For any questions about this invoice, please contact us at info@seacatering.com</p>
                                <p class="mt-2 print:mt-4"><span class="font-medium">Generated on:</span> {{ formatDateTime(new Date()) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6 print:hidden">
                    <Link :href="route('user.orders.show', order.id)" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Order Details
                    </Link>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Printer } from 'lucide-vue-next';

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    order_source: string;
    delivery_date: string;
    delivery_time_slot: string;
    subtotal: number;
    tax_amount: number;
    delivery_fee: number;
    total_amount: number;
    special_instructions?: string;
    status: string;
    payment_status: string;
    created_at: string;
    customer_name: string;
    customer_email: string;
    delivery_address?: {
        recipient_name: string;
        phone_number: string;
        full_address: string;
    };
    order_items: Array<{
        id: number;
        menu_item: {
            name: string;
            category: string;
            image?: string;
            calories?: number;
            protein?: number;
        };
        quantity: number;
        price: number;
        total_price: number;
    }>;
    payment?: any;
}

const props = defineProps<{
    order: Order;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date: Date) => {
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        confirmed: 'default',
        preparing: 'outline',
        ready: 'default',
        out_for_delivery: 'default',
        delivered: 'default',
        cancelled: 'destructive',
    };
    return variants[status] || 'default';
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

const printInvoice = () => {
    window.print();
};

const downloadPDF = () => {
    // This would typically integrate with a PDF generation service
    // For now, we'll just trigger the print dialog
    window.print();
};
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    .print\:block,
    .print\:block * {
        visibility: visible;
    }

    .print\:hidden {
        display: none !important;
    }

    .print\:shadow-none {
        box-shadow: none !important;
    }

    .print\:border-b-2 {
        border-bottom-width: 2px !important;
    }

    .print\:border-t-2 {
        border-top-width: 2px !important;
    }

    .print\:bg-gray-100 {
        background-color: #f3f4f6 !important;
    }

    .print\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .print\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }

    .print\:text-center {
        text-align: center !important;
    }

    .print\:text-left {
        text-align: left !important;
    }

    .print\:text-3xl {
        font-size: 1.875rem !important;
        line-height: 2.25rem !important;
    }

    .print\:mb-6 {
        margin-bottom: 1.5rem !important;
    }

    .print\:mt-4 {
        margin-top: 1rem !important;
    }
}
</style>
