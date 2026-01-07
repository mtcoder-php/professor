<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="close"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all animate-fade-in">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gradient">
                        {{ mode === 'create' ? 'Yangi fakultet qo\'shish' : 'Fakultetni tahrirlash' }}
                    </h3>
                    <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="p-6 space-y-5">
                    <!-- Name & Code -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Name -->
                        <div>
                            <label class="form-label">Fakultet nomi *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Yangi Asr Fakulteti"
                                class="form-input"
                                :class="{ 'border-red-500': errors.name }"
                                required
                            />
                            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>

                        <!-- Code -->
                        <div>
                            <label class="form-label">Fakultet kodi *</label>
                            <input
                                v-model="form.code"
                                type="text"
                                placeholder="YAF"
                                maxlength="10"
                                class="form-input uppercase"
                                :class="{ 'border-red-500': errors.code }"
                                required
                            />
                            <p v-if="errors.code" class="mt-2 text-sm text-red-600">{{ errors.code[0] }}</p>
                            <p v-else class="mt-2 text-xs text-gray-500">Maksimal 10 ta belgi (harflar va tire)</p>
                        </div>
                    </div>

                    <!-- Dean Name -->
                    <div>
                        <label class="form-label">Dekan F.I.O</label>
                        <input
                            v-model="form.dean_name"
                            type="text"
                            placeholder="Mamatov Dilmurod Karimovich"
                            class="form-input"
                            :class="{ 'border-red-500': errors.dean_name }"
                        />
                        <p v-if="errors.dean_name" class="mt-2 text-sm text-red-600">{{ errors.dean_name[0] }}</p>
                    </div>

                    <!-- Phone & Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Phone -->
                        <div>
                            <label class="form-label">Telefon raqam</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="998901234567"
                                maxlength="12"
                                class="form-input"
                                :class="{ 'border-red-500': errors.phone }"
                            />
                            <p v-if="errors.phone" class="mt-2 text-sm text-red-600">{{ errors.phone[0] }}</p>
                            <p v-else class="mt-2 text-xs text-gray-500">Format: 998XXXXXXXXX</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="form-label">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="faculty@example.com"
                                class="form-input"
                                :class="{ 'border-red-500': errors.email }"
                            />
                            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email[0] }}</p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="form-label">Manzil</label>
                        <textarea
                            v-model="form.address"
                            rows="3"
                            placeholder="Fakultet manzilini kiriting..."
                            class="form-input resize-none"
                            :class="{ 'border-red-500': errors.address }"
                        ></textarea>
                        <p v-if="errors.address" class="mt-2 text-sm text-red-600">{{ errors.address[0] }}</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center space-x-3">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            Faol holat
                        </label>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            @click="close"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
                        >
                            Bekor qilish
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="btn-primary px-6 py-3 flex items-center gap-2"
                        >
                            <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ loading ? 'Saqlanmoqda...' : (mode === 'create' ? 'Qo\'shish' : 'Saqlash') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    faculty: {
        type: Object,
        default: null
    },
    mode: {
        type: String,
        default: 'create' // 'create' or 'edit'
    }
});

const emit = defineEmits(['close', 'success']);

const form = reactive({
    name: '',
    code: '',
    dean_name: '',
    phone: '',
    email: '',
    address: '',
    is_active: true
});

const errors = ref({});
const errorMessage = ref('');
const loading = ref(false);

// Load faculty data if editing
watch(() => props.faculty, (newVal) => {
    if (newVal && props.mode === 'edit') {
        form.name = newVal.name || '';
        form.code = newVal.code || '';
        form.dean_name = newVal.dean_name || '';
        form.phone = newVal.phone || '';
        form.email = newVal.email || '';
        form.address = newVal.address || '';
        form.is_active = newVal.is_active ?? true;
    }
}, { immediate: true });

const submitForm = async () => {
    errors.value = {};
    errorMessage.value = '';
    loading.value = true;

    try {
        if (props.mode === 'create') {
            await axios.post('/api/admin/faculties', form);
        } else {
            await axios.put(`/api/admin/faculties/${props.faculty.id}`, form);
        }

        emit('success');
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'Xatolik yuz berdi';
        }
    } finally {
        loading.value = false;
    }
};

const close = () => {
    emit('close');
};
</script>
