<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">Kafedralar</h1>
                <p class="text-gray-600 mt-1">Kafedralarni boshqarish va monitoring</p>
            </div>
            <button @click="openCreateModal" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Yangi kafedra
            </button>
        </div>

        <!-- Filters & Search -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-1">
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            @input="searchDepartments"
                            type="text"
                            placeholder="Qidirish..."
                            class="form-input pl-10"
                        />
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Faculty Filter -->
                <select v-model="filters.faculty_id" @change="loadDepartments" class="form-input">
                    <option value="">Barcha fakultetlar</option>
                    <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                        {{ faculty.name }}
                    </option>
                </select>

                <!-- Status Filter -->
                <select v-model="filters.is_active" @change="loadDepartments" class="form-input">
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
            <div v-else-if="departments.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                <p class="text-gray-600 mt-4 text-lg font-semibold">Kafedralar topilmadi</p>
                <p class="text-gray-500 mt-2">Yangi kafedra qo'shing</p>
            </div>

            <!-- Table Content -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kod</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fakultet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Mudiri</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">O'qituvchilar</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amallar</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ dept.id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ dept.name }}</div>
                            <div v-if="dept.email" class="text-xs text-gray-500">{{ dept.email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-success">{{ dept.code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-info">{{ dept.faculty?.name || '—' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ dept.head_name || '—' }}</div>
                            <div v-if="dept.phone" class="text-xs text-gray-500">{{ dept.phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ dept.users_count || 0 }} ta
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button
                                @click="toggleStatus(dept)"
                                :class="dept.is_active ? 'badge-success' : 'badge-warning'"
                                class="badge cursor-pointer hover:opacity-80"
                            >
                                {{ dept.is_active ? 'Faol' : 'Nofaol' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button
                                @click="viewDepartment(dept)"
                                class="text-blue-600 hover:text-blue-900"
                                title="Ko'rish"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button
                                @click="editDepartment(dept)"
                                class="text-green-600 hover:text-green-900"
                                title="Tahrirlash"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="confirmDelete(dept)"
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
                        Jami: <span class="font-semibold">{{ pagination.total }}</span> ta kafedra
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
        <DepartmentModal
            v-if="showModal"
            :department="selectedDepartment"
            :faculties="faculties"
            :mode="modalMode"
            @close="closeModal"
            @success="handleSuccess"
        />

        <DepartmentViewModal
            v-if="showViewModal"
            :department="selectedDepartment"
            @close="showViewModal = false"
            @edit="editFromView"
        />

        <ConfirmDialog
            v-if="showDeleteDialog"
            title="Kafedrani o'chirish"
            :message="`${deleteTarget?.name} kafedrasini o'chirmoqchimisiz?`"
            confirm-text="O'chirish"
            confirm-class="bg-red-600 hover:bg-red-700 text-white"
            @confirm="deleteDepartment"
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import DepartmentModal from '@/components/admin/DepartmentModal.vue';
import DepartmentViewModal from '@/components/admin/DepartmentViewModal.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

const loading = ref(false);
const departments = ref([]);
const faculties = ref([]); // ← Bo'sh array
const selectedDepartment = ref(null);
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const modalMode = ref('create');

const filters = ref({
    search: '',
    faculty_id: '',
    is_active: '',
    page: 1
});

const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
});

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties');
        console.log('Faculties response:', response.data); // DEBUG

        // Turli response strukturalarini qo'llab-quvvatlash
        if (response.data.success) {
            faculties.value = response.data.data.data || response.data.data || [];
        } else if (Array.isArray(response.data.data)) {
            faculties.value = response.data.data;
        } else if (Array.isArray(response.data)) {
            faculties.value = response.data;
        } else {
            console.warn('Unexpected faculties response structure:', response.data);
            faculties.value = [];
        }

        console.log('Faculties loaded:', faculties.value.length); // DEBUG
    } catch (error) {
        console.error('Fakultetlarni yuklashda xatolik:', error);
        faculties.value = [];
    }
};

const loadDepartments = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            search: filters.value.search,
            faculty_id: filters.value.faculty_id,
            is_active: filters.value.is_active,
            per_page: 10
        };

        console.log('Loading departments with params:', params); // DEBUG

        const response = await axios.get('/api/admin/departments', { params });

        console.log('Departments response:', response.data); // DEBUG

        if (response.data.success) {
            departments.value = response.data.data.data || [];
            pagination.value = {
                total: response.data.data.total || 0,
                per_page: response.data.data.per_page || 10,
                current_page: response.data.data.current_page || 1,
                last_page: response.data.data.last_page || 1
            };
        }
    } catch (error) {
        console.error('Kafedralarni yuklashda xatolik:', error);
        alert('Kafedralarni yuklashda xatolik yuz berdi');
        departments.value = [];
    } finally {
        loading.value = false;
    }
};

let searchTimeout = null;
const searchDepartments = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadDepartments();
    }, 500);
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadDepartments();
};

const openCreateModal = () => {
    selectedDepartment.value = null;
    modalMode.value = 'create';
    showModal.value = true;
};

const editDepartment = (dept) => {
    selectedDepartment.value = { ...dept };
    modalMode.value = 'edit';
    showModal.value = true;
};

const viewDepartment = async (dept) => {
    try {
        const response = await axios.get(`/api/admin/departments/${dept.id}`);
        if (response.data.success) {
            selectedDepartment.value = response.data.data;
            showViewModal.value = true;
        }
    } catch (error) {
        console.error('Kafedrani yuklashda xatolik:', error);
        alert('Kafedrani yuklashda xatolik yuz berdi');
    }
};

const editFromView = () => {
    showViewModal.value = false;
    editDepartment(selectedDepartment.value);
};

const confirmDelete = (dept) => {
    deleteTarget.value = dept;
    showDeleteDialog.value = true;
};

const deleteDepartment = async () => {
    try {
        const response = await axios.delete(`/api/admin/departments/${deleteTarget.value.id}`);
        if (response.data.success) {
            showDeleteDialog.value = false;
            deleteTarget.value = null;
            loadDepartments();
            alert('Kafedra muvaffaqiyatli o\'chirildi');
        }
    } catch (error) {
        console.error('Kafedrani o\'chirishda xatolik:', error);
        const message = error.response?.data?.message || 'Kafedrani o\'chirishda xatolik yuz berdi';
        alert(message);
    }
};

const toggleStatus = async (dept) => {
    try {
        const response = await axios.patch(`/api/admin/departments/${dept.id}/toggle-status`);
        if (response.data.success) {
            dept.is_active = !dept.is_active;
        }
    } catch (error) {
        console.error('Holat o\'zgartirishda xatolik:', error);
        alert('Holat o\'zgartirishda xatolik yuz berdi');
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedDepartment.value = null;
};

const handleSuccess = () => {
    closeModal();
    loadDepartments();
};

onMounted(() => {
    console.log('Departments page mounted'); // DEBUG
    loadFaculties();
    loadDepartments();
});
</script>
