<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Portfolio Baholash</h1>
            <p class="text-gray-600 mt-1">O'qituvchilarning portfoliolarini baholash</p>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <input
                        v-model="filters.search"
                        @input="searchTeachers"
                        type="text"
                        placeholder="O'qituvchi ismi..."
                        class="form-input w-full"
                    />
                </div>

                <!-- Faculty Filter - TUZATILDI ← -->
                <div>
                    <select v-model="filters.faculty_id" @change="onFacultyChange" class="form-input w-full">
                        <option value="">Barcha fakultetlar</option>
                        <template v-if="faculties && faculties.length > 0">
                            <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                                {{ faculty.name }}
                            </option>
                        </template>
                    </select>
                </div>

                <!-- Department Filter - TUZATILDI ← -->
                <div>
                    <select v-model="filters.department_id" @change="loadTeachers" class="form-input w-full">
                        <option value="">Barcha kafedralar</option>
                        <template v-if="filteredDepartments && filteredDepartments.length > 0">
                            <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </template>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <select v-model.number="filters.per_page" @change="onPerPageChange" class="form-input w-full">
                        <option :value="10">10 ta</option>
                        <option :value="25">25 ta</option>
                        <option :value="50">50 ta</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!teachers || teachers.length === 0" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-gray-600 mt-4 text-lg font-semibold">O'qituvchilar topilmadi</p>
        </div>

        <!-- Teachers List -->
        <div v-else class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">O'qituvchi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fakultet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Portfolio</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amallar</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    <tr v-for="teacher in teachers" :key="teacher.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ teacher.full_name }}</p>
                            <p class="text-xs text-gray-500">{{ teacher.scientific_degree }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ teacher.faculty?.name || '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ teacher.department?.name || '—' }}</td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <p class="text-sm text-gray-600">
                                    Jami: <span class="font-semibold">{{ teacher.portfolio_files_count || 0 }}</span>
                                </p>
                                <p class="text-sm text-yellow-600">
                                    Kutilmoqda: <span class="font-semibold">{{ teacher.pending_files_count || 0 }}</span>
                                </p>
                                <p class="text-sm text-green-600">
                                    Baholangan: <span class="font-semibold">{{ teacher.evaluated_files_count || 0 }}</span>
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <router-link
                                :to="`/prorektor/portfolio-evaluations/teacher/${teacher.id}`"
                                class="btn-primary text-sm"
                            >
                                Baholash
                            </router-link>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination - TO'LIQ ← -->
            <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Ko'rsatilmoqda: {{ pagination.from }} - {{ pagination.to }} / {{ pagination.total }}
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="goToPage(1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            «
                        </button>
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            Oldingi
                        </button>

                        <!-- Page Numbers -->
                        <template v-for="page in getPageNumbers()" :key="page">
                            <button
                                v-if="page !== '...'"
                                @click="goToPage(page)"
                                :class="[
                  'px-4 py-2 border rounded-lg',
                  page === pagination.current_page
                    ? 'bg-green-600 text-white border-green-600'
                    : 'border-gray-300 hover:bg-gray-50'
                ]"
                            >
                                {{ page }}
                            </button>
                            <span v-else class="px-2 py-2">...</span>
                        </template>

                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            Keyingi
                        </button>
                        <button
                            @click="goToPage(pagination.last_page)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            »
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const teachers = ref([]);
const faculties = ref([]);
const departments = ref([]);

const filters = ref({
    search: '',
    faculty_id: '',
    department_id: '',
    per_page: 15,
    page: 1
});

const pagination = ref({
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0
});

const filteredDepartments = computed(() => {
    if (!departments.value || departments.value.length === 0) {
        return [];
    }

    if (!filters.value.faculty_id) {
        return departments.value;
    }

    return departments.value.filter(d => d.faculty_id == filters.value.faculty_id);
});

const onFacultyChange = () => {
    filters.value.department_id = '';
    filters.value.page = 1;
    loadTeachers();
};

const onPerPageChange = () => {
    filters.value.page = 1;
    loadTeachers();
};

const loadTeachers = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            per_page: filters.value.per_page
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;

        const response = await axios.get('/api/prorektor/portfolio-evaluations', { params });

        if (response.data.success) {
            teachers.value = response.data.data.data || [];
            pagination.value = {
                total: response.data.data.total || 0,
                per_page: response.data.data.per_page || 15,
                current_page: response.data.data.current_page || 1,
                last_page: response.data.data.last_page || 1,
                from: response.data.data.from || 0,
                to: response.data.data.to || 0
            };
        }
    } catch (error) {
        console.error('Teachers load error:', error);
        teachers.value = [];
    } finally {
        loading.value = false;
    }
};

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties');
        if (response.data.success) {
            faculties.value = response.data.data || [];
        } else if (Array.isArray(response.data)) {
            faculties.value = response.data;
        } else {
            faculties.value = [];
        }
    } catch (error) {
        console.error('Faculties load error:', error);
        faculties.value = [];
    }
};

const loadDepartments = async () => {
    try {
        const response = await axios.get('/api/departments/all');
        if (response.data.success) {
            departments.value = response.data.data || [];
        } else if (Array.isArray(response.data)) {
            departments.value = response.data;
        } else {
            departments.value = [];
        }
    } catch (error) {
        console.error('Departments load error:', error);
        departments.value = [];
    }
};

let searchTimeout = null;
const searchTeachers = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadTeachers();
    }, 500);
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadTeachers();
};

const getPageNumbers = () => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3, 4, '...', last);
        } else if (current >= last - 2) {
            pages.push(1, '...', last - 3, last - 2, last - 1, last);
        } else {
            pages.push(1, '...', current - 1, current, current + 1, '...', last);
        }
    }

    return pages;
};

onMounted(async () => {
    await loadFaculties();
    await loadDepartments();
    await loadTeachers();
});
</script>
