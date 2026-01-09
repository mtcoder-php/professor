<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Hisobotlar</h1>
            <p class="text-gray-600 mt-1">O'qituvchilar bo'yicha to'liq hisobot</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                <p class="text-sm opacity-90">Fakultetlar</p>
                <p class="text-3xl font-bold">{{ stats.total_faculties || 0 }}</p>
            </div>
            <div class="card bg-gradient-to-br from-green-500 to-green-600 text-white">
                <p class="text-sm opacity-90">Kafedralar</p>
                <p class="text-3xl font-bold">{{ stats.total_departments || 0 }}</p>
            </div>
            <div class="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                <p class="text-sm opacity-90">O'qituvchilar</p>
                <p class="text-3xl font-bold">{{ stats.total_teachers || 0 }}</p>
            </div>
            <div class="card bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
                <p class="text-sm opacity-90">Testlar</p>
                <p class="text-3xl font-bold">{{ stats.total_tests || 0 }}</p>
            </div>
            <div class="card bg-gradient-to-br from-red-500 to-red-600 text-white">
                <p class="text-sm opacity-90">Test Natijalari</p>
                <p class="text-3xl font-bold">{{ stats.total_test_results || 0 }}</p>
            </div>
            <div class="card bg-gradient-to-br from-indigo-500 to-indigo-600 text-white">
                <p class="text-sm opacity-90">Portfoliolar</p>
                <p class="text-3xl font-bold">{{ stats.total_portfolios || 0 }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <h3 class="text-lg font-bold mb-4">Filtrlar</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Faculty Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fakultet:</label>
                    <select v-model="filters.faculty_id" @change="onFacultyChange" class="form-input w-full">
                        <option value="">Barcha fakultetlar</option>
                        <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                            {{ faculty.name }}
                        </option>
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kafedra:</label>
                    <select v-model="filters.department_id" @change="loadReport" class="form-input w-full">
                        <option value="">Barcha kafedralar</option>
                        <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button @click="loadReport" class="btn-primary flex items-center gap-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Yangilash
                    </button>
                    <button @click="exportExcel" :disabled="!hasData" class="btn-secondary">
                        Excel
                    </button>
                    <button @click="exportPdf" :disabled="!hasData" class="btn-secondary">
                        PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Report Table -->
        <div v-else-if="hasData" class="card overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">№</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">F.I.O</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fakultet</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>

                    <!-- Entry Tests -->
                    <th
                        v-for="test in reportData.entry_tests"
                        :key="'entry-' + test.id"
                        class="px-4 py-3 text-center text-xs font-semibold text-white uppercase bg-green-600"
                    >
                        {{ test.title }}<br>
                        <span class="text-[10px] font-normal">(Kiruvchi)</span>
                    </th>

                    <!-- Exit Tests -->
                    <th
                        v-for="test in reportData.exit_tests"
                        :key="'exit-' + test.id"
                        class="px-4 py-3 text-center text-xs font-semibold text-white uppercase bg-blue-600"
                    >
                        {{ test.title }}<br>
                        <span class="text-[10px] font-normal">(Chiquvchi)</span>
                    </th>

                    <!-- Portfolio Total -->
                    <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase bg-purple-600">
                        PORTFOLIO<br>
                        <span class="text-[10px] font-normal">(Umumiy ball)</span>
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr v-for="(teacher, index) in reportData.teachers" :key="teacher.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-center font-medium text-gray-900">{{ index + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ teacher.full_name }}</div>
                        <div class="text-xs text-gray-500">{{ teacher.scientific_degree }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ teacher.faculty }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ teacher.department }}</td>

                    <!-- Entry Test Results -->
                    <td
                        v-for="(entryTest, idx) in teacher.entry_tests"
                        :key="'entry-result-' + idx"
                        class="px-4 py-3 text-center bg-green-50"
                    >
                        <template v-if="entryTest.score !== null">
                            <div class="font-bold text-lg" :class="entryTest.passed ? 'text-green-600' : 'text-red-600'">
                                {{ entryTest.score }}/{{ entryTest.total_points }}
                            </div>
                        </template>
                        <span v-else class="text-gray-400">—</span>
                    </td>

                    <!-- Exit Test Results -->
                    <td
                        v-for="(exitTest, idx) in teacher.exit_tests"
                        :key="'exit-result-' + idx"
                        class="px-4 py-3 text-center bg-blue-50"
                    >
                        <template v-if="exitTest.score !== null">
                            <div class="font-bold text-lg" :class="exitTest.passed ? 'text-green-600' : 'text-red-600'">
                                {{ exitTest.score }}/{{ exitTest.total_points }}
                            </div>
                        </template>
                        <span v-else class="text-gray-400">—</span>
                    </td>

                    <!-- Portfolio Total -->
                    <td class="px-4 py-3 text-center bg-purple-50">
                        <div class="font-bold text-xl text-purple-600">
                            {{ teacher.portfolio_total || 0 }}
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div v-else class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600">Ma'lumotlar topilmadi</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const stats = ref({});
const faculties = ref([]);
const departments = ref([]);
const reportData = ref({
    teachers: [],
    entry_tests: [],
    exit_tests: []
});

const filters = ref({
    faculty_id: '',
    department_id: ''
});

const filteredDepartments = computed(() => {
    if (!filters.value.faculty_id) return departments.value;
    return departments.value.filter(d => d.faculty_id == filters.value.faculty_id);
});

const hasData = computed(() => {
    return reportData.value.teachers && reportData.value.teachers.length > 0;
});

const loadStats = async () => {
    try {
        const response = await axios.get('/api/admin/reports/overall');
        if (response.data.success) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Load stats error:', error);
    }
};

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties/all');
        if (response.data.success) {
            faculties.value = response.data.data || [];
        }
    } catch (error) {
        console.error('Load faculties error:', error);
    }
};

const loadDepartments = async () => {
    try {
        const response = await axios.get('/api/departments/all');
        if (response.data.success) {
            departments.value = response.data.data || [];
        }
    } catch (error) {
        console.error('Load departments error:', error);
    }
};

const onFacultyChange = () => {
    filters.value.department_id = '';
    loadReport();
};

const loadReport = async () => {
    loading.value = true;
    try {
        const params = {};
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;

        const response = await axios.get('/api/admin/reports/teachers', { params });

        if (response.data.success) {
            reportData.value = response.data.data;
        }
    } catch (error) {
        console.error('Load report error:', error);
    } finally {
        loading.value = false;
    }
};

const exportExcel = () => {
    const params = new URLSearchParams();
    if (filters.value.faculty_id) params.append('faculty_id', filters.value.faculty_id);
    if (filters.value.department_id) params.append('department_id', filters.value.department_id);
    window.open(`/api/admin/reports/export-excel?${params.toString()}`, '_blank');
};

const exportPdf = () => {
    const params = new URLSearchParams();
    if (filters.value.faculty_id) params.append('faculty_id', filters.value.faculty_id);
    if (filters.value.department_id) params.append('department_id', filters.value.department_id);
    window.open(`/api/admin/reports/export-pdf?${params.toString()}`, '_blank');
};

onMounted(async () => {
    await loadStats();
    await loadFaculties();
    await loadDepartments();
    await loadReport();
});
</script>
