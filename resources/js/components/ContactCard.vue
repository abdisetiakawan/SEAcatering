<template>
    <Card class="mx-auto max-w-md bg-white">
        <CardContent class="p-8">
            <div class="text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <Phone class="h-8 w-8 text-green-600" />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">Hubungi Manager Kami</h3>
                <p class="mb-4 text-gray-600">Brian - Customer Service Manager</p>

                <div class="space-y-3">
                    <div class="flex items-center justify-center space-x-2 text-gray-700">
                        <Phone class="h-5 w-5" />
                        <span class="font-semibold">08123456789</span>
                    </div>
                    <div class="flex items-center justify-center space-x-2 text-gray-700">
                        <Mail class="h-5 w-5" />
                        <span>info@seacatering.com</span>
                    </div>
                </div>

                <a
                    href="https://wa.me/6281234567890"
                    target="_blank"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700"
                >
                    <MessageCircle class="mr-2 h-4 w-4" />
                    Chat WhatsApp
                </a>
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Mail, MessageCircle, Phone } from 'lucide-vue-next';
</script>
