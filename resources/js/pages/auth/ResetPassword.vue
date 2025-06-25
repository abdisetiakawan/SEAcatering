<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, Eye, EyeOff, KeyRound, LoaderCircle, Utensils } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 to-green-100 p-4">
        <Head title="Reset Password - SEA Catering" />

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

            <!-- Reset Password Card -->
            <Card class="border-0 shadow-xl">
                <CardHeader class="pb-4 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <KeyRound class="h-8 w-8 text-green-600" />
                        </div>
                    </div>
                    <CardTitle class="text-2xl font-bold text-gray-900">Reset Password</CardTitle>
                    <p class="mt-2 text-gray-600">Masukkan password baru Anda di bawah ini</p>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email Field (Read Only) -->
                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">Email</Label>
                            <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" readonly class="h-11 bg-gray-50" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- New Password Field -->
                        <div class="space-y-2">
                            <Label for="password" class="text-sm font-medium text-gray-700">Password Baru</Label>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    autocomplete="new-password"
                                    v-model="form.password"
                                    autofocus
                                    placeholder="Masukkan password baru"
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

                            <!-- Password Requirements -->
                            <div class="space-y-1 text-xs text-gray-500">
                                <p class="font-medium">Password harus mengandung:</p>
                                <ul class="ml-2 space-y-1">
                                    <li class="flex items-center space-x-2">
                                        <CheckCircle class="h-3 w-3 text-green-500" />
                                        <span>Minimal 8 karakter</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <CheckCircle class="h-3 w-3 text-green-500" />
                                        <span>Huruf besar dan kecil</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <CheckCircle class="h-3 w-3 text-green-500" />
                                        <span>Angka dan simbol</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-sm font-medium text-gray-700">Konfirmasi Password</Label>
                            <div class="relative">
                                <Input
                                    id="password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    v-model="form.password_confirmation"
                                    placeholder="Ulangi password baru"
                                    class="h-11 pr-10"
                                    :class="{ 'border-red-500 focus:border-red-500': form.errors.password_confirmation }"
                                />
                                <button
                                    type="button"
                                    @click="togglePasswordConfirmationVisibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                >
                                    <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <!-- Submit Button -->
                        <Button type="submit" class="h-11 w-full bg-green-600 font-medium text-white hover:bg-green-700" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Reset Password
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
