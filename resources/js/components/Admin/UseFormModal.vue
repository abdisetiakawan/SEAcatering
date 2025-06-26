<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ isEditing ? 'Edit User' : 'Add New User' }}
                </h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Basic Info -->
                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Basic Information</h3>

                            <!-- Name -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Full Name *</label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    :class="{ 'border-red-500': errors?.name }"
                                    required
                                />
                                <p v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Email Address *</label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    :class="{ 'border-red-500': errors?.email }"
                                    required
                                />
                                <p v-if="errors?.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                            </div>

                            <!-- Phone -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Phone Number</label>
                                <input
                                    type="tel"
                                    v-model="form.phone"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    :class="{ 'border-red-500': errors?.phone }"
                                    placeholder="+62..."
                                />
                                <p v-if="errors?.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                            </div>

                            <!-- Password -->
                            <div v-if="!isEditing" class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Password *</label>
                                <input
                                    type="password"
                                    v-model="form.password"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    :class="{ 'border-red-500': errors?.password }"
                                    required
                                />
                                <p v-if="errors?.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                            </div>

                            <!-- Password Confirmation -->
                            <div v-if="!isEditing" class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Confirm Password *</label>
                                <input
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- Role & Status -->
                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Role & Status</h3>

                            <!-- Role -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Role</label>
                                <select v-model="form.role" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                    <option value="customer">Customer</option>
                                    <option value="chef">Chef</option>
                                    <option value="driver">Driver</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <!-- Admin Status -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.is_admin" class="mr-2 text-blue-600" />
                                    <span class="text-sm text-gray-700">Admin Access</span>
                                </label>
                            </div>

                            <!-- Email Verified -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.email_verified" class="mr-2 text-blue-600" />
                                    <span class="text-sm text-gray-700">Email Verified</span>
                                </label>
                            </div>

                            <!-- Active Status -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.is_active" class="mr-2 text-blue-600" />
                                    <span class="text-sm text-gray-700">Active Account</span>
                                </label>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Additional Information</h3>

                            <!-- Date of Birth -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Date of Birth</label>
                                <input type="date" v-model="form.date_of_birth" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                            </div>

                            <!-- Gender -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Gender</label>
                                <select v-model="form.gender" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Address</label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    placeholder="Enter full address..."
                                ></textarea>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Admin Notes</label>
                                <textarea
                                    v-model="form.admin_notes"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    placeholder="Internal notes about this user..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Specific Fields -->
                <div v-if="form.role === 'driver'" class="mt-6 rounded-lg bg-blue-50 p-4">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Driver Information</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">License Number</label>
                            <input
                                type="text"
                                v-model="form.license_number"
                                class="w-full rounded-md border border-gray-300 px-3 py-2"
                                placeholder="Driver license number"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Vehicle Type</label>
                            <select v-model="form.vehicle_type" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                <option value="">Select Vehicle</option>
                                <option value="motorcycle">Motorcycle</option>
                                <option value="car">Car</option>
                                <option value="van">Van</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Vehicle Plate</label>
                            <input
                                type="text"
                                v-model="form.vehicle_plate"
                                class="w-full rounded-md border border-gray-300 px-3 py-2"
                                placeholder="Vehicle plate number"
                            />
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_available" class="mr-2 text-blue-600" />
                                <span class="text-sm text-gray-700">Available for Delivery</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-3 border-t pt-6">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        v-if="isEditing"
                        type="button"
                        @click="resetPassword"
                        class="rounded-md border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-700 hover:bg-yellow-100"
                    >
                        Reset Password
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ processing ? 'Saving...' : isEditing ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

type UserRole = 'customer' | 'chef' | 'driver' | 'admin';
type Gender = 'male' | 'female' | 'other' | '';
type VehicleType = 'motorcycle' | 'car' | 'van' | '';

interface User {
    id: number;
    name: string;
    email: string;
    phone?: string;
    role: UserRole;
    is_admin: boolean;
    email_verified_at?: string;
    is_active: boolean;
    date_of_birth?: string;
    gender?: Gender;
    address?: string;
    admin_notes?: string;
    // Driver specific
    license_number?: string;
    vehicle_type?: VehicleType;
    vehicle_plate?: string;
    is_available?: boolean;
}

interface UserForm {
    name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
    role: UserRole;
    is_admin: boolean;
    email_verified: boolean;
    is_active: boolean;
    date_of_birth: string;
    gender: Gender;
    address: string;
    admin_notes: string;
    // Driver specific
    license_number: string;
    vehicle_type: VehicleType;
    vehicle_plate: string;
    is_available: boolean;
}

interface ValidationErrors {
    name?: string;
    email?: string;
    phone?: string;
    password?: string;
}

interface Props {
    show: boolean;
    user?: User;
    errors?: ValidationErrors;
}

interface Emits {
    (e: 'close'): void;
    (e: 'saved'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const processing = ref<boolean>(false);
const isEditing = computed((): boolean => !!props.user?.id);

const form = ref<UserForm>({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: 'customer',
    is_admin: false,
    email_verified: false,
    is_active: true,
    date_of_birth: '',
    gender: '',
    address: '',
    admin_notes: '',
    // Driver specific
    license_number: '',
    vehicle_type: '',
    vehicle_plate: '',
    is_available: true,
});

const resetForm = (): void => {
    if (props.user) {
        // Edit mode - populate form with user data
        form.value = {
            name: props.user.name || '',
            email: props.user.email || '',
            phone: props.user.phone || '',
            password: '',
            password_confirmation: '',
            role: props.user.role || 'customer',
            is_admin: props.user.is_admin || false,
            email_verified: !!props.user.email_verified_at,
            is_active: props.user.is_active !== false,
            date_of_birth: props.user.date_of_birth || '',
            gender: (props.user.gender as Gender) || '',
            address: props.user.address || '',
            admin_notes: props.user.admin_notes || '',
            // Driver specific
            license_number: props.user.license_number || '',
            vehicle_type: (props.user.vehicle_type as VehicleType) || '',
            vehicle_plate: props.user.vehicle_plate || '',
            is_available: props.user.is_available !== false,
        };
    } else {
        // Create mode - reset to defaults
        form.value = {
            name: '',
            email: '',
            phone: '',
            password: '',
            password_confirmation: '',
            role: 'customer',
            is_admin: false,
            email_verified: false,
            is_active: true,
            date_of_birth: '',
            gender: '',
            address: '',
            admin_notes: '',
            // Driver specific
            license_number: '',
            vehicle_type: '',
            vehicle_plate: '',
            is_available: true,
        };
    }
};

const submitForm = (): void => {
    processing.value = true;

    const url = isEditing.value ? `/admin/users/${props.user?.id}` : '/admin/users';

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, form.value, {
        onSuccess: () => {
            emit('saved');
            closeModal();
        },
        onError: () => {
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const resetPassword = (): void => {
    if (confirm("Are you sure you want to reset this user's password? They will receive an email with a new password.")) {
        router.post(
            `/admin/users/${props.user?.id}/reset-password`,
            {},
            {
                onSuccess: () => {
                    alert('Password reset email sent successfully!');
                },
            },
        );
    }
};

const closeModal = (): void => {
    resetForm();
    emit('close');
};

watch(
    () => props.show,
    (newVal: boolean) => {
        if (newVal) {
            resetForm();
        }
    },
);
</script>
