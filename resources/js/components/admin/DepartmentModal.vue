<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">
                {{ mode === 'create' ? 'Yangi kafedra' : 'Kafedrani tahrirlash' }}
            </h2>

            <!-- Error Display -->
            <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kafedra nomi <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="form-input w-full"
                        placeholder="Tarix kafedrasi"
                        required
                    />
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kafedra kodi <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.code"
                        type="text"
                        class="form-input w-full uppercase"
                        placeholder="TK"
                        maxlength="10"
                        required
                    />
                </div>

                <!-- Faculty -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fakultet <span class="text-red-500">*</span>
                    </label>
                    <select v-model="form.faculty_id" class="form-input w-full" required>
                        <option value="">Fakultetni tanlang</option>
                        <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                            {{ faculty.name }}
                        </option>
                    </select>
                </div>

                <!-- Head Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kafedra mudiri
                    </label>
                    <input
                        v-model="form.head_name"
                        type="text"
                        class="form-input w-full"
                        placeholder="Rahimov Jamshid"
                    />
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
                        placeholder="tarix@university.uz"
                    />
                </div>

                <!-- Room Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Xona raqami
                    </label>
                    <input
                        v-model="form.room_number"
                        type="text"
                        class="form-input w-full"
                        placeholder="203"
                    />
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                    />
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                        Faol kafedra
                    </label>
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
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    department: Object,
    faculties: Array,
    mode: String
});

const emit = defineEmits(['close', 'success']);

const form = ref({
    name: '',
    code: '',
    faculty_id: '',
    head_name: '',
    phone: '',
    email: '',
    room_number: '',
    is_active: true
});

const submitting = ref(false);
const errors = ref([]);

// Watch for department changes (edit mode)
watch(() => props.department, (dept) => {
    if (dept && props.mode === 'edit') {
        form.value = {
            name: dept.name || '',
            code: dept.code || '',
            faculty_id: dept.faculty_id || '',
            head_name: dept.head_name || '',
            phone: dept.phone || '',
            email: dept.email || '',
            room_number: dept.room_number || '',
            is_active: dept.is_active ?? true
        };
    }
}, { immediate: true });

const handleSubmit = async () => {
    errors.value = [];
    submitting.value = true;

    try {
        // Convert code to uppercase
        form.value.code = form.value.code.toUpperCase();

        if (props.mode === 'create') {
            await axios.post('/api/admin/departments', form.value);
        } else {
            await axios.put(`/api/admin/departments/${props.department.id}`, form.value);
        }

        emit('success');
        alert(props.mode === 'create' ? 'Kafedra yaratildi' : 'Kafedra yangilandi');
    } catch (error) {
        console.error('Submit error:', error);

        if (error.response?.data?.errors) {
            // Laravel validation errors
            errors.value = Object.values(error.response.data.errors).flat();
        } else {
            errors.value = [error.response?.data?.message || 'Xatolik yuz berdi'];
        }
    } finally {
        submitting.value = false;
    }
};
</script>
