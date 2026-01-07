<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Portfolio Baholash</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ file.category?.name }}</p>
                </div>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- File Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-900">{{ file.file_name }}</p>
                            <div class="flex gap-3 text-xs text-blue-700 mt-1">
                                <span>{{ formatFileSize(file.file_size) }}</span>
                                <span v-if="file.year">{{ file.year }}-yil</span>
                                <span>{{ formatDate(file.uploaded_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-if="file.description" class="text-sm text-blue-800">
                        <strong>Izoh:</strong> {{ file.description }}
                    </p>
                </div>

                <!-- Previous Evaluation -->
                <div v-if="file.evaluation" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-yellow-900 mb-2">Oldingi baholash:</h3>
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-sm text-yellow-800">Ball:</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ file.evaluation.score }}</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-yellow-800">Izoh:</p>
                            <p class="text-sm text-yellow-900">{{ file.evaluation.comment || 'Izoh yo\'q' }}</p>
                        </div>
                        <div class="text-xs text-yellow-700">
                            <p>{{ file.evaluation.evaluator?.full_name }}</p>
                            <p>{{ formatDate(file.evaluation.evaluated_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Error Display -->
                <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                    </ul>
                </div>

                <!-- Evaluation Form -->
                <form @submit.prevent="handleEvaluate" class="space-y-4">
                    <!-- Score -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ball (0-100) <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.score"
                            type="number"
                            min="0"
                            max="100"
                            step="0.5"
                            class="form-input w-full text-2xl font-bold text-center"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-1">0 dan 100 gacha ball bering</p>
                    </div>

                    <!-- Comment -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Izoh
                        </label>
                        <textarea
                            v-model="form.comment"
                            class="form-input w-full"
                            rows="4"
                            placeholder="Baholash haqida izoh yozing..."
                            maxlength="1000"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">{{ form.comment?.length || 0 }} / 1000</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Holat <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.status"
                                    type="radio"
                                    value="evaluated"
                                    class="w-4 h-4"
                                    required
                                />
                                <span class="badge badge-success">Baholandi</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.status"
                                    type="radio"
                                    value="rejected"
                                    class="w-4 h-4"
                                    required
                                />
                                <span class="badge badge-danger">Rad etildi</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Progress -->
                    <div v-if="submitting" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <p class="text-blue-800 font-medium">Saqlanmoqda...</p>
                        </div>
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
                            :disabled="submitting"
                        >
                            Bekor qilish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    file: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'success']);

const submitting = ref(false);
const errors = ref([]);

const form = ref({
    score: 0,
    comment: '',
    status: 'evaluated'
});

// Initialize form with existing evaluation if available
onMounted(() => {
    if (props.file.evaluation) {
        form.value = {
            score: props.file.evaluation.score || 0,
            comment: props.file.evaluation.comment || '',
            status: props.file.status || 'evaluated'
        };
    }
});

const handleEvaluate = async () => {
    errors.value = [];
    submitting.value = true;

    // Validation
    if (form.value.score < 0 || form.value.score > 100) {
        errors.value = ['Ball 0 dan 100 gacha bo\'lishi kerak'];
        submitting.value = false;
        return;
    }

    try {
        const response = await axios.post(
            `/api/prorektor/portfolio-evaluations/${props.file.id}/evaluate`,
            form.value
        );

        if (response.data.success) {
            alert('Baholash muvaffaqiyatli saqlandi');
            emit('success');
        }
    } catch (error) {
        console.error('Baholashda xatolik:', error);

        if (error.response?.data?.errors) {
            errors.value = Object.values(error.response.data.errors).flat();
        } else {
            errors.value = [error.response?.data?.message || 'Xatolik yuz berdi'];
        }
    } finally {
        submitting.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
