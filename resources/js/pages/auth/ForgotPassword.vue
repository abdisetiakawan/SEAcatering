<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, KeyRound, LoaderCircle, Utensils } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 to-green-100 p-4">
        <Head title="Lupa Password - SEA Catering" />

        <div class="w-full max-w-md">
            <!-- Back to Login -->
            <div class="mb-6">
                <Link :href="route('login')" class="inline-flex items-center text-sm text-gray-600 transition-colors hover:text-green-600">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Login
                </Link>
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

            <!-- Status Message -->
            <div v-if="status" class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
                <div class="flex items-center space-x-2">
                    <CheckCircle class="h-5 w-5 text-green-600" />
                    <p class="text-sm font-medium text-green-800">{{ status }}</p>
                </div>
            </div>

            <!-- Forgot Password Card -->
            <Card class="border-0 shadow-xl">
                <CardHeader class="pb-4 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <KeyRound class="h-8 w-8 text-green-600" />
                        </div>
                    </div>
                    <CardTitle class="text-2xl font-bold text-gray-900">Lupa Password?</CardTitle>
                    <p class="mt-2 text-gray-600">Masukkan email Anda untuk menerima link reset password</p>
                </CardHeader>

                <CardContent>
                    <div class="space-y-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Email Field -->
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium text-gray-700">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    v-model="form.email"
                                    autofocus
                                    placeholder="email@example.com"
                                    class="h-11"
                                    :class="{ 'border-red-500 focus:border-red-500': form.errors.email }"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                class="h-11 w-full bg-green-600 font-medium text-white hover:bg-green-700"
                                :disabled="form.processing"
                            >
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Kirim Link Reset Password
                            </Button>
                        </form>

                        <!-- Back to Login -->
                        <div class="border-t pt-4 text-center">
                            <p class="text-sm text-gray-600">
                                Atau, kembali ke
                                <TextLink :href="route('login')" class="font-medium text-green-600 underline underline-offset-4 hover:text-green-700">
                                    halaman login
                                </TextLink>
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Help Text -->
            <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="text-center">
                    <p class="mb-2 text-sm text-blue-800">
                        <strong>Cara reset password:</strong>
                    </p>
                    <ol class="space-y-1 text-left text-xs text-blue-700">
                        <li>1. Masukkan alamat email yang terdaftar</li>
                        <li>2. Klik tombol "Kirim Link Reset Password"</li>
                        <li>3. Periksa email Anda dan klik link yang dikirim</li>
                        <li>4. Buat password baru di halaman yang terbuka</li>
                    </ol>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>&copy; 2024 SEA Catering. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</template>
