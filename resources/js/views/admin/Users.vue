<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">O'qituvchilar</h1>
                <p class="text-gray-600 mt-1">O'qituvchilarni boshqarish va monitoring</p>
            </div>
            <div class="flex gap-3">
                <!-- Import Button -->
                <button @click="showImportModal = true" class="btn-secondary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Excel import
                </button>

                <button @click="openCreateModal" class="btn-primary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Yangi o'qituvchi
                </button>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            @input="searchUsers"
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
                <select v-model="filters.faculty_id" @change="loadUsers" class="form-input">
                    <option value="">Barcha fakultetlar</option>
                    <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                        {{ faculty.name }}
                    </option>
                </select>

                <!-- Department Filter -->
                <select v-model="filters.department_id" @change="loadUsers" class="form-input">
                    <option value="">Barcha kafedralar</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                        {{ dept.name }}
                    </option>
                </select>

                <!-- Scientific Degree Filter -->
                <select v-model="filters.scientific_degree" @change="loadUsers" class="form-input">
                    <option value="">Barcha darajalar</option>
                    <option value="Fan doktori">Fan doktori</option>
                    <option value="PhD">PhD</option>
                    <option value="DSc">DSc</option>
                    <option value="Dotsent">Dotsent</option>
                    <option value="Professor">Professor</option>
                    <option value="Katta o'qituvchi">Katta o'qituvchi</option>
                    <option value="O'qituvchi">O'qituvchi</option>
                    <option value="Assistent">Assistent</option>
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
            <div v-else-if="users.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-gray-600 mt-4 text-lg font-semibold">O'qituvchilar topilmadi</p>
                <p class="text-gray-500 mt-2">Yangi o'qituvchi qo'shing</p>
            </div>

            <!-- Table Content -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">F.I.O</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Passport</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fakultet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Ilmiy daraja</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Rol</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amallar</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ user.id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img
                                        v-if="user.avatar"
                                        :src="`/storage/${user.avatar}`"
                                        :alt="user.full_name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <div v-else class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                      <span class="text-green-600 font-semibold text-sm">
                        {{ user.full_name.charAt(0) }}
                      </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ user.full_name }}</div>
                                    <div v-if="user.email" class="text-xs text-gray-500">{{ user.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-info">{{ user.passport_series }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ user.faculty?.name || '—' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ user.department?.name || '—' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-success">{{ user.scientific_degree || '—' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                <span
                    :class="{
                    'badge-purple': user.roles?.[0]?.name === 'admin',
                    'badge-blue': user.roles?.[0]?.name === 'prorektor',
                    'badge-green': user.roles?.[0]?.name === 'teacher'
                  }"
                    class="badge"
                >
                  {{ user.roles?.[0]?.name || 'teacher' }}
                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button
                                @click="viewUser(user)"
                                class="text-blue-600 hover:text-blue-900"
                                title="Ko'rish"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button
                                @click="editUser(user)"
                                class="text-green-600 hover:text-green-900"
                                title="Tahrirlash"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="confirmDelete(user)"
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
                        <span class="font-semibold">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
                        -
                        <span class="font-semibold">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
                        dan
                        <span class="font-semibold">{{ pagination.total }}</span>
                        ta o'qituvchi
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- First Page -->
                        <button
                            @click="goToPage(1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Birinchi sahifa"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>

                        <!-- Previous -->
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Oldingi
                        </button>

                        <!-- Page Numbers -->
                        <div class="flex gap-1">
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                  'px-3 py-2 border rounded-lg',
                  page === pagination.current_page
                    ? 'bg-green-600 text-white border-green-600'
                    : 'border-gray-300 hover:bg-gray-50',
                  page === '...' ? 'cursor-default' : ''
                ]"
                                :disabled="page === '...'"
                            >
                                {{ page }}
                            </button>
                        </div>

                        <!-- Next -->
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Keyingi
                        </button>

                        <!-- Last Page -->
                        <button
                            @click="goToPage(pagination.last_page)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Oxirgi sahifa"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Per Page Selector -->
                <div class="flex items-center justify-center gap-2 mt-4 pt-4 border-t border-gray-200">
                    <span class="text-sm text-gray-600">Sahifada:</span>
                    <select v-model="pagination.per_page" @change="changePerPage" class="form-input py-1 px-2 text-sm">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-600">ta</span>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <UserModal
            v-if="showModal"
            :user="selectedUser"
            :faculties="faculties"
            :departments="allDepartments"
            :mode="modalMode"
            @close="closeModal"
            @success="handleSuccess"
        />

        <UserImportModal
            v-if="showImportModal"
            @close="showImportModal = false"
            @success="handleImportSuccess"
        />

        <UserViewModal
            v-if="showViewModal"
            :user="selectedUser"
            @close="showViewModal = false"
            @edit="editFromView"
        />

        <ConfirmDialog
            v-if="showDeleteDialog"
            title="O'qituvchini o'chirish"
            :message="`${deleteTarget?.full_name} o'qituvchisini o'chirmoqchimisiz?`"
            confirm-text="O'chirish"
            confirm-class="bg-red-600 hover:bg-red-700 text-white"
            @confirm="deleteUser"
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import UserModal from '@/components/admin/UserModal.vue';
import UserImportModal from '@/components/admin/UserImportModal.vue';
import UserViewModal from '@/components/admin/UserViewModal.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';

const loading = ref(false);
const users = ref([]);
const faculties = ref([]);
const departments = ref([]);
const allDepartments = ref([]);
const selectedUser = ref(null);
const showModal = ref(false);
const showImportModal = ref(false);
const showViewModal = ref(false);
const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const modalMode = ref('create');

const filters = ref({
    search: '',
    faculty_id: '',
    department_id: '',
    scientific_degree: '',
    page: 1,
    per_page: 10  // ← QOSHISH
});

const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
});

