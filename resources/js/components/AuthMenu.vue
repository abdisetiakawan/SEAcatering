<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, LogOut } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <div class="ml-6 flex items-center gap-3 border-l border-gray-200 pl-6">
        <!-- If user is authenticated -->
        <template v-if="user">
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">Halo, {{ user.name }}</span>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center rounded-sm border border-green-600 px-4 py-2 text-sm font-medium text-green-600 transition-colors hover:bg-green-50"
                >
                    <LayoutDashboard class="mr-2 h-4 w-4" />
                    Dashboard
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="inline-flex items-center rounded-sm border border-transparent px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:border-red-200 hover:text-red-600"
                >
                    <LogOut class="mr-2 h-4 w-4" />
                    Logout
                </Link>
            </div>
        </template>

        <!-- If user is not authenticated -->
        <template v-else>
            <Link
                :href="route('login')"
                class="inline-block rounded-sm border border-transparent px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:border-green-200 hover:text-green-600"
            >
                Log in
            </Link>
            <Link
                :href="route('register')"
                class="inline-block rounded-sm border border-green-600 px-4 py-2 text-sm font-medium text-green-600 transition-colors hover:bg-green-50"
            >
                Register
            </Link>
        </template>
    </div>
</template>
