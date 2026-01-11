<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">
                    {{ mode === 'create' ? 'Yangi foydalanuvchi' : 'Foydalanuvchini tahrirlash' }}
                </h2>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <!-- Avatar -->
                <div class="flex justify-center mb-4">
                    <div class="relative">
                        <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                            <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" alt="Avatar">
                            <svg v-else class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <label class="absolute bottom-0 right-0 bg-green-600 text-white rounded-full p-2 cursor-pointer hover:bg-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <input type="file" @change="handleAvatarChange" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Full Name -->
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

                <!-- Faculty & Department -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fakultet</label>
                        <select v-model="form.faculty_id" @change="onFacultyChange" class="form-input w-full">
                            <option value="">Tanlang</option>
                            <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                                {{ faculty.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kafedra</label>
                        <select v-model="form.department_id" class="form-input w-full" :disabled="!form.faculty_id">
                            <option value="">Tanlang</option>
                            <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Scientific Degree -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ilmiy daraja</label>
                    <select v-model="form.scientific_degree" class="form-input w-full">
                        <option value="">Tanlang</option>
                        <option value="O'qituvchi">O'qituvchi</option>
                        <option value="Assistent">Assistent</option>
                        <option value="Katta o'qituvchi">Katta o'qituvchi</option>
                        <option value="Dotsent">Dotsent</option>
                        <option value="Professor">Professor</option>
                        <option value="Fan nomzodi">Fan nomzodi</option>
                        <option value="Fan doktori">Fan doktori</option>
                        <option value="PhD">PhD</option>
                        <option value="DSc">DSc</option>
                    </select>
                </div>

                <!-- Passport & Birth Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Passport seriya <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.passport_series"
                            @input="formatPassport"
                            type="text"
                            required
                            maxlength="9"
                            class="form-input w-full uppercase"
                            :class="{ 'border-red-500': errors.passport_series }"
                            placeholder="AA1234567"
                        >
                        <p v-if="errors.passport_series" class="text-red-500 text-xs mt-1">{{ errors.passport_series[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tug'ilgan kun <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.birth_date"
                            type="date"
                            required
                            class="form-input w-full"
                            :class="{ 'border-red-500': errors.birth_date }"
                        >
                        <p v-if="errors.birth_date" class="text-red-500 text-xs mt-1">{{ errors.birth_date[0] }}</p>
                    </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-input w-full"
                            placeholder="+998901234567"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
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

                <!-- Actions -->
                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="btn-primary flex-1 disabled:opacity-50"
                    >
                        {{ submitting ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="btn-secondary"
                    >
                        Bekor qilish
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    mode: {
        type: String,
        default: 'create'
    },
    faculties: Array,
    departments: Array
});

const emit = defineEmits(['close', 'success']);

const form = ref({
    full_name: '',
    faculty_id: '',
    department_id: '',
    scientific_degree: '',
    passport_series: '',
    birth_date: '',
    phone: '',
    email: '',
    avatar: null
});

const errors = ref({}); // ← QOSHILDI
const avatarPreview = ref(null);
const submitting = ref(false);

const filteredDepartments = computed(() => {
    if (!form.value.faculty_id) return [];
    return props.departments.filter(d => d.faculty_id == form.value.faculty_id);
});

watch(() => props.user, (user) => {
    if (user && props.mode === 'edit') {
        form.value = {
            full_name: user.full_name || '',
            faculty_id: user.faculty_id || '',
            department_id: user.department_id || '',
            scientific_degree: user.scientific_degree || '',
            passport_series: user.passport_series || '',
            birth_date: user.birth_date || '',
            phone: user.phone || '',
            email: user.email || '',
            avatar: null
        };

        if (user.avatar) {
            avatarPreview.value = '/' + user.avatar;
        }
    } else {
        // Reset form for create mode
        form.value = {
            full_name: '',
            faculty_id: '',
            department_id: '',
            scientific_degree: '',
            passport_series: '',
            birth_date: '',
            phone: '',
            email: '',
            avatar: null
        };
        avatarPreview.value = null;
    }

    errors.value = {}; // ← RESET ERRORS
}, { immediate: true });

const onFacultyChange = () => {
    form.value.department_id = '';
};

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const formatPassport = (event) => {
    let value = event.target.value;
    value = value.replace(/[^a-zA-Z0-9]/g, '');

    if (value.length <= 2) {
        form.value.passport_series = value.toUpperCase();
    } else {
        const letters = value.substring(0, 2).toUpperCase();
        const numbers = value.substring(2, 9).replace(/[^0-9]/g, '');
        form.value.passport_series = letters + numbers;
    }
};

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {}; // ← RESET

    try {
        const formData = new FormData();

        // Append all form fields
        Object.keys(form.value).forEach(key => {
            if (key === 'avatar' && form.value[key]) {
                formData.append(key, form.value[key]);
            } else if (form.value[key] !== null && form.value[key] !== '') {
                formData.append(key, form.value[key]);
            }
        });

        let response;

        if (props.mode === 'create') {
            response = await axios.post('/api/admin/users', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            // Add _method for Laravel
            formData.append('_method', 'PUT');

            response = await axios.post(`/api/admin/users/${props.user.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }

        if (response.data.success) {
            emit('success');
            emit('close');
        }
    } catch (error) {
        console.error('Submit error:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors; // ← SET ERRORS
            const errorMessages = Object.values(error.response.data.errors).flat().join('\n');
            alert('Validatsiya xatoligi:\n' + errorMessages);
        } else {
            alert(error.response?.data?.message || 'Xatolik yuz berdi');
        }
    } finally {
        submitting.value = false;
    }
};
</script>
