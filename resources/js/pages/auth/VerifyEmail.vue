<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, LoaderCircle, Mail, Utensils } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 to-green-100 p-4">
        <Head title="Verifikasi Email - SEA Catering" />

        <div class="w-full max-w-md">
            <!-- Back to Home -->
            <div class="mb-6">
                <Link :href="route('home')" class="inline-flex items-center text-sm text-gray-600 transition-colors hover:text-green-600">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Beranda
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
            <div v-if="status === 'verification-link-sent'" class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
                <div class="flex items-center space-x-2">
                    <CheckCircle class="h-5 w-5 text-green-600" />
                    <p class="text-sm font-medium text-green-800">
                        Link verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
                    </p>
                </div>
            </div>

            <!-- Verify Email Card -->
            <Card class="border-0 shadow-xl">
                <CardHeader class="pb-4 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <Mail class="h-8 w-8 text-green-600" />
                        </div>
                    </div>
                    <CardTitle class="text-2xl font-bold text-gray-900">Verifikasi Email</CardTitle>
                    <p class="mt-2 text-gray-600">
                        Silakan verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan kepada Anda.
                    </p>
                </CardHeader>

                <CardContent>
                    <div class="space-y-6 text-center">
                        <!-- Resend Button -->
                        <form @submit.prevent="submit">
                            <Button
                                type="submit"
                                variant="outline"
                                class="h-11 w-full border-green-600 text-green-600 hover:bg-green-50"
                                :disabled="form.processing"
                            >
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Kirim Ulang Email Verifikasi
                            </Button>
                        </form>

                        <!-- Logout Link -->
                        <div class="border-t pt-4">
                            <TextLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-sm text-gray-600 underline underline-offset-4 hover:text-green-600"
                            >
                                Keluar
                            </TextLink>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Help Text -->
            <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="text-center">
                    <p class="mb-2 text-sm text-blue-800">
                        <strong>Tidak menerima email?</strong>
                    </p>
                    <ul class="space-y-1 text-xs text-blue-700">
                        <li>• Periksa folder spam/junk email Anda</li>
                        <li>• Pastikan alamat email sudah benar</li>
                        <li>• Tunggu beberapa menit sebelum mencoba lagi</li>
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>&copy; 2024 SEA Catering. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</template>
