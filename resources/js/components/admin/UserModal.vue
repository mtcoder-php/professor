<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">
                {{ mode === 'create' ? 'Yangi o\'qituvchi' : 'O\'qituvchini tahrirlash' }}
            </h2>

            <!-- Error Display -->
            <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            F.I.O <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.full_name"
                            type="text"
                            class="form-input w-full"
                            placeholder="Karimov Jamshid Akramovich"
                            required
                        />
                    </div>

                    <!-- Faculty -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Fakultet <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.faculty_id" @change="onFacultyChange" class="form-input w-full" required>
                            <option value="">Fakultetni tanlang</option>
                            <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                                {{ faculty.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kafedra <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.department_id" class="form-input w-full" required :disabled="!form.faculty_id">
                            <option value="">Kafedarani tanlang</option>
                            <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Passport Series -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Passport seriya raqami <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.passport_series"
                            @input="formatPassport"
                            type="text"
                            class="form-input w-full uppercase"
                            placeholder="AA1234567"
                            maxlength="9"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-1">Format: AA1234567</p>
                    </div>

                    <!-- Birth Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tug'ilgan kun <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.birth_date"
                            type="date"
                            class="form-input w-full"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-1">Parol: dd.mm.yyyy formatda</p>
                    </div>

                    <!-- Scientific Degree -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ilmiy daraja <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.scientific_degree" class="form-input w-full" required>
                            <option value="">Tanlang</option>
                            <option value="Fan doktori">Fan doktori</option>
                            <option value="PhD">PhD</option>
                            <option value="DSc">DSc</option>
                            <option value="Dotsent">Dotsent</option>
                            <option value="Professor">Professor</option>
                            <option value="Katta o'qituvchi">Katta o'qituvchi</option>
                            <option value="O'qituvchi">O'qituvchi</option>
                            <option value="Assistent">Assistent</option>
                        </select>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rol <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.role" class="form-input w-full" required>
                            <option value="teacher">Teacher (O'qituvchi)</option>
                            <option value="prorektor">ProRektor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Telefon raqami
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-input w-full"
                            placeholder="998901234567"
                            maxlength="12"
                        />
                        <p class="text-xs text-gray-500 mt-1">Format: 998XXXXXXXXX</p>
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
                            placeholder="karimov@university.uz"
                        />
                    </div>

                    <!-- Avatar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rasm (Avatar)
                        </label>
                        <input
                            @change="handleFileChange"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg"
                            class="form-input w-full"
                        />
                        <p class="text-xs text-gray-500 mt-1">Max: 2MB, Format: JPG, PNG</p>

                        <!-- Preview -->
                        <div v-if="avatarPreview || (mode === 'edit' && user?.avatar)" class="mt-3">
                            <img
                                :src="avatarPreview || `/storage/${user.avatar}`"
                                alt="Avatar preview"
                                class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
                            />
                        </div>
                    </div>
                </div>

                <!-- Password Info -->
                <div v-if="mode === 'create'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Parol avtomatik yaratiladi:</strong> Tug'ilgan kun dd.mm.yyyy formatda
                        <br />
                        <span class="text-xs">Misol: 15.03.1990</span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t">
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
                        class="btn-secondary flex-1"
                    >
                        Bekor qilish
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    faculties: Array,
    departments: Array,
    mode: String
});

const emit = defineEmits(['close', 'success']);

const form = ref({
    full_name: '',
    faculty_id: '',
    department_id: '',
    passport_series: '',
    birth_date: '',
    scientific_degree: '',
    phone: '',
    email: '',
    avatar: null,
    role: 'teacher'
});

const submitting = ref(false);
const errors = ref([]);
const avatarPreview = ref(null);

// Filter departments by selected faculty
const filteredDepartments = computed(() => {
    if (!form.value.faculty_id) return [];
    return props.departments.filter(dept => dept.faculty_id == form.value.faculty_id);
});

// Watch for user changes (edit mode)
watch(() => props.user, (user) => {
    if (user && props.mode === 'edit') {
        form.value = {
            full_name: user.full_name || '',
            faculty_id: user.faculty_id || '',
            department_id: user.department_id || '',
            passport_series: user.passport_series || '',
            birth_date: user.birth_date ? user.birth_date.split('T')[0] : '',
            scientific_degree: user.scientific_degree || '',
            phone: user.phone || '',
            email: user.email || '',
            avatar: null,
            role: user.roles?.[0]?.name || 'teacher'
        };
    }
}, { immediate: true });

// Format passport to uppercase
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

// Handle faculty change
const onFacultyChange = () => {
    // Reset department when faculty changes
    form.value.department_id = '';
};

// Handle file upload
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Fayl hajmi 2MB dan katta bo\'lmasligi kerak');
            event.target.value = '';
            return;
        }

        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            alert('Faqat JPG va PNG formatdagi rasmlar qabul qilinadi');
            event.target.value = '';
            return;
        }

        form.value.avatar = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleSubmit = async () => {
    errors.value = [];
    submitting.value = true;

    try {
        const formData = new FormData();

        // Append all form fields
        formData.append('full_name', form.value.full_name);
        formData.append('faculty_id', form.value.faculty_id);
        formData.append('department_id', form.value.department_id);
        formData.append('passport_series', form.value.passport_series.toUpperCase());
        formData.append('birth_date', form.value.birth_date);
        formData.append('scientific_degree', form.value.scientific_degree);
        formData.append('role', form.value.role);

        if (form.value.phone) formData.append('phone', form.value.phone);
        if (form.value.email) formData.append('email', form.value.email);
        if (form.value.avatar) formData.append('avatar', form.value.avatar);

        if (props.mode === 'create') {
            await axios.post('/api/admin/users', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            // Laravel doesn't support multipart/form-data with PUT, use POST with _method
            formData.append('_method', 'PUT');
            await axios.post(`/api/admin/users/${props.user.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }

        emit('success');
        alert(props.mode === 'create' ? 'O\'qituvchi yaratildi' : 'O\'qituvchi yangilandi');
    } catch (error) {
        console.error('Submit error:', error);

        if (error.response?.data?.errors) {
            errors.value = Object.values(error.response.data.errors).flat();
        } else {
            errors.value = [error.response?.data?.message || 'Xatolik yuz berdi'];
        }
    } finally {
        submitting.value = false;
    }
};
</script>
