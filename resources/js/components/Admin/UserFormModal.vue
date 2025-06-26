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
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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
                                <select
                                    v-model="form.role"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="customer">Customer</option>
                                    <option value="chef">Chef</option>
                                    <option value="driver">Driver</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <!-- Admin Status -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_admin"
                                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">Admin Access</span>
                                </label>
                            </div>

                            <!-- Email Verified -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.email_verified"
                                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">Email Verified</span>
                                </label>
                            </div>

                            <!-- Active Status -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_active"
                                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
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
                                <input
                                    type="date"
                                    v-model="form.date_of_birth"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>

                            <!-- Gender -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Gender</label>
                                <select
                                    v-model="form.gender"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
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
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Enter full address..."
                                ></textarea>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Admin Notes</label>
                                <textarea
                                    v-model="form.admin_notes"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Internal notes about this user..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Specific Fields -->
                <div v-if="form.role === 'driver'" class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <h3 class="mb-4 flex items-center text-lg font-medium text-gray-900">
                        <svg class="mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        Driver Information
                    </h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">License Number</label>
                            <input
                                type="text"
                                v-model="form.license_number"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="Driver license number"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Vehicle Type</label>
                            <select
                                v-model="form.vehicle_type"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
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
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="Vehicle plate number"
                            />
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.is_available"
                                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Available for Delivery</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-3 border-t border-gray-200 pt-6">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                    >
                        Cancel
                    </button>
                    <button
                        v-if="isEditing"
                        type="button"
                        @click="resetPassword"
                        class="rounded-md border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-700 transition-colors duration-200 hover:bg-yellow-100 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 focus:outline-none"
                    >
                        Reset Password
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="processing" class="flex items-center">
                            <svg
                                class="mr-2 -ml-1 h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Saving...
                        </span>
                        <span v-else>
                            {{ isEditing ? 'Update User' : 'Create User' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

// Type Definitions
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
    email_verified_at?: string | null;
    is_active: boolean;
    date_of_birth?: string | null;
    gender?: Gender;
    address?: string | null;
    admin_notes?: string | null;
    created_at?: string;
    updated_at?: string;
    // Driver specific fields
    license_number?: string | null;
    vehicle_type?: VehicleType;
    vehicle_plate?: string | null;
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
    // Driver specific fields
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
    password_confirmation?: string;
    role?: string;
    [key: string]: string | undefined;
}

interface Props {
    show: boolean;
    user?: User | null;
    errors?: ValidationErrors;
}

interface Emits {
    (e: 'close'): void;
    (e: 'saved'): void;
}

// Component Setup
const props = withDefaults(defineProps<Props>(), {
    show: false,
    user: null,
    errors: () => ({}),
});

const emit = defineEmits<Emits>();

// Reactive State
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
    // Driver specific fields
    license_number: '',
    vehicle_type: '',
    vehicle_plate: '',
    is_available: true,
});

// Methods
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
            // Driver specific fields
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
            // Driver specific fields
            license_number: '',
            vehicle_type: '',
            vehicle_plate: '',
            is_available: true,
        };
    }
};

const submitForm = async (): Promise<void> => {
    processing.value = true;

    try {
        const url = isEditing.value ? `/admin/users/${props.user?.id}` : '/admin/users';

        const method = isEditing.value ? 'put' : 'post';

        router[method](url, form.value, {
            onSuccess: () => {
                emit('saved');
                closeModal();
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
                processing.value = false;
            },
            onFinish: () => {
                processing.value = false;
            },
        });
    } catch (error) {
        console.error('Error submitting form:', error);
        processing.value = false;
    }
};

const resetPassword = (): void => {
    if (!props.user?.id) return;

    if (confirm("Are you sure you want to reset this user's password? They will receive an email with a new password.")) {
        router.post(
            `/admin/users/${props.user.id}/reset-password`,
            {},
            {
                onSuccess: () => {
                    alert('Password reset email sent successfully!');
                },
                onError: (errors) => {
                    console.error('Password reset error:', errors);
                    alert('Failed to send password reset email. Please try again.');
                },
            },
        );
    }
};

const closeModal = (): void => {
    resetForm();
    emit('close');
};

// Watchers
watch(
    () => props.show,
    async (newVal: boolean) => {
        if (newVal) {
            await nextTick();
            resetForm();
        }
    },
);

// Watch for role changes to reset driver fields
watch(
    () => form.value.role,
    (newRole: UserRole) => {
        if (newRole !== 'driver') {
            form.value.license_number = '';
            form.value.vehicle_type = '';
            form.value.vehicle_plate = '';
            form.value.is_available = true;
        }
    },
);
</script>

<style scoped>
/* Custom styles for better UX */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Focus styles for better accessibility */
input:focus,
select:focus,
textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Smooth transitions */
button {
    transition: all 0.2s ease-in-out;
}

/* Custom checkbox styles */
input[type='checkbox']:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

/* Driver section highlight */
.bg-blue-50 {
    background-color: #eff6ff;
}

/* Error state styles */
.border-red-500 {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
</style>
