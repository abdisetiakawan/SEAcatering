<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff, LoaderCircle, Shield, Utensils } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 to-green-100 p-4">
        <Head title="Konfirmasi Password - SEA Catering" />

        <div class="w-full max-w-md">
            <!-- Back Button -->
            <div class="mb-6">
                <button
                    @click="$inertia.visit(route('dashboard'))"
                    class="inline-flex items-center text-sm text-gray-600 transition-colors hover:text-green-600"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali
                </button>
            </div>

            <!-- Logo & Brand -->
            <div class="mb-8 text-center">
                <div class="mb-4 flex items-center justify-center space-x-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-600">
                        <Utensils class="h-7 w-7 text-white" />
                    </div>
                    <span class="text-3xl font-bold text-green-600">SEA Catering</span>
                </div>
            </div>

            <!-- Confirm Password Card -->
            <Card class="border-0 shadow-xl">
                <CardHeader class="pb-4 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <Shield class="h-8 w-8 text-green-600" />
                        </div>
                    </div>
                    <CardTitle class="text-2xl font-bold text-gray-900">Konfirmasi Password</CardTitle>
                    <p class="mt-2 text-gray-600">Ini adalah area aman dari aplikasi. Silakan konfirmasi password Anda sebelum melanjutkan.</p>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Password Field -->
                        <div class="space-y-2">
                            <Label for="password" class="text-sm font-medium text-gray-700">Password</Label>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    autofocus
                                    placeholder="Masukkan password Anda"
                                    class="h-11 pr-10"
                                    :class="{ 'border-red-500 focus:border-red-500': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="togglePasswordVisibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                >
                                    <Eye v-if="!showPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Submit Button -->
                        <Button type="submit" class="h-11 w-full bg-green-600 font-medium text-white hover:bg-green-700" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Konfirmasi Password
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>&copy; 2024 SEA Catering. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</template>
