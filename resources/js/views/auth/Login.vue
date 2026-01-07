<template>
    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500">
        <!-- Background Shapes -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-md w-full relative z-10">
            <!-- Logo -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Professor Platform</h1>
                <p class="text-blue-100">O'qituvchilarni baholash tizimi</p>
            </div>

            <!-- Card -->
            <div class="card-gradient animate-fade-in">
                <h2 class="text-2xl font-bold text-center mb-6 text-gradient">
                    Xush kelibsiz! 👋
                </h2>

                <!-- Error -->
                <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg animate-slide-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleLogin" class="space-y-5">
                    <!-- Passport -->
                    <div>
                        <label class="form-label">Passport seriya raqami</label>
                        <input
                            v-model="form.passport_series"
                            @input="formatPassport"
                            type="text"
                            placeholder="AA1234567"
                            maxlength="9"
                            class="form-input font-mono text-lg"
                            :class="{ 'border-red-500': errors.passport_series }"
                            required
                        />
                        <p v-if="errors.passport_series" class="mt-2 text-sm text-red-600">
                            {{ errors.passport_series }}
                        </p>
                        <p v-else class="mt-2 text-xs text-gray-500">Masalan: AA1234567</p>
                    </div>

                    <!-- Password - YANGILANGAN -->
                    <div>
                        <label class="form-label">Parol (Tug'ilgan kun)</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="dd.mm.yyyy"
                                maxlength="10"
                                class="form-input font-mono text-lg pr-12"
                                :class="{ 'border-red-500': errors.password }"
                                required
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <!-- Eye Icon (Show) -->
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <!-- Eye Slash Icon (Hide) -->
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-600">
                            {{ errors.password }}
                        </p>
                        <p v-else class="mt-2 text-xs text-gray-500">Masalan: 01.01.1990</p>
                    </div>

                    <!-- Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full btn-primary py-4 text-base flex items-center justify-center gap-2"
                    >
                        <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ loading ? 'Kirilmoqda...' : 'Kirish' }}</span>
                    </button>
                </form>

                <!-- Test Accounts -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 text-center mb-3 uppercase">Test Hisoblar</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <span class="font-semibold text-gray-700">👨‍💼 Admin</span>
                            <span class="font-mono text-gray-600">AA1234567 / 01.01.1990</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="font-semibold text-gray-700">👔 Prorektor</span>
                            <span class="font-mono text-gray-600">AB1234567 / 15.05.1985</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <span class="font-semibold text-gray-700">👨‍🏫 O'qituvchi</span>
                            <span class="font-mono text-gray-600">AC1111111 / 10.03.1992</span>
                        </div>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600 text-sm">
                        Akkauntingiz yo'qmi?
                        <router-link to="/register" class="font-bold text-blue-600 hover:text-blue-700">
                            Ro'yxatdan o'tish →
                        </router-link>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-white/90 text-sm mt-6">
                © 2026 Professor Platform
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    passport_series: '',
    password: ''
});

const errors = reactive({
    passport_series: '',
    password: ''
});

const loading = ref(false);
const errorMessage = ref('');
const showPassword = ref(false); // YANGI

const formatPassport = (event) => {
    let value = event.target.value;
    value = value.replace(/[^a-zA-Z0-9]/g, '');

    if (value.length <= 2) {
        form.passport_series = value.toUpperCase();
    } else {
        const letters = value.substring(0, 2).toUpperCase();
        const numbers = value.substring(2, 9).replace(/[^0-9]/g, '');
        form.passport_series = letters + numbers;
    }
};

const handleLogin = async () => {
    errors.passport_series = '';
    errors.password = '';
    errorMessage.value = '';

    loading.value = true;

    try {
        const response = await authStore.login({
            passport_series: form.passport_series,
            password: form.password
        });

        const role = response.role;
        if (role === 'admin') {
            router.push('/admin/dashboard');
        } else if (role === 'prorektor') {
            router.push('/prorektor/dashboard');
        } else {
            router.push('/teacher/dashboard');
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            const apiErrors = error.response.data.errors;
            errors.passport_series = apiErrors.passport_series?.[0] || '';
            errors.password = apiErrors.password?.[0] || '';
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'Tizimga kirishda xatolik yuz berdi';
        }
    } finally {
        loading.value = false;
    }
};
</script>
