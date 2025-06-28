<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md">
        <!-- Card Header -->
        <div class="border-b border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="rounded-full p-2" :class="getTypeIconClass(address.address_type)">
                        <component :is="getTypeIcon(address.address_type)" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ address.label }}</h3>
                        <div class="flex items-center space-x-2">
                            <Badge v-if="address.is_default" class="bg-green-100 text-green-800">
                                <Star class="mr-1 h-3 w-3" />
                                Alamat Utama
                            </Badge>
                            <Badge variant="outline" class="capitalize">
                                {{ getTypeLabel(address.address_type) }}
                            </Badge>
                        </div>
                    </div>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm">
                            <MoreVertical class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="$emit('edit', address)">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Alamat
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="!address.is_default" @click="$emit('set-default', address)">
                            <Star class="mr-2 h-4 w-4" />
                            Jadikan Utama
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="$emit('delete', address)" class="text-red-600 focus:text-red-600">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Hapus Alamat
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Card Content -->
        <div class="p-4">
            <!-- Recipient Info -->
            <div class="mb-4">
                <div class="flex items-center space-x-2 text-sm">
                    <User class="h-4 w-4 text-gray-400" />
                    <span class="font-medium text-gray-900">{{ address.recipient_name }}</span>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <Phone class="h-4 w-4 text-gray-400" />
                    <span>{{ formatPhoneNumber(address.phone_number) }}</span>
                </div>
            </div>

            <!-- Address Details -->
            <div class="mb-4">
                <div class="flex items-start space-x-2 text-sm text-gray-600">
                    <MapPin class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                    <div class="flex-1">
                        <p>{{ address.address_line_1 }}</p>
                        <p v-if="address.address_line_2">{{ address.address_line_2 }}</p>
                        <p>{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
                        <p>{{ address.country }}</p>
                    </div>
                </div>
            </div>

            <!-- Delivery Instructions -->
            <div v-if="address.delivery_instructions" class="mb-4">
                <div class="flex items-start space-x-2 text-sm">
                    <MessageSquare class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                    <div class="flex-1">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Catatan Pengiriman</p>
                        <p class="text-gray-600">{{ address.delivery_instructions }}</p>
                    </div>
                </div>
            </div>

            <!-- Usage Stats -->
            <div class="flex items-center justify-between border-t border-gray-100 pt-4 text-xs text-gray-500">
                <span>Dibuat {{ formatDate(address.created_at) }}</span>
                <div class="flex items-center space-x-4">
                    <span class="flex items-center">
                        <Package class="mr-1 h-3 w-3" />
                        {{ getOrderCount(address.id) }} pesanan
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex justify-between">
                <Button variant="ghost" size="sm" @click="$emit('edit', address)" class="text-gray-600 hover:text-gray-900">
                    <Edit class="mr-2 h-4 w-4" />
                    Edit
                </Button>
                <Button
                    v-if="!address.is_default"
                    variant="ghost"
                    size="sm"
                    @click="$emit('set-default', address)"
                    class="text-green-600 hover:text-green-700"
                >
                    <Star class="mr-2 h-4 w-4" />
                    Jadikan Utama
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Building, Edit, Home, MapPin, MessageSquare, MoreVertical, Package, Phone, Star, Trash2, User } from 'lucide-vue-next';

interface UserAddress {
    id: number;
    label: string;
    recipient_name: string;
    phone_number: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    delivery_instructions?: string;
    address_type: 'residential' | 'commercial' | 'apartment';
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

defineProps<{
    address: UserAddress;
}>();

defineEmits<{
    edit: [address: UserAddress];
    delete: [address: UserAddress];
    'set-default': [address: UserAddress];
}>();

// Methods
const getTypeIcon = (type: string) => {
    const icons = {
        home: Home,
        office: Building,
        other: MapPin,
    };
    return icons[type as keyof typeof icons] || MapPin;
};

const getTypeIconClass = (type: string) => {
    const classes = {
        home: 'bg-green-100 text-green-600',
        office: 'bg-blue-100 text-blue-600',
        other: 'bg-purple-100 text-purple-600',
    };
    return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-600';
};

const getTypeLabel = (type: string) => {
    const labels = {
        home: 'Rumah',
        office: 'Kantor',
        other: 'Lainnya',
    };
    return labels[type as keyof typeof labels] || type;
};

const formatPhoneNumber = (phone: string) => {
    // Format Indonesian phone number
    if (phone.startsWith('08')) {
        return phone.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
    }
    return phone;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getOrderCount = (addressId: number) => {
    return Math.floor(Math.random() * 20);
};
</script>
