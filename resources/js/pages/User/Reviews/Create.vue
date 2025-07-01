<template>
    <UserLayout>
        <Head :title="`Review ${menuItem.name}`" />

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <Link :href="route('user.orders.index')" class="text-gray-500 hover:text-gray-700"> Orders </Link>
                            </li>
                            <li>
                                <ChevronRight class="h-4 w-4 text-gray-400" />
                            </li>
                            <li>
                                <Link :href="route('user.orders.show', order.id)" class="text-gray-500 hover:text-gray-700">
                                    {{ order.order_number }}
                                </Link>
                            </li>
                            <li>
                                <ChevronRight class="h-4 w-4 text-gray-400" />
                            </li>
                            <li class="text-gray-900">Review</li>
                        </ol>
                    </nav>
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">Write a Review</h1>
                    <p class="mt-1 text-gray-600">Share your experience with {{ menuItem.name }}</p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Review Form -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Your Review</h2>
                            </div>
                            <div class="p-6">
                                <!-- Menu Item Info -->
                                <div class="mb-6 flex items-center space-x-4 rounded-lg border p-4">
                                    <img :src="getImageUrl(menuItem.image)" :alt="menuItem.name" class="h-16 w-16 rounded-lg object-cover" />
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">{{ menuItem.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ menuItem.category?.name }}</p>
                                        <div class="mt-1 flex items-center space-x-4">
                                            <span class="text-sm text-gray-600">Qty: {{ orderItem.quantity }}</span>
                                            <span class="text-sm font-semibold text-green-600">{{ formatCurrency(orderItem.total_price) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Form -->
                                <form @submit.prevent="submitReview" class="space-y-6">
                                    <!-- Rating -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700">Rating *</label>
                                        <div class="flex items-center space-x-1">
                                            <button v-for="i in 5" :key="i" type="button" @click="form.rating = i" class="focus:outline-none">
                                                <Star
                                                    :class="[
                                                        'h-8 w-8 transition-colors',
                                                        i <= form.rating ? 'fill-current text-yellow-400' : 'text-gray-300 hover:text-yellow-200',
                                                    ]"
                                                />
                                            </button>
                                        </div>
                                        <div v-if="errors.rating" class="mt-1 text-sm text-red-600">
                                            {{ errors.rating }}
                                        </div>
                                    </div>

                                    <!-- Comment -->
                                    <div>
                                        <label for="comment" class="mb-2 block text-sm font-medium text-gray-700"> Comment </label>
                                        <textarea
                                            id="comment"
                                            v-model="form.comment"
                                            rows="4"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                            placeholder="Tell us about your experience with this item..."
                                        ></textarea>
                                        <div v-if="errors.comment" class="mt-1 text-sm text-red-600">
                                            {{ errors.comment }}
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex items-center justify-end space-x-4">
                                        <Button type="button" variant="outline" @click="router.visit(route('user.orders.show', order.id))">
                                            Cancel
                                        </Button>
                                        <Button type="submit" :disabled="isSubmitting || form.rating === 0" class="bg-green-600 hover:bg-green-700">
                                            {{ isSubmitting ? 'Submitting...' : 'Submit Review' }}
                                        </Button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Order Info -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900">Order Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Order Number:</span>
                                        <p class="text-sm text-gray-600">{{ order.order_number }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Delivery Date:</span>
                                        <p class="text-sm text-gray-600">{{ formatDate(order.delivery_date) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Status:</span>
                                        <OrderStatusBadge :status="order.status" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Item Stats -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900">Item Rating</h3>
                            </div>
                            <div class="p-6">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-900">
                                        {{ menuItem.average_rating }}
                                    </div>
                                    <div class="mt-1 flex justify-center">
                                        <Star
                                            v-for="i in 5"
                                            :key="i"
                                            :class="[
                                                'h-5 w-5',
                                                i <= Math.round(menuItem.average_rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300',
                                            ]"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Based on {{ menuItem.total_reviews }} {{ menuItem.total_reviews === 1 ? 'review' : 'reviews' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Reviews -->
                        <div v-if="recentReviews.length > 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Reviews</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div v-for="review in recentReviews" :key="review.id" class="border-b border-gray-100 pb-4 last:border-b-0">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex">
                                                <Star
                                                    v-for="i in 5"
                                                    :key="i"
                                                    :class="['h-4 w-4', i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300']"
                                                />
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ review.user.name }}</span>
                                        </div>
                                        <p v-if="review.comment" class="mt-2 text-sm text-gray-700">{{ review.comment }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ review.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import OrderStatusBadge from '@/components/User/OrderStatusBadge.vue';
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronRight, Star } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface Order {
    id: number;
    order_number: string;
    delivery_date: string;
    status: string;
}

interface MenuItem {
    id: number;
    name: string;
    description: string;
    image: string;
    price: number;
    category?: {
        id: number;
        name: string;
    };
    average_rating: number;
    total_reviews: number;
}

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
}

interface Review {
    id: number;
    rating: number;
    comment?: string;
    created_at: string;
    user: {
        name: string;
    };
}

const props = defineProps<{
    order: Order;
    menuItem: MenuItem;
    orderItem: OrderItem;
    recentReviews: Review[];
}>();

const form = reactive({
    order_id: props.order.id,
    menu_item_id: props.menuItem.id,
    rating: 0,
    comment: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

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

const getImageUrl = (image: string) => {
    if (!image) return '/placeholder.svg?height=64&width=64';
    return image.startsWith('http') ? image : `/storage/${image}`;
};

const route = (name: string, params?: any) => {
    const routes: Record<string, string> = {
        'user.orders.index': '/user/orders',
        'user.orders.show': `/user/orders/${params}`,
        'user.reviews.store': '/user/reviews',
    };
    return routes[name] || '#';
};

const submitReview = async () => {
    if (form.rating === 0) {
        errors.value.rating = 'Please select a rating';
        return;
    }

    isSubmitting.value = true;
    errors.value = {};

    try {
        await router.post(route('user.reviews.store'), form, {
            preserveScroll: true,
            onError: (responseErrors) => {
                errors.value = responseErrors;
            },
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
