<template>
    <div class="space-y-6">
        <!-- Faculty Selector -->
        <div class="card">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Fakultetni tanlang:</label>
            <select v-model="selectedFacultyId" @change="loadFacultyReport" class="form-input w-full max-w-md">
                <option value="">Fakultet tanlang...</option>
                <template v-if="faculties && faculties.length > 0">
                    <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                        {{ faculty.name }}
                    </option>
                </template>
            </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- No Faculty Selected -->
        <div v-else-if="!selectedFacultyId" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-600">Fakultetni tanlang</p>
        </div>

        <!-- Faculty Report -->
        <template v-else-if="report && report.faculty">
            <!-- Faculty Info -->
            <div class="card">
                <h2 class="text-2xl font-bold mb-4">{{ report.faculty.name || 'Fakultet' }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-800">O'qituvchilar</p>
                        <p class="text-3xl font-bold text-blue-900">{{ report.stats?.total_teachers || 0 }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-800">Jami Testlar</p>
                        <p class="text-3xl font-bold text-purple-900">{{ report.stats?.total_tests || 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-800">O'tgan Testlar</p>
                        <p class="text-3xl font-bold text-green-900">{{ report.stats?.passed_tests || 0 }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">O'rtacha Ball</p>
                        <p class="text-3xl font-bold text-yellow-900">{{ formatScore(report.stats?.average_test_score) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Portfolio Stats -->
            <div class="card">
                <h3 class="text-xl font-bold mb-4">Portfolio Statistikasi</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-800">Jami Portfoliolar</p>
                        <p class="text-3xl font-bold text-blue-900">{{ report.stats?.total_portfolios || 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-800">Baholangan</p>
                        <p class="text-3xl font-bold text-green-900">{{ report.stats?.evaluated_portfolios || 0 }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">Kutilmoqda</p>
                        <p class="text-3xl font-bold text-yellow-900">
                            {{ (report.stats?.total_portfolios || 0) - (report.stats?.evaluated_portfolios || 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Departments -->
            <div v-if="report.faculty.departments && report.faculty.departments.length > 0" class="card">
                <h3 class="text-xl font-bold mb-4">Kafedralar</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mudiri</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Telefon</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="dept in report.faculty.departments" :key="dept.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ dept.name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ dept.head_name || '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ dept.phone || '—' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Teachers List -->
            <div v-if="report.teachers && report.teachers.length > 0" class="card">
                <h3 class="text-xl font-bold mb-4">O'qituvchilar</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">F.I.O</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Testlar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Portfolio</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="teacher in report.teachers" :key="teacher.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ teacher.full_name }}</p>
                                <p class="text-xs text-gray-500">{{ teacher.scientific_degree }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ teacher.department?.name || '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-green-600 font-semibold">{{ teacher.passed_tests || 0 }}</span>
                                <span class="text-gray-500"> / {{ teacher.total_tests || 0 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-green-600 font-semibold">{{ teacher.evaluated_files || 0 }}</span>
                                <span class="text-gray-500"> / {{ teacher.total_files || 0 }}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty Teachers -->
            <div v-else class="card text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-600">Bu fakultetda o'qituvchilar topilmadi</p>
            </div>
        </template>

        <!-- Error State -->
        <div v-else-if="!loading && selectedFacultyId" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-600">Ma'lumotlarni yuklashda xatolik</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const faculties = ref([]);
const selectedFacultyId = ref('');
const report = ref(null);

const formatScore = (score) => {
    if (score === null || score === undefined) return '0';
    const num = parseFloat(score);
    if (isNaN(num)) return '0';
    return num.toFixed(1);
};

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties/all'); // ← YANGILANDI

        if (response.data.success) {
            faculties.value = response.data.data || [];
        } else if (Array.isArray(response.data)) {
            faculties.value = response.data;
        } else {
            faculties.value = [];
        }

        console.log('📚 Faculties loaded:', faculties.value.length);
    } catch (error) {
        console.error('Faculties load error:', error);
        faculties.value = [];
    }
};

const loadFacultyReport = async () => {
    if (!selectedFacultyId.value) {
        report.value = null;
        return;
    }

    loading.value = true;
    try {
        console.log('📊 Loading faculty report for ID:', selectedFacultyId.value);

        const response = await axios.get(`/api/prorektor/reports/faculty/${selectedFacultyId.value}`);

        if (response.data.success) {
            report.value = response.data.data;
            console.log('✅ Faculty report loaded:', report.value);
        } else {
            console.error('❌ Invalid response:', response.data);
            report.value = null;
        }
    } catch (error) {
        console.error('❌ Faculty report load error:', error);
        report.value = null;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadFaculties();
});
</script>
