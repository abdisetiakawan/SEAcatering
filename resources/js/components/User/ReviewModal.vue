<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Write a Review</DialogTitle>
                <DialogDescription> Share your experience with {{ menuItem.name }} </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitReview" class="space-y-4">
                <!-- Rating -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Rating</label>
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
                        placeholder="Tell us about your experience..."
                    ></textarea>
                    <div v-if="errors.comment" class="mt-1 text-sm text-red-600">
                        {{ errors.comment }}
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('close')"> Cancel </Button>
                    <Button type="submit" :disabled="isSubmitting" class="bg-green-600 hover:bg-green-700">
                        {{ isSubmitting ? 'Submitting...' : 'Submit Review' }}
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
import { reactive, ref } from 'vue';
import { route } from 'ziggy-js';

interface MenuItem {
    id: number;
    name: string;
}

const props = defineProps<{
    show: boolean;
    menuItem: MenuItem;
}>();

const emit = defineEmits<{
    close: [];
    submitted: [];
}>();

const form = reactive({
    rating: 0,
    comment: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const submitReview = async () => {
    if (form.rating === 0) {
        errors.value.rating = 'Please select a rating';
        return;
    }

    if (!form.comment.trim()) {
        errors.value.comment = 'Please write a comment';
        return;
    }

    isSubmitting.value = true;
    errors.value = {};

    try {
        await router.post(
            route('user.reviews.store'),
            {
                menu_item_id: props.menuItem.id,
                rating: form.rating,
                comment: form.comment,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.rating = 0;
                    form.comment = '';
                    emit('submitted');
                },
                onError: (responseErrors) => {
                    errors.value = responseErrors;
                },
            },
        );
    } finally {
        isSubmitting.value = false;
    }
};
</script>
