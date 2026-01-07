<template>
    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-green-600 via-emerald-600 to-teal-500">
        <!-- Background Shapes -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-3xl w-full relative z-10">
            <!-- Logo -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Ro'yxatdan o'tish</h1>
                <p class="text-green-100">Professor Platform</p>
            </div>

            <!-- Card -->
            <div class="card-gradient animate-fade-in">
                <h2 class="text-2xl font-bold text-center mb-6 text-gradient">
                    Yangi akkaunt yaratish 🎓
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
                <form @submit.prevent="handleRegister" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Full Name -->
                        <div class="md:col-span-2">
                            <label class="form-label">To'liq ism-familiya *</label>
                            <input
                                v-model="form.full_name"
                                type="text"
                                placeholder="Ahmadov Jamshid Karimovich"
                                class="form-input"
                                :class="{ 'border-red-500': errors.full_name }"
                                required
                            />
                            <p v-if="errors.full_name" class="mt-2 text-sm text-red-600">{{ errors.full_name }}</p>
                        </div>

                        <!-- Faculty -->
                        <div>
                            <label class="form-label">Fakultet *</label>
                            <select
                                v-model="form.faculty_id"
                                @change="loadDepartments"
                                class="form-input"
                                :class="{ 'border-red-500': errors.faculty_id }"
                                required
                            >
                                <option value="">Tanlang...</option>
                                <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                                    {{ faculty.name }}
                                </option>
                            </select>
                            <p v-if="errors.faculty_id" class="mt-2 text-sm text-red-600">{{ errors.faculty_id }}</p>
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="form-label">Kafedra *</label>
                            <select
                                v-model="form.department_id"
                                class="form-input"
                                :class="{ 'border-red-500': errors.department_id }"
                                :disabled="!form.faculty_id"
                                required
                            >
                                <option value="">Tanlang...</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                            <p v-if="errors.department_id" class="mt-2 text-sm text-red-600">{{ errors.department_id }}</p>
                        </div>

                        <!-- Passport -->
                        <div>
                            <label class="form-label">Passport seriya raqami *</label>
                            <input
                                v-model="form.passport_series"
                                @input="formatPassport"
                                type="text"
                                placeholder="AA1234567"
                                maxlength="9"
                                class="form-input font-mono"
                                :class="{ 'border-red-500': errors.passport_series }"
                                required
                            />
                            <p v-if="errors.passport_series" class="mt-2 text-sm text-red-600">{{ errors.passport_series }}</p>
                        </div>

                        <!-- Birth Date -->
                        <div>
                            <label class="form-label">Tug'ilgan kun *</label>
                            <input
                                v-model="form.birth_date"
                                type="date"
                                class="form-input"
                                :class="{ 'border-red-500': errors.birth_date }"
                                required
                            />
                            <p v-if="errors.birth_date" class="mt-2 text-sm text-red-600">{{ errors.birth_date }}</p>
                        </div>

                        <!-- Scientific Degree -->
                        <div>
                            <label class="form-label">Ilmiy daraja *</label>
                            <select
                                v-model="form.scientific_degree"
                                class="form-input"
                                :class="{ 'border-red-500': errors.scientific_degree }"
                                required
                            >
                                <option value="">Tanlang...</option>
                                <option value="Assistent">Assistent</option>
                                <option value="Katta o'qituvchi">Karta o'qituvchi</option>
                                <option value="Dotsent">Dotsent</option>
                                <option value="Professor">Professor</option>
                                <option value="Fan nomzodi">Fan nomzodi</option>
                                <option value="Fan doktori">Fan doktori</option>
                                <option value="PhD">PhD</option>
                                <option value="DSc">DSc</option>
                            </select>
                            <p v-if="errors.scientific_degree" class="mt-2 text-sm text-red-600">{{ errors.scientific_degree }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="form-label">Telefon raqam</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="998901234567"
                                maxlength="12"
                                class="form-input font-mono"
                                :class="{ 'border-red-500': errors.phone }"
                            />
                            <p v-if="errors.phone" class="mt-2 text-sm text-red-600">{{ errors.phone }}</p>
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label class="form-label">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="email@example.com"
                                class="form-input"
                                :class="{ 'border-red-500': errors.email }"
                            />
                            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="form-label">Parol *</label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="Kamida 8 ta belgi"
                                    class="form-input pr-12"
                                    :class="{ 'border-red-500': errors.password }"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="form-label">Parolni tasdiqlash *</label>
                            <div class="relative">
                                <input
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirm ? 'text' : 'password'"
                                    placeholder="Parolni qayta kiriting"
                                    class="form-input pr-12"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirm = !showPasswordConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg v-if="!showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full btn-success py-4 text-base flex items-center justify-center gap-2 mt-6"
                    >
                        <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ loading ? 'Ro\'yxatdan o\'tilmoqda...' : 'Ro\'yxatdan o\'tish' }}</span>
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600 text-sm">
                        Akkauntingiz bormi?
                        <router-link to="/login" class="font-bold text-green-600 hover:text-green-700">
                            ← Kirish
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
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    full_name: '',
    faculty_id: '',
    department_id: '',
    passport_series: '',
    birth_date: '',
    scientific_degree: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const errors = reactive({
    full_name: '',
    faculty_id: '',
    department_id: '',
    passport_series: '',
    birth_date: '',
    scientific_degree: '',
    phone: '',
    email: '',
    password: ''
});

const faculties = ref([]);
const departments = ref([]);
const loading = ref(false);
const errorMessage = ref('');
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties');
        faculties.value = response.data.data || response.data;
    } catch (error) {
        console.error('Fakultetlarni yuklashda xatolik:', error);
    }
};

const loadDepartments = async () => {
    if (!form.faculty_id) {
        departments.value = [];
        form.department_id = '';
        return;
    }

    try {
        const response = await axios.get(`/api/faculties/${form.faculty_id}/departments`);
        departments.value = response.data.data || response.data;
    } catch (error) {
        console.error('Kafedralarni yuklashda xatolik:', error);
    }
};

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

const handleRegister = async () => {
    Object.keys(errors).forEach(key => errors[key] = '');
    errorMessage.value = '';

    loading.value = true;

    try {
        const response = await axios.post('/api/register', form);

        authStore.token = response.data.token;
        authStore.user = response.data.user;
        localStorage.setItem('token', response.data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;

        router.push('/teacher/dashboard');
    } catch (error) {
        if (error.response?.data?.errors) {
            const apiErrors = error.response.data.errors;
            Object.keys(apiErrors).forEach(key => {
                errors[key] = apiErrors[key][0];
            });
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'Ro\'yxatdan o\'tishda xatolik yuz berdi';
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadFaculties();
});
</script>
