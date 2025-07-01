<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Edit Review' : 'Write Review' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update your review' : 'Share your experience with this item' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitReview" class="space-y-4">
                <!-- Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                    <div class="flex items-center space-x-1">
                        <button
                            v-for="i in 5"
                            :key="i"
                            type="button"
                            @click="form.rating = i"
                            class="focus:outline-none"
                        >
                            <Star
                                :class="[
                                    'h-6 w-6 transition-colors',
                                    i <= form.rating
                                        ? 'fill-current text-yellow-400'
                                        : 'text-gray-300 hover:text-yellow-200',
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
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        Comment (Optional)
                    </label>
                    <textarea
                        id="comment"
                        v-model="form.comment"
                        rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        placeholder="Tell us about your experience..."
                    ></textarea>
                    <div v-if="errors.comment" class="mt-1 text-sm text-red-600">
                        {{ errors.comment }}
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('close')">
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="isSubmitting || form.rating === 0"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        {{ isSubmitting ? 'Submitting...' : (isEditing ? 'Update Review' : 'Submit Review') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

interface Review {
    id: number;
    rating: number;
    comment?: string;
}

const props = defineProps<{
    show: boolean;
    review?: Review | null;
}>();

const emit = defineEmits<{
    close: [];
    updated: [];
}>();

const isEditing = computed(() => !!props.review);

const form = reactive({
    rating: 0,
    comment: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Watch for review prop changes to populate form
watch(() => props.review, (newReview) => {
    if (newReview) {
        form.rating = newReview.rating;
        form.comment = newReview.comment || '';
    } else {
        form.rating = 0;
        form.comment = '';
    }
}, { immediate: true });

// Reset form when modal closes
watch(() => props.show, (isOpen) => {
    if (!isOpen) {
        errors.value = {};
        if (!isEditing.value) {
            form.rating = 0;
            form.comment = '';
        }
    }
});

const submitReview = async () => {
    if (form.rating === 0) {
        errors.value.rating = 'Please select a rating';
        return;
    }

    isSubmitting.value = true;
    errors.value = {};

    try {
        if (isEditing.value && props.review) {
            await router.put(`/user/reviews/${props.review.id}`, form, {
                preserveScroll: true,
                onSuccess: () => {
                    emit('updated');
                },
                onError: (responseErrors) => {
                    errors.value = responseErrors;
                },
            });
        } else {
            await router.post('/user/reviews', form, {
                preserveScroll: true,
                onSuccess: () => {
                    emit('updated');
                },
                onError: (responseErrors) => {
                    errors.value = responseErrors;
                },
            });
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>
