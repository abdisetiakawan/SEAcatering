<template>
    <Modal :show="show" @close="$emit('close')" max-width="4xl">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ isEditing ? 'Edit Menu' : 'Tambah Menu Baru' }}
                </h2>
                <Button variant="ghost" size="sm" @click="$emit('close')">
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Nama Menu * </label>
                            <Input v-model="form.name" placeholder="Contoh: Grilled Chicken Salad" :error="errors.name" required />
                        </div>

                        <div>
                            <Select v-model="form.category">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Pilih Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="diet">Diet Plan</SelectItem>
                                        <SelectItem value="protein">Protein Plan</SelectItem>
                                        <SelectItem value="royal">Royal Plan</SelectItem>
                                        <SelectItem value="vegetarian">Vegetarian</SelectItem>
                                        <SelectItem value="seafood">Seafood</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Harga (Rp) * </label>
                                <Input v-model="form.price" type="number" placeholder="35000" :error="errors.price" required />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Kalori * </label>
                                <Input v-model="form.calories" type="number" placeholder="350" :error="errors.calories" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Protein (g) * </label>
                                <Input v-model="form.protein" type="number" step="0.1" placeholder="25" :error="errors.protein" required />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Karbo (g) * </label>
                                <Input v-model="form.carbs" type="number" step="0.1" placeholder="15" :error="errors.carbs" required />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Lemak (g) * </label>
                                <Input v-model="form.fat" type="number" step="0.1" placeholder="12" :error="errors.fat" required />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Deskripsi * </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500 focus:outline-none"
                                placeholder="Deskripsi menu..."
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Bahan-bahan </label>
                            <textarea
                                v-model="form.ingredients"
                                rows="2"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500 focus:outline-none"
                                placeholder="Daftar semua bahan..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Gambar Menu </label>
                            <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                                <div v-if="imagePreview" class="mb-4">
                                    <img :src="imagePreview" alt="Preview" class="mx-auto h-48 max-w-full rounded object-cover" />
                                </div>
                                <div v-else class="mb-4">
                                    <Upload class="mx-auto mb-2 h-12 w-12 text-gray-400" />
                                    <p class="text-gray-500">Upload gambar menu</p>
                                </div>
                                <input ref="fileInput" type="file" accept="image/*" @change="handleImageUpload" class="hidden" />
                                <Button type="button" variant="outline" @click="fileInput?.click()"> Pilih Gambar </Button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Alergen </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label v-for="allergen in allergenOptions" :key="allergen" class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :value="allergen"
                                        v-model="form.allergens"
                                        class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    />
                                    <span class="text-sm">{{ allergen }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input type="checkbox" v-model="form.is_available" class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                            <label class="text-sm font-medium text-gray-700"> Menu tersedia </label>
                        </div>

                        <!-- Preview Card -->
                        <div class="rounded-lg border bg-gray-50 p-4">
                            <h4 class="mb-2 font-medium">Preview</h4>
                            <div class="rounded border bg-white p-3">
                                <h5 class="font-semibold">{{ form.name || 'Nama Menu' }}</h5>
                                <p class="mb-2 text-sm text-gray-600">{{ form.description || 'Deskripsi menu...' }}</p>
                                <div class="mb-2 flex justify-between text-sm">
                                    <span>Kalori: {{ form.calories || 0 }}</span>
                                    <span>Protein: {{ form.protein || 0 }}g</span>
                                </div>
                                <p class="font-bold text-green-600">Rp {{ formatPrice(form.price || 0) }}</p>
                                <Badge v-if="form.category" class="mt-2">{{ getCategoryLabel(form.category) }}</Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 border-t pt-6">
                    <Button type="button" variant="outline" @click="$emit('close')"> Batal </Button>
                    <Button type="submit" class="bg-green-600 hover:bg-green-700" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : isEditing ? 'Update Menu' : 'Simpan Menu' }}
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { Upload, X } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    description: string;
    price: number;
    category: string;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
    ingredients: string;
    allergens: string[];
    image: string;
    is_available: boolean;
}

const fileInput = ref<HTMLInputElement | null>(null);

const props = defineProps<{
    show: boolean;
    item?: MenuItem | null;
    isEditing: boolean;
}>();

defineEmits<{
    close: [];
    saved: [];
}>();

// State
const processing = ref(false);
const imagePreview = ref<string | null>(null);
const errors = ref<Record<string, string>>({});

const form = reactive({
    name: '',
    description: '',
    price: 0,
    category: '',
    calories: 0,
    protein: 0,
    carbs: 0,
    fat: 0,
    ingredients: '',
    allergens: [] as string[],
    image: null as File | null,
    is_available: true,
});

const allergenOptions = ['Gluten', 'Dairy', 'Nuts', 'Eggs', 'Soy', 'Seafood'];
function resolveImagePreview(image: string | null): string | null {
    if (!image) return null;
    if (image.startsWith('http') || image.startsWith('data:')) return image;
    return `/storage/${image}`;
}

// Watch for item changes
watch(
    () => props.item,
    (newItem) => {
        if (newItem && props.isEditing) {
            Object.assign(form, {
                name: newItem.name,
                description: newItem.description,
                price: newItem.price,
                category: newItem.category,
                calories: newItem.calories,
                protein: newItem.protein,
                carbs: newItem.carbs,
                fat: newItem.fat,
                ingredients: newItem.ingredients,
                allergens: newItem.allergens || [],
                is_available: newItem.is_available,
                image: null, // kosongkan karena image upload tidak dikirim ulang saat edit
            });

            imagePreview.value = resolveImagePreview(newItem.image);
        } else {
            resetForm();
        }
    },
);
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen && !props.isEditing) {
            resetForm();
        }
    },
);

const resetForm = () => {
    Object.assign(form, {
        name: '',
        description: '',
        price: 0,
        category: '',
        calories: 0,
        protein: 0,
        carbs: 0,
        fat: 0,
        ingredients: '',
        allergens: [],
        image: null,
        is_available: true,
    });
    imagePreview.value = null;
    errors.value = {};
};

const handleImageUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleSubmit = () => {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();
    Object.entries(form).forEach(([key, value]) => {
        if (key === 'allergens') {
            formData.append(key, JSON.stringify(value));
        } else if (key === 'image' && value) {
            formData.append(key, value as File);
        } else if (value !== null) {
            formData.append(key, String(value));
        }
    });

    const url = props.isEditing ? route('admin.menu.update', props.item!.id) : route('admin.menu.store');

    if (props.isEditing) {
        formData.append('_method', 'PATCH');
    }

    router.post(url, formData, {
        onSuccess: () => {
            processing.value = false;

            router.visit(route('admin.menu.index'), {
                preserveScroll: true,
                preserveState: false, // biar form modal tidak tetap kebuka
            });
        },
        onError: (errorResponse) => {
            processing.value = false;
            errors.value = errorResponse;
        },
    });
};

const getCategoryLabel = (category: string) => {
    const labels: Record<string, string> = {
        diet: 'Diet Plan',
        protein: 'Protein Plan',
        royal: 'Royal Plan',
        vegetarian: 'Vegetarian',
        seafood: 'Seafood',
    };
    return labels[category] || category;
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
