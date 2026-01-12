<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Profil</h1>
            <p class="text-gray-600 mt-1">Shaxsiy ma'lumotlaringiz</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Profile Content -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Avatar & Basic Info -->
            <div class="lg:col-span-1">
                <div class="card text-center">
                    <!-- Avatar -->
                    <div class="mb-4">
                        <div class="relative inline-block">
                            <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 mx-auto">
                                <img
                                    v-if="profile.avatar"
                                    :src="'/' + profile.avatar"
                                    class="w-full h-full object-cover"
                                    alt="Avatar"
                                >
                                <div v-else class="w-full h-full flex items-center justify-center bg-green-600 text-white text-4xl font-bold">
                                    {{ getInitials(profile.full_name) }}
                                </div>
                            </div>
                            <label class="absolute bottom-0 right-0 bg-green-600 text-white rounded-full p-2 cursor-pointer hover:bg-green-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <input type="file" @change="handleAvatarChange" accept="image/*" class="hidden">
                            </label>
                        </div>
                    </div>

                    <!-- Name & Role -->
                    <h2 class="text-xl font-bold text-gray-900 mb-1">{{ profile.full_name }}</h2>
                    <p class="text-sm text-gray-600 mb-2">{{ profile.scientific_degree }}</p>
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <span class="badge badge-success">ProRektor</span>
                    </div>

                    <!-- Info Cards -->
                    <div class="space-y-3 text-left mt-6">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Fakultet</p>
                            <p class="font-semibold text-gray-900">{{ profile.faculty?.name || '—' }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Kafedra</p>
                            <p class="font-semibold text-gray-900">{{ profile.department?.name || '—' }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Passport</p>
                            <p class="font-semibold text-gray-900">{{ profile.passport_series }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Edit Forms -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Info -->
                <div class="card">
                    <h3 class="text-lg font-bold mb-4">Shaxsiy ma'lumotlar</h3>

                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <!-- Full Name ← O'ZGARTIRILADI -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                F.I.O <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.full_name"
                                type="text"
                                required
                                class="form-input w-full"
                                :class="{ 'border-red-500': errors.full_name }"
                                placeholder="To'liq ism-familiya"
                            >
                            <p v-if="errors.full_name" class="text-red-500 text-xs mt-1">{{ errors.full_name[0] }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Telefon
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="form-input w-full"
                                    :class="{ 'border-red-500': errors.phone }"
                                    placeholder="+998901234567"
                                >
                                <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-input w-full"
                                    :class="{ 'border-red-500': errors.email }"
                                    placeholder="email@example.com"
                                >
                                <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end pt-4 border-t">
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="btn-primary disabled:opacity-50"
                            >
                                {{ submitting ? 'Saqlanmoqda...' : 'Saqlash' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="card">
                    <h3 class="text-lg font-bold mb-4">Parolni o'zgartirish</h3>

                    <form @submit.prevent="changePassword" class="space-y-4">
                        <!-- Current Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Joriy parol <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                class="form-input w-full"
                                :class="{ 'border-red-500': passwordErrors.current_password }"
                            >
                            <p v-if="passwordErrors.current_password" class="text-red-500 text-xs mt-1">
                                {{ passwordErrors.current_password[0] }}
                            </p>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Yangi parol <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="passwordForm.new_password"
                                type="password"
                                required
                                class="form-input w-full"
                                :class="{ 'border-red-500': passwordErrors.new_password }"
                            >
                            <p class="text-xs text-gray-500 mt-1">Kamida 8 ta belgi</p>
                            <p v-if="passwordErrors.new_password" class="text-red-500 text-xs mt-1">
                                {{ passwordErrors.new_password[0] }}
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Parolni tasdiqlang <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="passwordForm.new_password_confirmation"
                                type="password"
                                required
                                class="form-input w-full"
                            >
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end pt-4 border-t">
                            <button
                                type="submit"
                                :disabled="changingPassword"
                                class="btn-primary disabled:opacity-50"
                            >
                                {{ changingPassword ? 'O\'zgartirilmoqda...' : 'Parolni o\'zgartirish' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth'; // ← Auth store'ni update qilish uchun

const authStore = useAuthStore();
const loading = ref(false);
const submitting = ref(false);
const changingPassword = ref(false);

const profile = ref({});
const form = ref({
    full_name: '', // ← QOSHILDI
    phone: '',
    email: ''
});

const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const errors = ref({});
const passwordErrors = ref({});

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
    }
    return name.charAt(0).toUpperCase();
};

const loadProfile = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/prorektor/profile');

        if (response.data.success) {
            profile.value = response.data.data;
            form.value.full_name = profile.value.full_name || ''; // ← QOSHILDI
            form.value.phone = profile.value.phone || '';
            form.value.email = profile.value.email || '';
        }
    } catch (error) {
        console.error('Load profile error:', error);
    } finally {
        loading.value = false;
    }
};

const handleAvatarChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('full_name', form.value.full_name); // ← QOSHILDI
    formData.append('phone', form.value.phone || '');
    formData.append('email', form.value.email || '');

    try {
        const response = await axios.post('/api/prorektor/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            params: { _method: 'PUT' }
        });

        if (response.data.success) {
            profile.value = response.data.data;

            // Update auth store
            authStore.user = response.data.data;

            alert('Rasm yangilandi');
        }
    } catch (error) {
        console.error('Avatar upload error:', error);
        alert('Rasm yuklashda xatolik');
    }
};

const updateProfile = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        const response = await axios.put('/api/prorektor/profile', form.value);

        if (response.data.success) {
            profile.value = response.data.data;

            // Update auth store (sidebar/navbar yangilanadi)
            authStore.user = response.data.data;

            alert('Profil yangilandi');
        }
    } catch (error) {
        console.error('Update profile error:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            alert(error.response?.data?.message || 'Xatolik yuz berdi');
        }
    } finally {
        submitting.value = false;
    }
};

const changePassword = async () => {
    changingPassword.value = true;
    passwordErrors.value = {};

    try {
        const response = await axios.post('/api/prorektor/profile/change-password', passwordForm.value);

        if (response.data.success) {
            alert('Parol muvaffaqiyatli o\'zgartirildi');
            passwordForm.value = {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            };
        }
    } catch (error) {
        console.error('Change password error:', error);

        if (error.response?.data?.errors) {
            passwordErrors.value = error.response.data.errors;
        } else {
            alert(error.response?.data?.message || 'Xatolik yuz berdi');
        }
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    loadProfile();
});
</script>
