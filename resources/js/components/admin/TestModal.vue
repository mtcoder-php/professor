<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">
                {{ mode === 'create' ? 'Yangi test' : 'Testni tahrirlash' }}
            </h2>

            <!-- Error Display -->
            <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Test Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Test nomi <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="form-input w-full"
                        placeholder="Kiruvchi test - Tarix"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Test Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Test turi <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.type" class="form-input w-full" required>
                            <option value="">Tanlang</option>
                            <option value="entry">Kiruvchi test</option>
                            <option value="exit">Chiquvchi test</option>
                        </select>
                    </div>

                    <!-- Points per Question -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Har bir savol uchun ball <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.points_per_question"
                            type="number"
                            step="0.5"
                            min="0.5"
                            class="form-input w-full"
                            placeholder="2"
                            required
                        />
                    </div>

                    <!-- Questions Per Attempt -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Har safar ko'rsatiladigan savollar
                        </label>
                        <input
                            v-model.number="form.questions_per_attempt"
                            type="number"
                            min="1"
                            class="form-input w-full"
                            placeholder="Bo'sh = barchasi"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            100 ta savol bo'lsa, 50 yozing - har safar random 50 ta tushadi
                        </p>
                    </div>

                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Test davomiyligi (daqiqa) <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.duration_minutes"
                            type="number"
                            min="1"
                            class="form-input w-full"
                            placeholder="60"
                            required
                        />
                    </div>

                    <!-- Pass Score -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            O'tish bali (%) <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.pass_score"
                            type="number"
                            min="0"
                            max="100"
                            class="form-input w-full"
                            placeholder="70"
                            required
                        />
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Boshlanish sanasi
                        </label>
                        <input
                            v-model="form.start_date"
                            type="datetime-local"
                            class="form-input w-full"
                        />
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tugash sanasi
                        </label>
                        <input
                            v-model="form.end_date"
                            type="datetime-local"
                            class="form-input w-full"
                        />
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        />
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                            Faol test
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.allow_retake"
                            type="checkbox"
                            id="allow_retake"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        />
                        <label for="allow_retake" class="ml-2 text-sm font-medium text-gray-700">
                            Qayta topshirish imkoniyati
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.show_results"
                            type="checkbox"
                            id="show_results"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        />
                        <label for="show_results" class="ml-2 text-sm font-medium text-gray-700">
                            Natijalarni ko'rsatish
                        </label>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Eslatma:</strong> Testni yaratgandan keyin savollarni qo'shishingiz kerak bo'ladi.
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
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    test: Object,
    mode: String
});

const emit = defineEmits(['close', 'success']);

const form = ref({
    title: '',
    type: '',
    points_per_question: 2,
    questions_per_attempt: null,
    duration_minutes: 60,
    pass_score: 70,
    start_date: '',
    end_date: '',
    is_active: true,
    allow_retake: false,
    show_results: true
});

const submitting = ref(false);
const errors = ref([]);

const formatDateForInput = (dateString) => {
    if (!dateString) return '';

    try {
        // If already in YYYY-MM-DDTHH:mm format, use it
        if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/)) {
            return dateString;
        }

        // Parse and format
        const cleanDate = dateString.replace(/\.\d+Z?$/, '').replace(' ', 'T');
        const date = new Date(cleanDate);

        if (isNaN(date.getTime())) return '';

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch (e) {
        console.error('Date format error:', e);
        return '';
    }
};

watch(() => props.test, (test) => {
    if (test && props.mode === 'edit') {
        form.value = {
            title: test.title || '',
            type: test.type || '',
            points_per_question: test.points_per_question || 2,
            questions_per_attempt: test.questions_per_attempt || null,
            duration_minutes: test.duration_minutes || 60,
            pass_score: test.pass_score || 70,
            start_date: formatDateForInput(test.start_date),
            end_date: formatDateForInput(test.end_date),
            is_active: test.is_active ?? true,
            allow_retake: test.allow_retake ?? false,
            show_results: test.show_results ?? true
        };
    } else {
        // Reset for create
        form.value = {
            title: '',
            type: '',
            points_per_question: 2,
            questions_per_attempt: null,
            duration_minutes: 60,
            pass_score: 70,
            start_date: '',
            end_date: '',
            is_active: true,
            allow_retake: false,
            show_results: true
        };
    }
}, { immediate: true });

const handleSubmit = async () => {
    errors.value = [];
    submitting.value = true;

    try {
        let response;

        if (props.mode === 'create') {
            response = await axios.post('/api/admin/tests', form.value);
        } else {
            response = await axios.put(`/api/admin/tests/${props.test.id}`, form.value);
        }

        emit('success');
        alert(props.mode === 'create' ? 'Test yaratildi' : 'Test yangilandi');
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
