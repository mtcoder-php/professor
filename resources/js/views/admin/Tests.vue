<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">Testlar</h1>
                <p class="text-gray-600 mt-1">Testlarni boshqarish va monitoring</p>
            </div>
            <button @click="openCreateModal" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Yangi test
            </button>
        </div>

        <!-- Filters & Search -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            @input="searchTests"
                            type="text"
                            placeholder="Qidirish..."
                            class="form-input pl-10"
                        />
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Type Filter -->
                <select v-model="filters.type" @change="loadTests" class="form-input">
                    <option value="">Barcha turlar</option>
                    <option value="entry">Kiruvchi test</option>
                    <option value="exit">Chiquvchi test</option>
                </select>

                <!-- Status Filter -->
                <select v-model="filters.is_active" @change="loadTests" class="form-input">
                    <option value="">Barcha holatlar</option>
                    <option value="1">Faol</option>
                    <option value="0">Nofaol</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <!-- Loading -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
                <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="tests.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-600 mt-4 text-lg font-semibold">Testlar topilmadi</p>
                <p class="text-gray-500 mt-2">Yangi test yarating</p>
            </div>

            <!-- Table Content -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Test nomi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Turi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Savollar</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Vaqt</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Ball</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amallar</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="test in tests" :key="test.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ test.id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ test.title }}</div>
                            <div class="text-xs text-gray-500">
                                {{ test.creator?.full_name || 'Admin' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                <span :class="test.type === 'entry' ? 'badge-blue' : 'badge-purple'" class="badge">
                  {{ test.type === 'entry' ? 'Kiruvchi' : 'Chiquvchi' }}
                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-lg font-bold text-green-600">{{ test.questions_count || 0 }}</span>
                            <div class="text-xs text-gray-500">{{ test.total_points || 0 }} ball</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ test.duration_minutes }} daqiqa</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">O'tish: {{ test.pass_score }}%</div>
                            <div class="text-xs text-gray-500">{{ test.points_per_question }} ball/savol</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button
                                @click="toggleStatus(test)"
                                :class="test.is_active ? 'badge-success' : 'badge-warning'"
                                class="badge cursor-pointer hover:opacity-80"
                            >
                                {{ test.is_active ? 'Faol' : 'Nofaol' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button
                                @click="viewTest(test)"
                                class="text-blue-600 hover:text-blue-900"
                                title="Ko'rish"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button
                                @click="manageQuestions(test)"
                                class="text-purple-600 hover:text-purple-900"
                                title="Savollar"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </button>
                            <button
                                @click="editTest(test)"
                                class="text-green-600 hover:text-green-900"
                                title="Tahrirlash"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="confirmDelete(test)"
                                class="text-red-600 hover:text-red-900"
                                title="O'chirish"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Jami: <span class="font-semibold">{{ pagination.total }}</span> ta test
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            Oldingi
                        </button>
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            Keyingi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <TestModal
            v-if="showModal"
            :test="selectedTest"
            :mode="modalMode"
            @close="closeModal"
            @success="handleSuccess"
        />

        <TestViewModal
            v-if="showViewModal"
            :test="selectedTest"
            @close="showViewModal = false"
            @edit="editFromView"
            @manage-questions="manageQuestionsFromView"
        />

        <TestQuestionsModal
            v-if="showQuestionsModal"
            :test="selectedTest"
            @close="showQuestionsModal = false"
            @success="handleQuestionsSuccess"
        />

        <ConfirmDialog
            v-if="showDeleteDialog"
            title="Testni o'chirish"
            :message="`${deleteTarget?.title} testini o'chirmoqchimisiz?`"
            confirm-text="O'chirish"
            confirm-class="bg-red-600 hover:bg-red-700 text-white"
            @confirm="deleteTest"
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TestModal from '@/components/admin/TestModal.vue';
import TestViewModal from '@/components/admin/TestViewModal.vue';
import TestQuestionsModal from '@/components/admin/TestQuestionsModal.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

const loading = ref(false);
const tests = ref([]);
const selectedTest = ref(null);
const showModal = ref(false);
const showViewModal = ref(false);
const showQuestionsModal = ref(false);
const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const modalMode = ref('create');

const filters = ref({
    search: '',
    type: '',
    is_active: '',
    page: 1,
    per_page: 10
});

const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
});

const loadTests = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            per_page: filters.value.per_page
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.type) params.type = filters.value.type;
        if (filters.value.is_active !== '') params.is_active = filters.value.is_active;

        const response = await axios.get('/api/admin/tests', { params });

        if (response.data.success) {
            tests.value = response.data.data.data || [];
            pagination.value = {
                total: response.data.data.total || 0,
                per_page: response.data.data.per_page || 10,
                current_page: response.data.data.current_page || 1,
                last_page: response.data.data.last_page || 1
            };
        }
    } catch (error) {
        console.error('Testlarni yuklashda xatolik:', error);
        alert('Testlarni yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

let searchTimeout = null;
const searchTests = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadTests();
    }, 500);
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadTests();
};

const openCreateModal = () => {
    selectedTest.value = null;
    modalMode.value = 'create';
    showModal.value = true;
};

const editTest = (test) => {
    selectedTest.value = { ...test };
    modalMode.value = 'edit';
    showModal.value = true;
};

const viewTest = async (test) => {
    try {
        const response = await axios.get(`/api/admin/tests/${test.id}`);
        if (response.data.success) {
            selectedTest.value = response.data.data;
            showViewModal.value = true;
        }
    } catch (error) {
        console.error('Testni yuklashda xatolik:', error);
        alert('Testni yuklashda xatolik yuz berdi');
    }
};

const manageQuestions = (test) => {
    selectedTest.value = { ...test };
    showQuestionsModal.value = true;
};

const editFromView = () => {
    showViewModal.value = false;
    editTest(selectedTest.value);
};

const manageQuestionsFromView = () => {
    showViewModal.value = false;
    manageQuestions(selectedTest.value);
};

const confirmDelete = (test) => {
    deleteTarget.value = test;
    showDeleteDialog.value = true;
};

const deleteTest = async () => {
    try {
        const response = await axios.delete(`/api/admin/tests/${deleteTarget.value.id}`);
        if (response.data.success) {
            showDeleteDialog.value = false;
            deleteTarget.value = null;
            loadTests();
            alert('Test muvaffaqiyatli o\'chirildi');
        }
    } catch (error) {
        console.error('Testni o\'chirishda xatolik:', error);
        const message = error.response?.data?.message || 'Testni o\'chirishda xatolik yuz berdi';
        alert(message);
    }
};

const toggleStatus = async (test) => {
    try {
        const response = await axios.patch(`/api/admin/tests/${test.id}/toggle-status`);
        if (response.data.success) {
            test.is_active = !test.is_active;
        }
    } catch (error) {
        console.error('Holat o\'zgartirishda xatolik:', error);
        alert('Holat o\'zgartirishda xatolik yuz berdi');
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedTest.value = null;
};

const handleSuccess = () => {
    closeModal();
    loadTests();
};

const handleQuestionsSuccess = () => {
    showQuestionsModal.value = false;
    loadTests();
};

onMounted(() => {
    loadTests();
});
</script>
