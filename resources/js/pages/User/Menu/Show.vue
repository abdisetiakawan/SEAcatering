<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Breadcrumb -->
        <div class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-2 py-4 text-sm text-gray-600">
                    <Link :href="route('dashboard')" class="hover:text-green-600">Dashboard</Link>
                    <ChevronRight class="h-4 w-4" />
                    <Link :href="route('user.menu.index')" class="hover:text-green-600">Menu</Link>
                    <ChevronRight class="h-4 w-4" />
                    <span class="text-gray-900">{{ menuItem.name }}</span>
                </div>
            </div>
        </div>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Product Image -->
                    <div class="space-y-4">
                        <div class="aspect-square overflow-hidden rounded-lg bg-white shadow-sm">
                            <img
                                :src="menuItem.image.startsWith('http') ? menuItem.image : '/storage/' + menuItem.image"
                                :alt="menuItem.name"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Image Gallery (if multiple images) -->
                        <div class="grid grid-cols-4 gap-2">
                            <div v-for="i in 4" :key="i" class="aspect-square cursor-pointer overflow-hidden rounded-lg bg-gray-100 hover:opacity-75">
                                <img
                                    :src="menuItem.image.startsWith('http') ? menuItem.image : '/storage/' + menuItem.image"
                                    :alt="`${menuItem.name} view ${i}`"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-6">
                        <!-- Header -->
                        <div>
                            <div class="mb-2 flex items-center space-x-2">
                                <Badge variant="outline" class="text-xs">
                                    {{ menuItem.category }}
                                </Badge>
                                <Badge v-if="menuItem.is_popular" class="bg-orange-100 text-xs text-orange-800"> Popular </Badge>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ menuItem.name }}</h1>
                            <p class="mt-2 text-lg text-gray-600">{{ menuItem.description }}</p>
                        </div>

                        <!-- Rating -->
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-1">
                                <div class="flex items-center">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        :class="[
                                            'h-5 w-5',
                                            i <= Math.floor(menuItem.average_rating) ? 'fill-current text-yellow-400' : 'text-gray-300',
                                        ]"
                                    />
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ menuItem.average_rating?.toFixed(1) || '0.0' }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-600"> ({{ menuItem.review_count || 0 }} reviews) </span>
                        </div>

                        <!-- Price -->
                        <div class="flex items-center space-x-4">
                            <span class="text-3xl font-bold text-green-600">
                                {{ formatCurrency(menuItem.price) }}
                            </span>
                            <span v-if="menuItem.original_price" class="text-lg text-gray-500 line-through">
                                {{ formatCurrency(menuItem.original_price) }}
                            </span>
                        </div>

                        <!-- Nutrition Info -->
                        <div class="rounded-lg bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Nutrition Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-center space-x-2">
                                    <Zap class="h-5 w-5 text-orange-500" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ menuItem.calories }}</div>
                                        <div class="text-xs text-gray-600">Calories</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Activity class="h-5 w-5 text-blue-500" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ menuItem.protein }}g</div>
                                        <div class="text-xs text-gray-600">Protein</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Wheat class="h-5 w-5 text-yellow-500" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ menuItem.carbs }}g</div>
                                        <div class="text-xs text-gray-600">Carbs</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Droplets class="h-5 w-5 text-green-500" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ menuItem.fat }}g</div>
                                        <div class="text-xs text-gray-600">Fat</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ingredients -->
                        <div class="rounded-lg bg-white p-6 shadow-sm">
                            <h3 class="mb-3 text-lg font-semibold text-gray-900">Ingredients</h3>
                            <p class="text-gray-600">{{ menuItem.ingredients }}</p>
                        </div>

                        <!-- Allergens -->
                        <div v-if="menuItem.allergens && menuItem.allergens.length > 0" class="rounded-lg bg-white p-6 shadow-sm">
                            <h3 class="mb-3 text-lg font-semibold text-gray-900">Allergen Information</h3>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="allergen in menuItem.allergens" :key="allergen" variant="destructive" class="text-xs">
                                    <AlertTriangle class="mr-1 h-3 w-3" />
                                    {{ allergen }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Add to Cart -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-medium text-gray-900">Quantity:</span>
                                <div class="flex items-center space-x-2">
                                    <Button
                                        @click="quantity > 1 && quantity--"
                                        variant="outline"
                                        size="sm"
                                        class="h-10 w-10 p-0"
                                        :disabled="quantity <= 1"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </Button>
                                    <span class="w-12 text-center text-lg font-medium">{{ quantity }}</span>
                                    <Button
                                        @click="quantity < 10 && quantity++"
                                        variant="outline"
                                        size="sm"
                                        class="h-10 w-10 p-0"
                                        :disabled="quantity >= 10"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <Button
                                    @click="addToCart"
                                    :disabled="!menuItem.is_available || isLoading"
                                    class="flex-1 bg-green-600 hover:bg-green-700"
                                    size="lg"
                                >
                                    <ShoppingCart class="mr-2 h-5 w-5" />
                                    {{ isLoading ? 'Adding...' : 'Add to Cart' }}
                                </Button>
                                <Button @click="toggleWishlist" variant="outline" size="lg" class="px-4">
                                    <Heart :class="['h-5 w-5', isWishlisted ? 'fill-current text-red-500' : '']" />
                                </Button>
                            </div>

                            <div v-if="!menuItem.is_available" class="text-center">
                                <Badge variant="destructive" class="text-sm"> Currently Unavailable </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="mt-16">
                    <div class="rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold text-gray-900">Customer Reviews</h2>
                                <Button variant="outline" @click="showReviewModal = true">
                                    <MessageSquare class="mr-2 h-4 w-4" />
                                    Write Review
                                </Button>
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="divide-y divide-gray-200">
                            <div v-for="review in menuItem.reviews" :key="review.id" class="p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                        <User class="h-5 w-5 text-green-600" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-sm font-medium text-gray-900">{{ review.user.name }}</h4>
                                            <div class="flex items-center">
                                                <Star
                                                    v-for="i in 5"
                                                    :key="i"
                                                    :class="['h-4 w-4', i <= review.rating ? 'fill-current text-yellow-400' : 'text-gray-300']"
                                                />
                                            </div>
                                            <span class="text-xs text-gray-500">
                                                {{ formatDate(review.created_at) }}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-600">{{ review.comment }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty Reviews State -->
                            <div v-if="!menuItem.reviews || menuItem.reviews.length === 0" class="p-12 text-center">
                                <MessageSquare class="mx-auto mb-4 h-12 w-12 text-gray-300" />
                                <h3 class="mb-2 text-lg font-medium text-gray-900">No reviews yet</h3>
                                <p class="mb-6 text-gray-500">Be the first to review this item!</p>
                                <Button @click="showReviewModal = true" class="bg-green-600 hover:bg-green-700"> Write First Review </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Items -->
                <div v-if="relatedItems && relatedItems.length > 0" class="mt-16">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">You Might Also Like</h2>
                        <p class="mt-1 text-gray-600">Similar items from the same category</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div
                            v-for="item in relatedItems"
                            :key="item.id"
                            class="group cursor-pointer rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md"
                            @click="$inertia.visit(route('user.menu.show', item.id))"
                        >
                            <div class="mb-4 aspect-square overflow-hidden rounded-lg bg-gray-100">
                                <img
                                    :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image"
                                    :alt="item.name"
                                    class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                />
                            </div>
                            <h3 class="mb-1 text-sm font-medium text-gray-900">{{ item.name }}</h3>
                            <p class="mb-2 line-clamp-2 text-xs text-gray-600">{{ item.description }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-green-600">
                                    {{ formatCurrency(item.price) }}
                                </span>
                                <div class="flex items-center space-x-1">
                                    <Star class="h-3 w-3 fill-current text-yellow-400" />
                                    <span class="text-xs text-gray-600">{{ item.average_rating?.toFixed(1) || '0.0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <SuccessModal
            :show="showSuccessModal"
            title="Added to Cart!"
            :message="`${quantity} × ${menuItem.name} has been added to your cart.`"
            :show-cart-button="true"
            @close="showSuccessModal = false"
        />

        <!-- Review Modal -->
        <ReviewModal :show="showReviewModal" :menu-item="menuItem" @close="showReviewModal = false" @submitted="handleReviewSubmitted" />
    </div>
</template>

<script setup lang="ts">
import ReviewModal from '@/components/User/ReviewModal.vue';
import SuccessModal from '@/components/User/SuccessModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link, router } from '@inertiajs/vue3';
import {
    Activity,
    AlertTriangle,
    ChevronRight,
    Droplets,
    Heart,
    MessageSquare,
    Minus,
    Plus,
    ShoppingCart,
    Star,
    User,
    Wheat,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    original_price?: number;
    image: string;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
    ingredients: string;
    allergens: string[];
    is_available: boolean;
    is_popular?: boolean;
    average_rating: number;
    review_count: number;
    category:
        | {
              name: string;
          }
        | string;
    reviews: Array<{
        id: number;
        rating: number;
        comment: string;
        created_at: string;
        user: {
            name: string;
        };
    }>;
}

const props = defineProps<{
    menuItem: MenuItem;
    relatedItems: MenuItem[];
}>();

// State
const quantity = ref(1);
const isLoading = ref(false);
const isWishlisted = ref(false);
const showSuccessModal = ref(false);
const showReviewModal = ref(false);

// Methods
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

const addToCart = async () => {
    if (!props.menuItem.is_available || isLoading.value) return;

    isLoading.value = true;

    try {
        await router.post(
            route('user.cart.add'),
            {
                menu_item_id: props.menuItem.id,
                quantity: quantity.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccessModal.value = true;
                    quantity.value = 1;
                },
                onError: (errors) => {
                    console.error('Error adding to cart:', errors);
                },
            },
        );
    } finally {
        isLoading.value = false;
    }
};

const toggleWishlist = () => {
    isWishlisted.value = !isWishlisted.value;
    // TODO: Implement wishlist API call
};

const handleReviewSubmitted = () => {
    showReviewModal.value = false;
    router.reload({ only: ['menuItem'] });
};
</script>
