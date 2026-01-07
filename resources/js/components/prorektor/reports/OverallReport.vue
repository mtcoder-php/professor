<template>
    <div class="space-y-6">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <template v-else>
            <!-- Teacher Stats -->
            <div class="card">
                <h2 class="text-xl font-bold mb-4">O'qituvchilar</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-800">Jami O'qituvchilar</p>
                        <p class="text-3xl font-bold text-blue-900">{{ stats.total_teachers || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Test Stats -->
            <div class="card">
                <h2 class="text-xl font-bold mb-4">Test Natijalari</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-800">Jami Testlar</p>
                        <p class="text-3xl font-bold text-purple-900">{{ stats.total_test_results || 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-800">O'tgan</p>
                        <p class="text-3xl font-bold text-green-900">{{ stats.passed_tests || 0 }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-red-800">O'tmagan</p>
                        <p class="text-3xl font-bold text-red-900">{{ stats.failed_tests || 0 }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">O'rtacha Ball</p>
                        <p class="text-3xl font-bold text-yellow-900">{{ formatScore(stats.average_test_score) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Portfolio Stats -->
            <div class="card">
                <h2 class="text-xl font-bold mb-4">Portfolio</h2>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-800">Jami Fayllar</p>
                        <p class="text-3xl font-bold text-blue-900">{{ stats.total_portfolio_files || 0 }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">Kutilmoqda</p>
                        <p class="text-3xl font-bold text-yellow-900">{{ stats.pending_portfolios || 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-800">Baholangan</p>
                        <p class="text-3xl font-bold text-green-900">{{ stats.evaluated_portfolios || 0 }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-red-800">Rad etilgan</p>
                        <p class="text-3xl font-bold text-red-900">{{ stats.rejected_portfolios || 0 }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-800">O'rtacha Ball</p>
                        <p class="text-3xl font-bold text-purple-900">{{ formatScore(stats.average_portfolio_score) }}</p>
                    </div>
                </div>
            </div>

            <!-- By Faculty -->
            <div v-if="stats.by_faculty && stats.by_faculty.length > 0" class="card">
                <h2 class="text-xl font-bold mb-4">Fakultetlar bo'yicha</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fakultet</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">O'qituvchilar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Portfolio</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="faculty in stats.by_faculty" :key="faculty.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ faculty.name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ faculty.teachers_count || 0 }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ faculty.portfolio_files_count || 0 }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="card text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-600">Ma'lumotlar topilmadi</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const stats = ref({});

const formatScore = (score) => {
    if (score === null || score === undefined) return '0';
    const num = parseFloat(score);
    if (isNaN(num)) return '0';
    return num.toFixed(1);
};

const loadStats = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/prorektor/reports/overall');

        if (response.data.success) {
            stats.value = response.data.data;
            console.log('📊 Stats loaded:', stats.value);
        }
    } catch (error) {
        console.error('Load stats error:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadStats();
});
</script>
