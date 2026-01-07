<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">Dashboard</h1>
                <p class="text-gray-600 mt-1">Xush kelibsiz, {{ user?.full_name }}</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Content -->
        <div v-else>
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Teachers -->
                <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-blue-800">O'qituvchilar</h3>
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-blue-900">{{ statistics?.total_teachers || 0 }}</p>
                    <p class="text-xs text-blue-700 mt-1">Jami o'qituvchilar</p>
                </div>

                <!-- Test Permissions -->
                <div class="card bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-green-800">Test ruxsatlari</h3>
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-green-900">{{ statistics?.test_permissions || 0 }}</p>
                    <p class="text-xs text-green-700 mt-1">Ruxsat berilgan</p>
                </div>

                <!-- Completed Tests -->
                <div class="card bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-purple-800">Topshirilgan</h3>
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-purple-900">{{ statistics?.completed_tests || 0 }}</p>
                    <p class="text-xs text-purple-700 mt-1">Test topshirildi</p>
                </div>

                <!-- Pending Portfolios -->
                <div class="card bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-orange-800">Kutilmoqda</h3>
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-orange-900">{{ statistics?.portfolio?.pending || 0 }}</p>
                    <p class="text-xs text-orange-700 mt-1">Portfolio fayllar</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <router-link to="/prorektor/test-permissions" class="card hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Test Ruxsatlari</h3>
                        <p class="text-sm text-gray-600">Testlarni faollashtirish</p>
                    </div>
                </router-link>

                <router-link to="/prorektor/portfolio-evaluations" class="card hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Portfolio Baholash</h3>
                        <p class="text-sm text-gray-600">Fayllarni baholash</p>
                    </div>
                </router-link>

                <router-link to="/prorektor/test-results" class="card hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Test Natijalari</h3>
                        <p class="text-sm text-gray-600">Natijalarni ko'rish</p>
                    </div>
                </router-link>
            </div>

            <!-- Recent Test Results -->
            <div v-if="recentResults && recentResults.length > 0" class="card mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">So'nggi test natijalari</h3>
                    <router-link to="/prorektor/test-results" class="text-sm text-green-600 hover:text-green-700">
                        Barchasini ko'rish →
                    </router-link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">O'qituvchi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Test</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ball</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Foiz</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sana</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="result in recentResults" :key="result.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ result.user?.full_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ result.test?.title }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ result.score }} / {{ result.test?.total_points }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ result.percentage }}%</td>
                            <td class="px-4 py-3">
                  <span :class="result.passed ? 'badge-success' : 'badge-danger'" class="badge">
                    {{ result.passed ? 'O\'tdi' : 'O\'tmadi' }}
                  </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(result.finished_at) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Portfolios -->
            <div v-if="pendingPortfolios && pendingPortfolios.length > 0" class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Baholash kutilmoqda</h3>
                    <router-link to="/prorektor/portfolio-evaluations" class="text-sm text-green-600 hover:text-green-700">
                        Barchasini ko'rish →
                    </router-link>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="file in pendingPortfolios"
                        :key="file.id"
                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900">{{ file.user?.full_name }}</p>
                                <p class="text-sm text-gray-600">{{ file.category?.name }}</p>
                            </div>
                        </div>
                        <router-link
                            :to="`/prorektor/portfolio-evaluations/teacher/${file.user_id}`"
                            class="btn-secondary text-sm"
                        >
                            Baholash
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const user = ref(null);
const statistics = ref(null);
const recentResults = ref([]);
const pendingPortfolios = ref([]);

const loadDashboard = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/prorektor/dashboard');
        if (response.data.success) {
            user.value = response.data.data.user;
            statistics.value = response.data.data.statistics;
            recentResults.value = response.data.data.recent_results;
            pendingPortfolios.value = response.data.data.pending_portfolios;
        }
    } catch (error) {
        console.error('Dashboard yuklashda xatolik:', error);
        alert('Dashboard yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    loadDashboard();
});
</script>
