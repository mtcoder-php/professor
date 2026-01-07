<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Test Natijalari</h1>
            <p class="text-gray-600 mt-1">O'qituvchilar test natijalarini ko'rish</p>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <input
                        v-model="filters.search"
                        @input="searchResults"
                        type="text"
                        placeholder="O'qituvchi ismi..."
                        class="form-input w-full"
                    />
                </div>

                <!-- Test Filter - TUZATILDI ← -->
                <div>
                    <select v-model="filters.test_id" @change="loadResults" class="form-input w-full">
                        <option value="">Barcha testlar</option>
                        <template v-if="tests && tests.length > 0">
                            <option v-for="test in tests" :key="test.id" :value="test.id">
                                {{ test.title }}
                            </option>
                        </template>
                    </select>
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
                    <select v-model="filters.department_id" @change="loadResults" class="form-input w-full">
                        <option value="">Barcha kafedralar</option>
                        <template v-if="filteredDepartments && filteredDepartments.length > 0">
                            <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </template>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select v-model="filters.status" @change="loadResults" class="form-input w-full">
                        <option value="">Barcha holatlar</option>
                        <option value="passed">O'tdi</option>
                        <option value="failed">O'tmadi</option>
                    </select>
                </div>
            </div>

            <!-- Per Page -->
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center gap-4">
                    <label class="text-sm text-gray-600">Sahifada ko'rsatish:</label>
                    <select v-model.number="filters.per_page" @change="onPerPageChange" class="form-input w-32">
                        <option :value="10">10 ta</option>
                        <option :value="25">25 ta</option>
                        <option :value="50">50 ta</option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    Jami: <span class="font-semibold">{{ pagination.total }}</span> ta natija
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!results || results.length === 0" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 mt-4 text-lg font-semibold">Test natijalari topilmadi</p>
        </div>

        <!-- Results Table -->
        <div v-else class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">O'qituvchi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Test</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Ball</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Foiz</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Sana</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amallar</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    <tr v-for="result in results" :key="result.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ result.user?.full_name || '—' }}</p>
                            <p class="text-xs text-gray-500">{{ result.user?.department?.name || '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ result.test?.title || '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ result.score }} / {{ result.test?.total_points || 0 }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold" :class="result.passed ? 'text-green-600' : 'text-red-600'">
                                {{ result.percentage }}%
                            </p>
                        </td>
                        <td class="px-6 py-4">
                <span
                    :class="result.passed ? 'badge-success' : 'badge-danger'"
                    class="badge"
                >
                  {{ result.passed ? 'O\'tdi' : 'O\'tmadi' }}
                </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ formatDate(result.finished_at) }}
                        </td>
                        <td class="px-6 py-4">
                            <button @click="showResultDetails(result)" class="text-blue-600 hover:text-blue-800" title="Ko'rish">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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

        <!-- Result Details Modal -->
        <TestResultDetailsModal
            v-if="showDetailsModal"
            :result="selectedResult"
            @close="showDetailsModal = false"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import TestResultDetailsModal from '@/components/prorektor/TestResultDetailsModal.vue';

const loading = ref(false);
const results = ref([]);
const tests = ref([]);
const faculties = ref([]);
const departments = ref([]);
const showDetailsModal = ref(false);
const selectedResult = ref(null);

const filters = ref({
    search: '',
    test_id: '',
    faculty_id: '',
    department_id: '',
    status: '',
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
    loadResults();
};

const onPerPageChange = () => {
    filters.value.page = 1;
    loadResults();
};

const loadResults = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            per_page: filters.value.per_page
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.test_id) params.test_id = filters.value.test_id;
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;
        if (filters.value.status) params.status = filters.value.status;

        const response = await axios.get('/api/prorektor/test-results', { params });

        if (response.data.success) {
            results.value = response.data.data.data || [];
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
        console.error('Results load error:', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const loadTests = async () => {
    try {
        const response = await axios.get('/api/prorektor/tests');
        if (response.data.success) {
            tests.value = response.data.data || [];
        } else {
            tests.value = [];
        }
    } catch (error) {
        console.error('Tests load error:', error);
        tests.value = [];
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
const searchResults = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadResults();
    }, 500);
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadResults();
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

const showResultDetails = async (result) => {
    try {
        const response = await axios.get(`/api/prorektor/test-results/${result.id}`);

        if (response.data.success) {
            selectedResult.value = response.data.data;
            showDetailsModal.value = true;
        }
    } catch (error) {
        console.error('Load result details error:', error);
        alert('Natijalarni yuklashda xatolik');
    }
};

const formatDate = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('uz-UZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(async () => {
    await loadTests();
    await loadFaculties();
    await loadDepartments();
    await loadResults();
});
</script>
