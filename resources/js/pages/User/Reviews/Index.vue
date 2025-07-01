<template>
    <UserLayout>
        <Head title="My Reviews" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">My Reviews</h1>
                    <p class="mt-1 text-gray-600">Manage all your product reviews</p>
                </div>

                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select
                                    id="status"
                                    v-model="filters.status"
                                    @change="applyFilters"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                >
                                    <option value="">All Status</option>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div>
                                <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                                <select
                                    id="rating"
                                    v-model="filters.rating"
                                    @change="applyFilters"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                >
                                    <option value="">All Ratings</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <Button @click="clearFilters" variant="outline" class="w-full"> Clear Filters </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                <div class="space-y-6">
                    <div v-if="reviews.data.length === 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-12 text-center">
                            <Star class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-4 text-lg font-medium text-gray-900">No reviews yet</h3>
                            <p class="mt-2 text-gray-600">You haven't written any reviews yet. Order some items and share your experience!</p>
                            <Button @click="router.visit('/user/menu')" class="mt-4"> Browse Menu </Button>
                        </div>
                    </div>

                    <div v-for="review in reviews.data" :key="review.id" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <img
                                    :src="getImageUrl(review.menu_item.image)"
                                    :alt="review.menu_item.name"
                                    class="h-16 w-16 rounded-lg object-cover"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">{{ review.menu_item.name }}</h3>
                                            <p class="text-sm text-gray-500">Order #{{ review.order.order_number }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Badge v-if="review.is_published" variant="default">Published</Badge>
                                            <Badge v-else variant="secondary">Draft</Badge>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="sm">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem @click="editReview(review)">
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit Review
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="confirmDeleteReview(review)" class="text-red-600">
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Delete Review
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </div>

                                    <div class="mt-2 flex items-center space-x-2">
                                        <div class="flex">
                                            <Star
                                                v-for="i in 5"
                                                :key="i"
                                                :class="['h-4 w-4', i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300']"
                                            />
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ review.rating }}/5</span>
                                        <span class="text-sm text-gray-500">•</span>
                                        <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
                                    </div>

                                    <p v-if="review.comment" class="mt-3 text-gray-700">{{ review.comment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="reviews.links && reviews.links.length > 3" class="mt-6">
                    <nav class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="reviews.prev_page_url"
                                :href="reviews.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="reviews.next_page_url"
                                :href="reviews.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">Showing {{ reviews.from }} to {{ reviews.to }} of {{ reviews.total }} results</p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <template v-for="(link, index) in reviews.links" :key="index">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                                link.active
                                                    ? 'z-10 border-green-500 bg-green-50 text-green-600'
                                                    : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50',
                                                index === 0 ? 'rounded-l-md' : '',
                                                index === reviews.links.length - 1 ? 'rounded-r-md' : '',
                                                'border',
                                            ]"
                                        >
                                            <span v-html="link.label"></span>
                                        </Link>
                                        <span v-else v-html="link.label"></span>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Edit Review Modal -->
        <ReviewModal :show="showEditModal" :review="selectedReview" @close="closeEditModal" @updated="handleReviewUpdated" />

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Delete Review"
            message="Are you sure you want to delete this review? This action cannot be undone."
            @confirm="deleteReview"
            @cancel="showDeleteModal = false"
        />
    </UserLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/User/ConfirmationModal.vue';
import ReviewModal from '@/components/User/ReviewModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserLayout from '@/layouts/UserLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, MoreHorizontal, Star, Trash2 } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    image: string;
}

interface Order {
    id: number;
    order_number: string;
}

interface Review {
    id: number;
    rating: number;
    comment?: string;
    is_published: boolean;
    created_at: string;
    menu_item: MenuItem;
    order: Order;
}

interface PaginatedReviews {
    data: Review[];
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
    from: number;
    to: number;
    total: number;
    prev_page_url?: string;
    next_page_url?: string;
}

const props = defineProps<{
    reviews: PaginatedReviews;
    filters: {
        status?: string;
        rating?: string;
    };
}>();

const filters = reactive({
    status: props.filters.status || '',
    rating: props.filters.rating || '',
});

const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedReview = ref<Review | null>(null);

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

const applyFilters = () => {
    router.get('/user/reviews', filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.status = '';
    filters.rating = '';
    applyFilters();
};

const editReview = (review: Review) => {
    selectedReview.value = review;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedReview.value = null;
};

const handleReviewUpdated = () => {
    closeEditModal();
    router.reload({ only: ['reviews'] });
};

const confirmDeleteReview = (review: Review) => {
    selectedReview.value = review;
    showDeleteModal.value = true;
};

const deleteReview = () => {
    if (selectedReview.value) {
        router.delete(`/user/reviews/${selectedReview.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedReview.value = null;
            },
        });
    }
};
</script>
