<template>
    <UserLayout>
        <Head title="Shopping Cart" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                                <p class="mt-1 text-gray-600">Review your items before checkout</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">{{ cartStats.total_items }} items</p>
                                    <p class="text-lg font-semibold text-green-600">{{ formatCurrency(cartTotal) }}</p>
                                </div>
                                <Button @click="clearCart" variant="outline" size="sm" v-if="cartItems.length > 0">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Clear Cart
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4" v-if="cartItems.length > 0">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600">{{ cartStats.total_quantity }}</div>
                        <div class="text-sm text-gray-600">Total Quantity</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(cartStats.subtotal) }}</div>
                        <div class="text-sm text-gray-600">Subtotal</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(deliveryFee) }}</div>
                        <div class="text-sm text-gray-600">Delivery Fee</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-2xl font-bold text-purple-600">{{ cartStats.total_calories }}</div>
                        <div class="text-sm text-gray-600">Total Calories</div>
                    </div>
                </div>

                <!-- Cart Content -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Cart Items</h2>
                            </div>

                            <!-- Cart Items List -->
                            <div v-if="cartItems.length > 0" class="divide-y divide-gray-200">
                                <CartItemCard
                                    v-for="item in cartItems"
                                    :key="item.id"
                                    :item="item"
                                    @update-quantity="updateQuantity"
                                    @remove-item="removeItem"
                                />
                            </div>

                            <!-- Empty Cart State -->
                            <div v-else class="p-12 text-center">
                                <ShoppingCart class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                                <h3 class="mb-2 text-lg font-medium text-gray-900">Your cart is empty</h3>
                                <p class="mb-6 text-gray-500">Start adding some delicious meals to your cart</p>
                                <Link
                                    :href="route('user.menu.index')"
                                    class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                                >
                                    <Plus class="mr-2 h-4 w-4" />
                                    Browse Menu
                                </Link>
                            </div>
                        </div>

                        <!-- Recommended Items -->
                        <div v-if="recommendedItems.length > 0" class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">You might also like</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <RecommendedItemCard v-for="item in recommendedItems" :key="item.id" :item="item" @add-to-cart="addToCart" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-6">
                            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="border-b border-gray-200 p-6">
                                    <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <!-- Subtotal -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Subtotal ({{ cartStats.total_quantity }} items)</span>
                                            <span class="font-medium">{{ formatCurrency(cartStats.subtotal) }}</span>
                                        </div>

                                        <!-- Delivery Fee -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Delivery Fee</span>
                                            <span class="font-medium">{{ formatCurrency(deliveryFee) }}</span>
                                        </div>

                                        <!-- Discount -->
                                        <div v-if="discount > 0" class="flex items-center justify-between text-green-600">
                                            <span>Discount</span>
                                            <span class="font-medium">-{{ formatCurrency(discount) }}</span>
                                        </div>

                                        <!-- Tax -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Tax (11%)</span>
                                            <span class="font-medium">{{ formatCurrency(tax) }}</span>
                                        </div>

                                        <div class="border-t pt-4">
                                            <div class="flex items-center justify-between text-lg font-semibold">
                                                <span>Total</span>
                                                <span class="text-green-600">{{ formatCurrency(cartTotal) }}</span>
                                            </div>
                                        </div>

                                        <!-- Promo Code (Coming Soon) -->
                                        <div class="border-t pt-4 opacity-50">
                                            <div class="flex space-x-2">
                                                <Input v-model="promoCode" placeholder="Enter promo code" class="flex-1" disabled />
                                                <Button variant="outline" size="sm" disabled> Coming Soon </Button>
                                            </div>
                                        </div>

                                        <!-- Checkout Button -->
                                        <div class="pt-4">
                                            <Button
                                                @click="proceedToCheckout"
                                                class="w-full bg-green-600 hover:bg-green-700"
                                                :disabled="cartItems.length === 0 || isProcessing"
                                            >
                                                <CreditCard class="mr-2 h-4 w-4" />
                                                {{ isProcessing ? 'Processing...' : 'Proceed to Checkout' }}
                                            </Button>
                                        </div>

                                        <!-- Continue Shopping -->
                                        <div class="pt-2">
                                            <Link
                                                :href="route('user.menu.index')"
                                                class="flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50"
                                            >
                                                <ArrowLeft class="mr-2 h-4 w-4" />
                                                Continue Shopping
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Info -->
                            <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Delivery Information</h3>
                                    <div class="space-y-3 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <Truck class="mr-2 h-4 w-4 text-green-600" />
                                            <span>Free delivery for orders over {{ formatCurrency(freeDeliveryThreshold) }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <Clock class="mr-2 h-4 w-4 text-blue-600" />
                                            <span>Delivery within 30-45 minutes</span>
                                        </div>
                                        <div class="flex items-center">
                                            <Shield class="mr-2 h-4 w-4 text-purple-600" />
                                            <span>Fresh ingredients guaranteed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clear Cart Confirmation Modal -->
        <ConfirmationModal
            :show="showClearModal"
            title="Clear Cart"
            message="Are you sure you want to remove all items from your cart? This action cannot be undone."
            @confirm="confirmClearCart"
            @cancel="showClearModal = false"
        />

        <!-- Success Modal -->
        <SuccessModal :show="showSuccessModal" :title="successTitle" :message="successMessage" @close="showSuccessModal = false" />
    </UserLayout>
</template>

<script setup lang="ts">
import CartItemCard from '@/components/User/CartItemCard.vue';
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import RecommendedItemCard from '@/components/User/RecommendedItemCard.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Clock, CreditCard, Plus, Shield, ShoppingCart, Trash2, Truck } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface CartItem {
    id: number;
    menu_item_id: number;
    quantity: number;
    price: number;
    subtotal: number;
    total_calories: number;
    total_protein: number;
    created_at: string;
    menu_item: {
        id: number;
        name: string;
        description: string;
        image: string;
        category: string;
        calories: number;
        protein: number;
        carbs: number;
        fat: number;
        is_available: boolean;
    };
}