// Computed - Visible pages for pagination
const visiblePages = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const delta = 2;
    const pages = [];

    pages.push(1);

    const rangeStart = Math.max(2, current - delta);
    const rangeEnd = Math.min(last - 1, current + delta);

    if (rangeStart > 2) {
        pages.push('...');
    }

    for (let i = rangeStart; i <= rangeEnd; i++) {
        pages.push(i);
    }

    if (rangeEnd < last - 1) {
        pages.push('...');
    }

    if (last > 1) {
        pages.push(last);
    }

    return pages;
});

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties');
        if (response.data.success) {
            faculties.value = response.data.data.data || response.data.data || [];
        }
    } catch (error) {
        console.error('Fakultetlarni yuklashda xatolik:', error);
    }
};

const loadDepartments = async () => {
    try {
        const response = await axios.get('/api/departments');
        if (response.data.success) {
            allDepartments.value = response.data.data.data || response.data.data || [];
            departments.value = allDepartments.value;
        }
    } catch (error) {
        console.error('Kafedralarni yuklashda xatolik:', error);
    }
};

const loadUsers = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            per_page: filters.value.per_page  // ← O'ZGARTIRISH
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;
        if (filters.value.scientific_degree) params.scientific_degree = filters.value.scientific_degree;

        console.log('Loading users with params:', params); // DEBUG

        const response = await axios.get('/api/admin/users', { params });

        console.log('Response:', response.data); // DEBUG

        if (response.data.success) {
            users.value = response.data.data.data || [];
            pagination.value = {
                total: response.data.data.total || 0,
                per_page: response.data.data.per_page || 10,
                current_page: response.data.data.current_page || 1,
                last_page: response.data.data.last_page || 1
            };

            console.log('Pagination:', pagination.value); // DEBUG
        }
    } catch (error) {
        console.error('O\'qituvchilarni yuklashda xatolik:', error);
        alert('O\'qituvchilarni yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

let searchTimeout = null;
const searchUsers = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadUsers();
    }, 500);
};

const goToPage = (page) => {
    if (page === '...' || page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadUsers();
};

const changePerPage = () => {
    filters.value.page = 1;
    loadUsers();
};

const openCreateModal = () => {
    selectedUser.value = null;
    modalMode.value = 'create';
    showModal.value = true;
};

const editUser = (user) => {
    selectedUser.value = { ...user };
    modalMode.value = 'edit';
    showModal.value = true;
};

const viewUser = async (user) => {
    try {
        const response = await axios.get(`/api/admin/users/${user.id}`);
        if (response.data.success) {
            selectedUser.value = response.data.data;
            showViewModal.value = true;
        }
    } catch (error) {
        console.error('O\'qituvchini yuklashda xatolik:', error);
        alert('O\'qituvchini yuklashda xatolik yuz berdi');
    }
};

const editFromView = () => {
    showViewModal.value = false;
    editUser(selectedUser.value);
};

const confirmDelete = (user) => {
    deleteTarget.value = user;
    showDeleteDialog.value = true;
};

const deleteUser = async () => {
    try {
        const response = await axios.delete(`/api/admin/users/${deleteTarget.value.id}`);
        if (response.data.success) {
            showDeleteDialog.value = false;
            deleteTarget.value = null;
            loadUsers();
            alert('O\'qituvchi muvaffaqiyatli o\'chirildi');
        }
    } catch (error) {
        console.error('O\'qituvchini o\'chirishda xatolik:', error);
        const message = error.response?.data?.message || 'O\'qituvchini o\'chirishda xatolik yuz berdi';
        alert(message);
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedUser.value = null;
};

const handleSuccess = () => {
    closeModal();
    loadUsers();
};

const handleImportSuccess = () => {
    showImportModal.value = false;
    loadUsers();
};

onMounted(() => {
    loadFaculties();
    loadDepartments();
    loadUsers();
});
</script>
