<template>
    <AdminLayout>
        <Head title="Edit Meal Plan" />
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">Edit Meal Plan</h1>
                            <p class="text-muted-foreground">Update meal plan information</p>
                        </div>
                        <Button variant="outline" @click="$inertia.visit(route('admin.meal-plans.index'))">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Meal Plans
                        </Button>
                    </div>

                    <!-- Form -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Meal Plan Details</CardTitle>
                            <CardDescription> Update the information below to modify the meal plan </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <!-- Basic Information -->
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <Label for="name">Plan Name *</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                placeholder="Enter meal plan name"
                                                :class="{ 'border-red-500': errors.name }"
                                            />
                                            <InputError :message="errors.name" />
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="plan_type">Plan Type *</Label>
                                            <Select v-model="form.plan_type">
                                                <SelectTrigger :class="{ 'border-red-500': errors.plan_type }">
                                                    <SelectValue placeholder="Select plan type" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="(label, value) in planTypes" :key="value" :value="value">
                                                        {{ label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError :message="errors.plan_type" />
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="price_per_meal">Price per Meal *</Label>
                                            <Input
                                                id="price_per_meal"
                                                v-model="form.price_per_meal"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                :class="{ 'border-red-500': errors.price_per_meal }"
                                            />
                                            <InputError :message="errors.price_per_meal" />
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="target_calories">Target Calories *</Label>
                                            <Input
                                                id="target_calories"
                                                v-model="form.target_calories"
                                                type="number"
                                                min="0"
                                                placeholder="Enter target calories"
                                                :class="{ 'border-red-500': errors.target_calories }"
                                            />
                                            <InputError :message="errors.target_calories" />
                                        </div>
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <Label for="image">Plan Image</Label>
                                            <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                                                <div v-if="imagePreview || mealPlan.image" class="mb-4">
                                                    <img
                                                        :src="imagePreview || `/storage/${mealPlan.image}`"
                                                        alt="Preview"
                                                        class="mx-auto h-32 w-32 rounded-lg object-cover"
                                                    />
                                                </div>
                                                <div v-else class="mb-4">
                                                    <ImageIcon class="mx-auto h-12 w-12 text-gray-400" />
                                                </div>
                                                <div class="space-y-2">
                                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                                                    <div class="flex justify-center gap-2">
                                                        <Button type="button" variant="outline" size="sm" @click="fileInput?.click()">
                                                            {{ mealPlan.image || imagePreview ? 'Change Image' : 'Choose File' }}
                                                        </Button>
                                                        <Button
                                                            v-if="mealPlan.image || imagePreview"
                                                            type="button"
                                                            variant="outline"
                                                            size="sm"
                                                            @click="removeImage"
                                                        >
                                                            <Trash2 class="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                            <InputError :message="errors.image" />
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                                            <Label for="is_active">Active Plan</Label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Description *</Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="{ 'border-red-500': errors.description }"
                                        placeholder="Describe the meal plan..."
                                    ></textarea>
                                    <InputError :message="errors.description" />
                                </div>

                                <!-- Features -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <Label>Plan Features</Label>
                                        <Button type="button" variant="outline" size="sm" @click="addFeature">
                                            <Plus class="mr-2 h-4 w-4" />
                                            Add Feature
                                        </Button>
                                    </div>
                                    <div class="space-y-2">
                                        <div v-for="(feature, index) in form.features" :key="index" class="flex items-center space-x-2">
                                            <Input v-model="form.features[index]" placeholder="Enter feature" class="flex-1" />
                                            <Button type="button" variant="outline" size="sm" @click="removeFeature(index)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <p v-if="form.features.length === 0" class="text-sm text-gray-500">
                                            No features added yet. Click "Add Feature" to get started.
                                        </p>
                                    </div>
                                </div>

                                <!-- Menu Items Selection -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <Label>Menu Items *</Label>
                                        <div class="flex items-center gap-4">
                                            <div class="text-sm text-gray-500">{{ form.menu_items.length }} item(s) selected</div>
                                            <div class="flex gap-2">
                                                <Button type="button" variant="outline" size="sm" @click="selectAllMenuItems"> Select All </Button>
                                                <Button type="button" variant="outline" size="sm" @click="clearAllMenuItems"> Clear All </Button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto rounded-lg border p-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            <div
                                                v-for="item in menuItems"
                                                :key="item.id"
                                                class="flex cursor-pointer items-center space-x-3 rounded-lg border p-3 hover:bg-gray-50"
                                                :class="{
                                                    'border-blue-200 bg-blue-50': form.menu_items.includes(item.id),
                                                }"
                                                @click="toggleMenuItem(item.id)"
                                            >
                                                <Checkbox :checked="form.menu_items.includes(item.id)" @click.stop="toggleMenuItem(item.id)" />
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-gray-900">
                                                        {{ item.name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">{{ item.category?.name }} • ${{ item.price }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="menuItems.length === 0" class="py-8 text-center">
                                            <p class="text-gray-500">No menu items available</p>
                                        </div>
                                    </div>
                                    <InputError :message="errors.menu_items" />
                                </div>

                                <!-- Submit Buttons -->
                                <div class="flex items-center justify-end space-x-4 border-t pt-6">
                                    <Button type="button" variant="outline" @click="$inertia.visit(route('admin.meal-plans.index'))"> Cancel </Button>
                                    <Button type="submit" :disabled="processing">
                                        <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                        Update Meal Plan
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ImageIcon, Loader2, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';

interface MenuItem {
    id: number;
    name: string;
    price: string;
    category?: {
        name: string;
    };
}

interface MealPlan {
    id: number;
    name: string;
    description: string;
    price_per_meal: string;
    target_calories: number;
    plan_type: string;
    features: string[] | null;
    image: string | null;
    is_active: boolean;
    menu_items: MenuItem[];
}

interface PlanTypes {
    [key: string]: string;
}

interface Props {
    mealPlan: MealPlan;
    menuItems: MenuItem[];
    planTypes: PlanTypes;
    errors?: Record<string, string>;
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
});

const fileInput = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(null);
const processing = ref<boolean>(false);

interface FormData {
    [key: string]: any;
    name: string;
    description: string;
    price_per_meal: string;
    target_calories: string;
    plan_type: string;
    features: string[];
    image: File | null;
    is_active: boolean;
    menu_items: number[];
}

const form = useForm<FormData>({
    name: props.mealPlan.name,
    description: props.mealPlan.description,
    price_per_meal: props.mealPlan.price_per_meal,
    target_calories: props.mealPlan.target_calories.toString(),
    plan_type: props.mealPlan.plan_type,
    features: props.mealPlan.features || [],
    image: null,
    is_active: props.mealPlan.is_active,
    menu_items: props.mealPlan.menu_items.map((item) => item.id),
});

const handleImageUpload = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e: ProgressEvent<FileReader>) => {
            if (e.target?.result) {
                imagePreview.value = e.target.result as string;
            }
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = (): void => {
    form.image = null;
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const addFeature = (): void => {
    form.features.push('');
};

const removeFeature = (index: number): void => {
    form.features.splice(index, 1);
};

const toggleMenuItem = (itemId: number): void => {
    const index = form.menu_items.indexOf(itemId);
    if (index > -1) {
        form.menu_items.splice(index, 1);
    } else {
        form.menu_items.push(itemId);
    }
};

const selectAllMenuItems = (): void => {
    form.menu_items = props.menuItems.map((item) => item.id);
};

const clearAllMenuItems = (): void => {
    form.menu_items = [];
};

const submit = (): void => {
    processing.value = true;

    form.put(route('admin.meal-plans.update', props.mealPlan.id), {
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: () => {
            // Form will redirect on success
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
};
</script>
