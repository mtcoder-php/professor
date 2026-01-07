<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">Fakultetlar</h1>
                <p class="text-gray-600 mt-1">Fakultetlarni boshqarish va monitoring</p>
            </div>
            <button @click="openCreateModal" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Yangi fakultet
            </button>
        </div>

        <!-- Filters & Search -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            @input="searchFaculties"
                            type="text"
                            placeholder="Fakultet nomi, kod yoki dekan bo'yicha qidirish..."
                            class="form-input pl-10"
                        />
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <select v-model="filters.is_active" @change="loadFaculties" class="form-input">
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
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="faculties.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-gray-600 mt-4 text-lg font-semibold">Fakultetlar topilmadi</p>
                <p class="text-gray-500 mt-2">Yangi fakultet qo'shing</p>
            </div>

            <!-- Table Content -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fakultet nomi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kod</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dekan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kafedralar</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Holat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amallar</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="faculty in faculties" :key="faculty.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ faculty.id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ faculty.name }}</div>
                            <div v-if="faculty.email" class="text-xs text-gray-500">{{ faculty.email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-info">{{ faculty.code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ faculty.dean_name || '—' }}</div>
                            <div v-if="faculty.phone" class="text-xs text-gray-500">{{ faculty.phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ faculty.departments_count || 0 }} ta
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button
                                @click="toggleStatus(faculty)"
                                :class="faculty.is_active ? 'badge-success' : 'badge-warning'"
                                class="badge cursor-pointer hover:opacity-80"
                            >
                                {{ faculty.is_active ? 'Faol' : 'Nofaol' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button
                                @click="viewFaculty(faculty)"
                                class="text-blue-600 hover:text-blue-900"
                                title="Ko'rish"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button
                                @click="editFaculty(faculty)"
                                class="text-green-600 hover:text-green-900"
                                title="Tahrirlash"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="confirmDelete(faculty)"
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
                        Jami: <span class="font-semibold">{{ pagination.total }}</span> ta fakultet
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Oldingi
                        </button>
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="goToPage(page)"
                            :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-4 py-2 border border-gray-300 rounded-lg"
                        >
                            {{ page }}
                        </button>
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Keyingi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FacultyModal
            v-if="showModal"
            :faculty="selectedFaculty"
            :mode="modalMode"
            @close="closeModal"
            @success="handleSuccess"
        />

        <!-- View Modal -->
        <FacultyViewModal
            v-if="showViewModal"
            :faculty="selectedFaculty"
            @close="showViewModal = false"
            @edit="editFromView"
        />

        <!-- Delete Confirmation -->
        <ConfirmDialog
            v-if="showDeleteDialog"
            title="Fakultetni o'chirish"
            :message="`${deleteTarget?.name} fakultetini o'chirmoqchimisiz?`"
            confirm-text="O'chirish"
            confirm-class="btn-danger"
            @confirm="deleteFaculty"
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import FacultyModal from '@/components/admin/FacultyModal.vue';
import FacultyViewModal from '@/components/admin/FacultyViewModal.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

const loading = ref(false);
const faculties = ref([]);
const selectedFaculty = ref(null);
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const modalMode = ref('create'); // 'create' or 'edit'

const filters = ref({
    search: '',
    is_active: '',
    page: 1
});

const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
});

// Computed
const visiblePages = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push('...', last);
    } else if (last > 1) {
        rangeWithDots.push(last);
    }

    return rangeWithDots;
});

// Methods
const loadFaculties = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            search: filters.value.search,
            is_active: filters.value.is_active,
            per_page: 10
        };

        const response = await axios.get('/api/admin/faculties', { params });

        faculties.value = response.data.data.data;
        pagination.value = {
            total: response.data.data.total,
            per_page: response.data.data.per_page,
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page
        };
    } catch (error) {
        console.error('Fakultetlarni yuklashda xatolik:', error);
        alert('Fakultetlarni yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

let searchTimeout = null;
const searchFaculties = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadFaculties();
    }, 500);
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadFaculties();
};

const openCreateModal = () => {
    selectedFaculty.value = null;
    modalMode.value = 'create';
    showModal.value = true;
};

const editFaculty = (faculty) => {
    selectedFaculty.value = { ...faculty };
    modalMode.value = 'edit';
    showModal.value = true;
};

const viewFaculty = async (faculty) => {
    try {
        const response = await axios.get(`/api/admin/faculties/${faculty.id}`);
        selectedFaculty.value = response.data.data;
        showViewModal.value = true;
    } catch (error) {
        console.error('Fakultetni yuklashda xatolik:', error);
        alert('Fakultetni yuklashda xatolik yuz berdi');
    }
};

const editFromView = () => {
    showViewModal.value = false;
    editFaculty(selectedFaculty.value);
};

const confirmDelete = (faculty) => {
    deleteTarget.value = faculty;
    showDeleteDialog.value = true;
};

const deleteFaculty = async () => {
    try {
        await axios.delete(`/api/admin/faculties/${deleteTarget.value.id}`);
        showDeleteDialog.value = false;
        deleteTarget.value = null;
        loadFaculties();
        alert('Fakultet muvaffaqiyatli o\'chirildi');
    } catch (error) {
        console.error('Fakultetni o\'chirishda xatolik:', error);
        const message = error.response?.data?.message || 'Fakultetni o\'chirishda xatolik yuz berdi';
        alert(message);
    }
};

const toggleStatus = async (faculty) => {
    try {
        await axios.patch(`/api/admin/faculties/${faculty.id}/toggle-status`);
        faculty.is_active = !faculty.is_active;
    } catch (error) {
        console.error('Holat o\'zgartirishda xatolik:', error);
        alert('Holat o\'zgartirishda xatolik yuz berdi');
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedFaculty.value = null;
};

const handleSuccess = () => {
    closeModal();
    loadFaculties();
};

onMounted(() => {
    loadFaculties();
});
</script>
