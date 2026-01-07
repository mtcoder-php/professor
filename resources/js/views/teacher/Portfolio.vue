<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Portfolio</h1>
            <p class="text-gray-600 mt-1">Shaxsiy portfolio boshqaruvi</p>
        </div>

        <!-- Statistics - TUZATILDI ← -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="card bg-gradient-to-br from-blue-50 to-blue-100">
                <p class="text-sm text-blue-800 mb-1">Jami kategoriyalar</p>
                <p class="text-3xl font-bold text-blue-900">{{ categories?.length || 0 }}</p>
            </div>
            <div class="card bg-gradient-to-br from-green-50 to-green-100">
                <p class="text-sm text-green-800 mb-1">Yuklangan</p>
                <p class="text-3xl font-bold text-green-900">{{ uploadedCount }}</p>
            </div>
            <div class="card bg-gradient-to-br from-yellow-50 to-yellow-100">
                <p class="text-sm text-yellow-800 mb-1">Kutilmoqda</p>
                <p class="text-3xl font-bold text-yellow-900">{{ pendingCount }}</p>
            </div>
            <div class="card bg-gradient-to-br from-purple-50 to-purple-100">
                <p class="text-sm text-purple-800 mb-1">Baholangan</p>
                <p class="text-3xl font-bold text-purple-900">{{ evaluatedCount }}</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Categories -->
        <div v-else class="space-y-4">
            <div v-for="category in categories" :key="category.id" class="card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ category.name }}</h3>
                        <p class="text-sm text-gray-600">{{ category.description }}</p>
                    </div>
                    <button @click="openUploadModal(category)" class="btn-primary">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Yuklash
                    </button>
                </div>

                <!-- Files List -->
                <div v-if="category.files && category.files.length > 0" class="space-y-2">
                    <div
                        v-for="file in category.files"
                        :key="file.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">{{ file.file_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ formatFileSize(file.file_size) }} • {{ formatDate(file.uploaded_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Status Badge -->
                            <span
                                :class="{
                  'badge-warning': file.status === 'pending',
                  'badge-success': file.status === 'evaluated',
                  'badge-danger': file.status === 'rejected'
                }"
                                class="badge text-xs"
                            >
                {{ getStatusText(file.status) }}
              </span>

                            <!-- Score (if evaluated) -->
                            <span v-if="file.evaluation" class="text-sm font-bold text-green-600">
                {{ file.evaluation.score }} ball
              </span>

                            <!-- Actions -->
                            <button @click="downloadFile(file)" class="text-blue-600 hover:text-blue-800" title="Yuklab olish">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>

                            <button @click="deleteFile(file)" class="text-red-600 hover:text-red-800" title="O'chirish">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    Hali fayl yuklanmagan
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showUploadModal = false">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <h2 class="text-2xl font-bold mb-4">Fayl yuklash</h2>
                <p class="text-gray-600 mb-4">{{ selectedCategory?.name }}</p>

                <form @submit.prevent="handleUpload">
                    <div class="space-y-4">
                        <!-- File Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fayl</label>
                            <input
                                type="file"
                                @change="handleFileSelect"
                                accept=".pdf,.doc,.docx,.xls,.xlsx"
                                class="form-input w-full"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, XLS, XLSX (max 2MB)</p>
                        </div>

                        <!-- Year -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Yil</label>
                            <input
                                v-model="uploadForm.year"
                                type="number"
                                min="2000"
                                :max="new Date().getFullYear() + 1"
                                class="form-input w-full"
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                            <textarea
                                v-model="uploadForm.description"
                                rows="3"
                                maxlength="500"
                                class="form-input w-full"
                            ></textarea>
                        </div>

                        <!-- Error -->
                        <div v-if="uploadError" class="text-red-600 text-sm">
                            {{ uploadError }}
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button type="button" @click="showUploadModal = false" class="flex-1 btn-secondary">
                                Bekor qilish
                            </button>
                            <button type="submit" :disabled="uploading" class="flex-1 btn-primary">
                                {{ uploading ? 'Yuklanmoqda...' : 'Yuklash' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const categories = ref([]);
const showUploadModal = ref(false);
const selectedCategory = ref(null);
const uploading = ref(false);
const uploadError = ref('');

const uploadForm = ref({
    file: null,
    year: new Date().getFullYear(),
    description: ''
});

// Computed Statistics - YANGI ←
const uploadedCount = computed(() => {
    if (!categories.value || categories.value.length === 0) return 0;

    let count = 0;
    categories.value.forEach(category => {
        if (category.files && category.files.length > 0) {
            count += category.files.length;
        }
    });
    return count;
});

const pendingCount = computed(() => {
    if (!categories.value || categories.value.length === 0) return 0;

    let count = 0;
    categories.value.forEach(category => {
        if (category.files && category.files.length > 0) {
            category.files.forEach(file => {
                if (file.status === 'pending') count++;
            });
        }
    });
    return count;
});

const evaluatedCount = computed(() => {
    if (!categories.value || categories.value.length === 0) return 0;

    let count = 0;
    categories.value.forEach(category => {
        if (category.files && category.files.length > 0) {
            category.files.forEach(file => {
                if (file.status === 'evaluated') count++;
            });
        }
    });
    return count;
});

const loadCategories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/portfolio');

        if (response.data.success) {
            categories.value = response.data.data || [];
            console.log('📁 Categories loaded:', categories.value);
        }
    } catch (error) {
        console.error('Categories load error:', error);
    } finally {
        loading.value = false;
    }
};

const openUploadModal = (category) => {
    selectedCategory.value = category;
    uploadForm.value = {
        file: null,
        year: new Date().getFullYear(),
        description: ''
    };
    uploadError.value = '';
    showUploadModal.value = true;
};

const handleFileSelect = (event) => {
    uploadForm.value.file = event.target.files[0];
};

const handleUpload = async () => {
    if (!uploadForm.value.file) {
        uploadError.value = 'Fayl tanlang';
        return;
    }

    uploading.value = true;
    uploadError.value = '';

    try {
        const formData = new FormData();
        formData.append('category_id', selectedCategory.value.id);
        formData.append('file', uploadForm.value.file);
        formData.append('year', uploadForm.value.year);
        formData.append('description', uploadForm.value.description);

        const response = await axios.post('/api/teacher/portfolio/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.success) {
            showUploadModal.value = false;
            await loadCategories();
            alert('Fayl muvaffaqiyatli yuklandi');
        }
    } catch (error) {
        console.error('Upload error:', error);
        uploadError.value = error.response?.data?.message || 'Yuklashda xatolik';
    } finally {
        uploading.value = false;
    }
};

const downloadFile = async (file) => {
    try {
        const response = await axios.get(`/api/teacher/portfolio/${file.id}/download-url`);

        if (response.data.success) {
            window.open(response.data.data.url, '_blank');
        }
    } catch (error) {
        console.error('Download error:', error);
        alert('Faylni yuklashda xatolik');
    }
};

const deleteFile = async (file) => {
    if (!confirm(`${file.file_name} faylini o'chirmoqchimisiz?`)) return;

    try {
        await axios.delete(`/api/teacher/portfolio/${file.id}`);
        await loadCategories();
        alert('Fayl o\'chirildi');
    } catch (error) {
        console.error('Delete error:', error);
        alert('O\'chirishda xatolik');
    }
};

const getStatusText = (status) => {
    const statuses = {
        'pending': 'Kutilmoqda',
        'evaluated': 'Baholangan',
        'rejected': 'Rad etilgan'
    };
    return statuses[status] || status;
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const formatDate = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

onMounted(() => {
    loadCategories();
});
</script>
