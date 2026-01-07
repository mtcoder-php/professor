<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <router-link to="/prorektor/portfolio-evaluations" class="text-green-600 hover:text-green-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </router-link>
            <div>
                <h1 class="text-3xl font-bold text-gradient">Portfolio Baholash</h1>
                <p class="text-gray-600 mt-1">{{ teacher?.full_name }}</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Content -->
        <div v-else>
            <!-- Teacher Info -->
            <div class="card mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">F.I.O</p>
                        <p class="font-semibold text-gray-900">{{ teacher?.full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Fakultet</p>
                        <p class="font-semibold text-gray-900">{{ teacher?.faculty?.name || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kafedra</p>
                        <p class="font-semibold text-gray-900">{{ teacher?.department?.name || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Ilmiy daraja</p>
                        <p class="font-semibold text-gray-900">{{ teacher?.scientific_degree || '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="space-y-4">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="card"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ category.order }}. {{ category.name }}</h3>
                                <span v-if="category.requires_period" class="badge badge-info text-xs">
                  Oxirgi 3 yil
                </span>
                            </div>
                            <p class="text-sm text-gray-600">{{ category.description }}</p>
                        </div>
                    </div>

                    <!-- Files List -->
                    <div v-if="category.files && category.files.length > 0" class="space-y-3">
                        <div
                            v-for="file in category.files"
                            :key="file.id"
                            class="bg-gray-50 rounded-lg p-4"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3 flex-1">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ file.file_name }}</p>
                                        <div class="flex gap-3 text-xs text-gray-600 mt-1">
                                            <span>{{ formatFileSize(file.file_size) }}</span>
                                            <span v-if="file.year">{{ file.year }}-yil</span>
                                            <span>{{ formatDate(file.uploaded_at) }}</span>
                                        </div>
                                        <p v-if="file.description" class="text-sm text-gray-600 mt-1">{{ file.description }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <!-- Status Badge -->
                                    <span
                                        :class="{
                      'badge-warning': file.status === 'pending',
                      'badge-success': file.status === 'evaluated',
                      'badge-danger': file.status === 'rejected'
                    }"
                                        class="badge"
                                    >
                    {{
                                            file.status === 'pending' ? 'Kutilmoqda' :
                                                file.status === 'evaluated' ? 'Baholangan' :
                                                    'Rad etilgan'
                                        }}
                  </span>

                                    <!-- Actions -->
                                    <button
                                        @click="downloadFile(file)"
                                        class="text-blue-600 hover:text-blue-800"
                                        title="Yuklab olish"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </button>

                                    <button
                                        @click="openEvaluateModal(file)"
                                        class="btn-primary text-sm"
                                    >
                                        {{ file.status === 'evaluated' ? 'Qayta baholash' : 'Baholash' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Evaluation Info -->
                            <div v-if="file.evaluation" class="bg-white rounded-lg p-3 mt-3 border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <p class="text-2xl font-bold text-green-600">{{ file.evaluation.score }}</p>
                                            <p class="text-sm text-gray-600">ball</p>
                                        </div>
                                        <p v-if="file.evaluation.comment" class="text-sm text-gray-700">{{ file.evaluation.comment }}</p>
                                    </div>
                                    <div class="text-right text-xs text-gray-500">
                                        <p>{{ file.evaluation.evaluator?.full_name }}</p>
                                        <p>{{ formatDate(file.evaluation.evaluated_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4 text-gray-500">
                        Hali fayl yuklanmagan
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluate Modal -->
        <PortfolioEvaluateModal
            v-if="showEvaluateModal"
            :file="selectedFile"
            @close="showEvaluateModal = false"
            @success="handleEvaluateSuccess"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import PortfolioEvaluateModal from '@/components/prorektor/PortfolioEvaluateModal.vue';

const route = useRoute();
const loading = ref(false);
const teacher = ref(null);
const categories = ref([]);
const selectedFile = ref(null);
const showEvaluateModal = ref(false);

const loadTeacherPortfolio = async () => {
    loading.value = true;
    try {
        const userId = route.params.userId;
        const response = await axios.get(`/api/prorektor/portfolio-evaluations/teacher/${userId}`);

        if (response.data.success) {
            teacher.value = response.data.data.teacher;
            categories.value = response.data.data.categories;
        }
    } catch (error) {
        console.error('Portfolio yuklashda xatolik:', error);
        alert('Portfolio yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

const openEvaluateModal = (file) => {
    selectedFile.value = file;
    showEvaluateModal.value = true;
};

const handleEvaluateSuccess = () => {
    showEvaluateModal.value = false;
    selectedFile.value = null;
    loadTeacherPortfolio();
};

const downloadFile = async (file) => {
    try {
        // Step 1: Get signed download URL
        const response = await axios.get(`/api/prorektor/portfolio-evaluations/${file.id}/download-url`);

        if (response.data.success) {
            // Step 2: Open signed URL in new tab
            window.open(response.data.data.url, '_blank');
        }
    } catch (error) {
        console.error('Download error:', error);
        alert('Faylni yuklashda xatolik');
    }
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    loadTeacherPortfolio();
});
</script>
