<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Profil sozlamalari</h1>
            <p class="text-gray-600 mt-1">Shaxsiy ma'lumotlarni tahrirlash</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Content -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Form -->
            <div class="lg:col-span-2 card">
                <h2 class="text-xl font-semibold mb-6">Shaxsiy ma'lumotlar</h2>

                <!-- Error Display -->
                <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                    </ul>
                </div>

                <!-- Success Message -->
                <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800">{{ successMessage }}</p>
                </div>

                <form @submit.prevent="updateProfile" class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            F.I.O <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.full_name"
                            type="text"
                            class="form-input w-full"
                            required
                        />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Telefon
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-input w-full"
                            placeholder="998901234567"
                            maxlength="12"
                        />
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
                            placeholder="email@example.com"
                        />
                    </div>

                    <!-- Avatar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rasm
                        </label>
                        <input
                            @change="handleFileChange"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg"
                            class="form-input w-full"
                        />
                        <p class="text-xs text-gray-500 mt-1">Max: 2MB, Format: JPG, PNG</p>

                        <!-- Preview -->
                        <div v-if="avatarPreview || user?.avatar" class="mt-3">
                            <img
                                :src="avatarPreview || `/storage/${user.avatar}`"
                                alt="Avatar"
                                class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
                            />
                        </div>
                    </div>

                    <!-- Read-only Fields -->
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <h3 class="font-semibold text-gray-900 mb-3">O'zgartirib bo'lmaydigan ma'lumotlar:</h3>
                        <div>
                            <p class="text-sm text-gray-600">Fakultet:</p>
                            <p class="font-medium">{{ user?.faculty?.name || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kafedra:</p>
                            <p class="font-medium">{{ user?.department?.name || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Passport:</p>
                            <p class="font-medium">{{ user?.passport_series }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Ilmiy daraja:</p>
                            <p class="font-medium">{{ user?.scientific_degree || '—' }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" :disabled="submitting" class="btn-primary flex-1 disabled:opacity-50">
                            {{ submitting ? 'Saqlanmoqda...' : 'Saqlash' }}
                        </button>
                        <router-link to="/teacher/dashboard" class="btn-secondary flex-1 text-center">
                            Bekor qilish
                        </router-link>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <h2 class="text-xl font-semibold mb-6">Parolni o'zgartirish</h2>

                <!-- Password Error -->
                <div v-if="passwordErrors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                        <li v-for="(error, index) in passwordErrors" :key="index">{{ error }}</li>
                    </ul>
                </div>

                <!-- Password Success -->
                <div v-if="passwordSuccess" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800">{{ passwordSuccess }}</p>
                </div>

                <form @submit.prevent="changePassword" class="space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Joriy parol <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            class="form-input w-full"
                            required
                        />
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Yangi parol <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.new_password"
                            type="password"
                            class="form-input w-full"
                            required
                            minlength="8"
                        />
                        <p class="text-xs text-gray-500 mt-1">Kamida 8 ta belgi</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Parolni tasdiqlang <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.new_password_confirmation"
                            type="password"
                            class="form-input w-full"
                            required
                        />
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="changingPassword" class="btn-primary w-full disabled:opacity-50">
                        {{ changingPassword ? 'O\'zgartirilmoqda...' : 'Parolni o\'zgartirish' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const submitting = ref(false);
const changingPassword = ref(false);
const user = ref(null);
const errors = ref([]);
const passwordErrors = ref([]);
const successMessage = ref('');
const passwordSuccess = ref('');
const avatarPreview = ref(null);

const form = ref({
    full_name: '',
    phone: '',
    email: '',
    avatar: null
});

const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const loadProfile = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/profile');
        if (response.data.success) {
            user.value = response.data.data;
            form.value = {
                full_name: user.value.full_name || '',
                phone: user.value.phone || '',
                email: user.value.email || '',
                avatar: null
            };
        }
    } catch (error) {
        console.error('Profil yuklashda xatolik:', error);
        alert('Profil yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Fayl hajmi 2MB dan katta bo\'lmasligi kerak');
            event.target.value = '';
            return;
        }

        form.value.avatar = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const updateProfile = async () => {
    errors.value = [];
    successMessage.value = '';
    submitting.value = true;

    try {
        const formData = new FormData();
        formData.append('full_name', form.value.full_name);
        if (form.value.phone) formData.append('phone', form.value.phone);
        if (form.value.email) formData.append('email', form.value.email);
        if (form.value.avatar) formData.append('avatar', form.value.avatar);

        const response = await axios.post('/api/teacher/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            user.value = response.data.data;
            successMessage.value = 'Profil muvaffaqiyatli yangilandi';
            avatarPreview.value = null;

            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        }
    } catch (error) {
        console.error('Profil yangilashda xatolik:', error);
        if (error.response?.data?.errors) {
            errors.value = Object.values(error.response.data.errors).flat();
        } else {
            errors.value = [error.response?.data?.message || 'Xatolik yuz berdi'];
        }
    } finally {
        submitting.value = false;
    }
};

const changePassword = async () => {
    passwordErrors.value = [];
    passwordSuccess.value = '';
    changingPassword.value = true;

    try {
        const response = await axios.post('/api/teacher/profile/change-password', passwordForm.value);

        if (response.data.success) {
            passwordSuccess.value = 'Parol muvaffaqiyatli o\'zgartirildi';
            passwordForm.value = {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            };

            setTimeout(() => {
                passwordSuccess.value = '';
            }, 3000);
        }
    } catch (error) {
        console.error('Parol o\'zgartirishda xatolik:', error);
        if (error.response?.data?.errors) {
            passwordErrors.value = Object.values(error.response.data.errors).flat();
        } else {
            passwordErrors.value = [error.response?.data?.message || 'Xatolik yuz berdi'];
        }
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    loadProfile();
});
</script>
