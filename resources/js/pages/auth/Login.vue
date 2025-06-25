<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle, Utensils, ArrowLeft, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center p-4">
        <Head title="Login - SEA Catering" />

        <div class="w-full max-w-md">
            <!-- Back to Home -->
            <div class="mb-6">
                <Link
                    :href="route('home')"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-green-600 transition-colors"
                >
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Kembali ke Beranda
                </Link>
            </div>

            <!-- Logo & Brand -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-600">
                        <Utensils class="h-7 w-7 text-white" />
                    </div>
                    <span class="text-3xl font-bold text-green-600">SEA Catering</span>
                </div>
                <p class="text-gray-600">Selamat datang kembali!</p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm font-medium text-green-800 text-center">{{ status }}</p>
            </div>

            <!-- Login Card -->
            <Card class="shadow-xl border-0">
                <CardHeader class="text-center pb-4">
                    <CardTitle class="text-2xl font-bold text-gray-900">Masuk ke Akun Anda</CardTitle>
                    <p class="text-gray-600 mt-2">Masukkan email dan password untuk masuk</p>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email Field -->
                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="email@example.com"
                                class="h-11"
                                :class="{ 'border-red-500 focus:border-red-500': form.errors.email }"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-medium text-gray-700">Password</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm text-green-600 hover:text-green-700 underline underline-offset-4"
                                    :tabindex="5"
                                >
                                    Lupa password?
                                </TextLink>
                            </div>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    v-model="form.password"
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

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                                <Checkbox
                                    id="remember"
                                    v-model="form.remember"
                                    :tabindex="3"
                                    class="data-[state=checked]:bg-green-600 data-[state=checked]:border-green-600"
                                />
                                <span class="text-sm text-gray-700">Ingat saya</span>
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="w-full h-11 bg-green-600 hover:bg-green-700 text-white font-medium"
                            :tabindex="4"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Masuk
                        </Button>

                        <!-- Register Link -->
                        <div class="text-center pt-4 border-t">
                            <p class="text-sm text-gray-600">
                                Belum punya akun?
                                <TextLink
                                    :href="route('register')"
                                    class="font-medium text-green-600 hover:text-green-700 underline underline-offset-4"
                                    :tabindex="6"
                                >
                                    Daftar sekarang
                                </TextLink>
                            </p>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Footer -->
            <div class="text-center mt-8 text-sm text-gray-500">
                <p>&copy; 2024 SEA Catering. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</template>