interface RecommendedItem {
    id: number;
    name: string;
    description: string;
    price: number;
    image: string;
    category: string;
    rating: number;
    calories: number;
}

interface CartStats {
    total_items: number;
    total_quantity: number;
    subtotal: number;
    total_calories: number;
    total_protein: number;
}

const props = defineProps<{
    cartItems: CartItem[];
    recommendedItems: RecommendedItem[];
    deliveryFee: number;
    freeDeliveryThreshold: number;
    cartStats: CartStats;
}>();

// State
const promoCode = ref('');
const showClearModal = ref(false);
const showSuccessModal = ref(false);
const successTitle = ref('');
const successMessage = ref('');
const isProcessing = ref(false);

// Computed
const discount = computed(() => {
    // Calculate discount based on promo code or other conditions
    return 0;
});

const tax = computed(() => {
    return Math.round((props.cartStats.subtotal + props.deliveryFee - discount.value) * 0.11);
});

const cartTotal = computed(() => {
    return props.cartStats.subtotal + props.deliveryFee + tax.value - discount.value;
});

// Methods
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const updateQuantity = (cartId: number, quantity: number) => {
    if (quantity <= 0) {
        removeItem(cartId);
        return;
    }

    router.patch(
        route('user.cart.update', cartId),
        {
            quantity: quantity,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Cart updated successfully
            },
            onError: (errors) => {
                console.error('Failed to update cart:', errors);
            },
        },
    );
};

const removeItem = (cartId: number) => {
    router.delete(route('user.cart.remove', cartId), {
        preserveScroll: true,
        onSuccess: () => {
            successTitle.value = 'Item Removed';
            successMessage.value = 'Item has been removed from your cart.';
            showSuccessModal.value = true;
        },
    });
};

const clearCart = () => {
    showClearModal.value = true;
};

const confirmClearCart = () => {
    router.delete(route('user.cart.clear'), {
        onSuccess: () => {
            showClearModal.value = false;
            successTitle.value = 'Cart Cleared';
            successMessage.value = 'All items have been removed from your cart.';
            showSuccessModal.value = true;
        },
    });
};

const addToCart = (item: RecommendedItem) => {
    router.post(
        route('user.cart.add'),
        {
            menu_item_id: item.id,
            quantity: 1,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                successTitle.value = 'Added to Cart';
                successMessage.value = `${item.name} has been added to your cart.`;
                showSuccessModal.value = true;
            },
        },
    );
};

const proceedToCheckout = () => {
    if (props.cartItems.length === 0) return;

    isProcessing.value = true;
    router.visit(route('user.checkout.index'), {
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};
</script>
