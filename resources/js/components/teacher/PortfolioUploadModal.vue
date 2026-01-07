<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Fayl yuklash</h2>

            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-1">{{ category.name }}</h3>
                <p class="text-sm text-blue-800">{{ category.description }}</p>
            </div>

            <!-- Error Display -->
            <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800 font-semibold mb-2">Xatoliklar:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="handleUpload" class="space-y-4">
                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fayl <span class="text-red-500">*</span>
                    </label>
                    <input
                        @change="handleFileSelect"
                        type="file"
                        accept=".pdf,.doc,.docx"
                        ref="fileInput"
                        class="form-input w-full"
                        required
                    />
                    <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX | Max: 2MB</p>

                    <!-- Selected File Info -->
                    <div v-if="selectedFile" class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-900">{{ selectedFile.name }}</p>
                                <p class="text-xs text-green-700">{{ formatFileSize(selectedFile.size) }}</p>
                            </div>
                            <button @click="clearFile" type="button" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Year (if required) -->
                <div v-if="category.requires_period">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Yil
                    </label>
                    <input
                        v-model.number="form.year"
                        type="number"
                        :min="currentYear - 3"
                        :max="currentYear"
                        class="form-input w-full"
                        placeholder="2024"
                    />
                    <p class="text-xs text-gray-500 mt-1">Oxirgi 3 yil: {{ currentYear - 3 }} - {{ currentYear }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Izoh
                    </label>
                    <textarea
                        v-model="form.description"
                        class="form-input w-full"
                        rows="3"
                        placeholder="Qo'shimcha ma'lumot..."
                        maxlength="500"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ form.description?.length || 0 }} / 500</p>
                </div>

                <!-- Upload Progress -->
                <div v-if="uploading" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <p class="text-blue-800 font-medium">Yuklanmoqda...</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t">
                    <button
                        type="submit"
                        :disabled="!selectedFile || uploading"
                        class="btn-primary flex-1 disabled:opacity-50"
                    >
                        {{ uploading ? 'Yuklanmoqda...' : 'Yuklash' }}
                    </button>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="btn-secondary flex-1"
                        :disabled="uploading"
                    >
                        Bekor qilish
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    category: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'success']);

const selectedFile = ref(null);
const fileInput = ref(null);
const uploading = ref(false);
const errors = ref([]);

const form = ref({
    year: null,
    description: ''
});

const currentYear = computed(() => new Date().getFullYear());

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Fayl hajmi 2MB dan katta bo\'lmasligi kerak');
            event.target.value = '';
            return;
        }

        // Validate file type
        const validTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!validTypes.includes(file.type) && !file.name.match(/\.(pdf|doc|docx)$/i)) {
            alert('Faqat PDF, DOC va DOCX formatdagi fayllar qabul qilinadi');
            event.target.value = '';
            return;
        }

        selectedFile.value = file;
    }
};

const clearFile = () => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const handleUpload = async () => {
    if (!selectedFile.value) return;

    errors.value = [];
    uploading.value = true;

    try {
        const formData = new FormData();
        formData.append('category_id', props.category.id);
        formData.append('file', selectedFile.value);

        if (form.value.year) {
            formData.append('year', form.value.year);
        }

        if (form.value.description) {
            formData.append('description', form.value.description);
        }

        const response = await axios.post('/api/teacher/portfolio/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            alert('Fayl muvaffaqiyatli yuklandi');
            emit('success');
        }
    } catch (error) {
        console.error('Upload xatolik:', error);

        if (error.response?.data?.errors) {
            errors.value = Object.values(error.response.data.errors).flat();
        } else {
            errors.value = [error.response?.data?.message || 'Fayl yuklashda xatolik yuz berdi'];
        }
    } finally {
        uploading.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
