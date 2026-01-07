<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">Shaxsiy kabinet</h1>
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
            <!-- User Info Card -->
            <div class="card mb-6">
                <div class="flex items-start gap-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <img
                            v-if="user?.avatar"
                            :src="`/storage/${user.avatar}`"
                            :alt="user.full_name"
                            class="w-24 h-24 rounded-full object-cover border-4 border-green-100"
                        />
                        <div v-else class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center border-4 border-green-200">
              <span class="text-green-600 font-bold text-3xl">
                {{ user?.full_name?.charAt(0) }}
              </span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900">{{ user?.full_name }}</h2>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600">
                                <strong>Fakultet:</strong> {{ user?.faculty?.name || '—' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Kafedra:</strong> {{ user?.department?.name || '—' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Ilmiy daraja:</strong> {{ user?.scientific_degree || '—' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Passport:</strong> {{ user?.passport_series }}
                            </p>
                        </div>
                    </div>

                    <!-- Edit Button -->
                    <router-link to="/teacher/profile" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Tahrirlash
                    </router-link>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Portfolio Progress -->
                <div class="card bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-green-800">Portfolio</h3>
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="mb-2">
                        <div class="flex items-end gap-1">
                            <span class="text-3xl font-bold text-green-900">{{ statistics?.portfolio_percentage || 0 }}%</span>
                        </div>
                        <p class="text-xs text-green-700 mt-1">
                            {{ statistics?.uploaded_categories || 0 }} / {{ statistics?.total_categories || 15 }} kategoriya
                        </p>
                    </div>
                    <div class="w-full bg-green-200 rounded-full h-2">
                        <div
                            class="bg-green-600 h-2 rounded-full transition-all"
                            :style="{ width: `${statistics?.portfolio_percentage || 0}%` }"
                        ></div>
                    </div>
                </div>

                <!-- Portfolio Files -->
                <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-blue-800">Yuklangan fayllar</h3>
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-blue-900 mb-2">
                        {{ statistics?.portfolio_files?.total || 0 }}
                    </div>
                    <div class="flex gap-2 text-xs">
                        <span class="text-yellow-700">⏳ {{ statistics?.portfolio_files?.pending || 0 }}</span>
                        <span class="text-green-700">✓ {{ statistics?.portfolio_files?.evaluated || 0 }}</span>
                        <span class="text-red-700">✗ {{ statistics?.portfolio_files?.rejected || 0 }}</span>
                    </div>
                </div>

                <!-- Available Tests -->
                <div class="card bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-purple-800">Testlar</h3>
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-purple-900 mb-2">
                        {{ statistics?.test_permissions || 0 }}
                    </div>
                    <p class="text-xs text-purple-700">Ruxsat berilgan</p>
                </div>

                <!-- Completed Tests -->
                <div class="card bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-orange-800">Topshirilgan</h3>
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-orange-900 mb-2">
                        {{ statistics?.completed_tests || 0 }}
                    </div>
                    <p class="text-xs text-orange-700">Test topshirildi</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <router-link to="/teacher/tests" class="card hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Testlar</h3>
                        <p class="text-sm text-gray-600">Testlarni topshirish</p>
                    </div>
                </router-link>

                <router-link to="/teacher/portfolio" class="card hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Portfolio</h3>
                        <p class="text-sm text-gray-600">Fayllarni yuklash</p>
                    </div>
                </router-link>

                <router-link to="/teacher/profile" class="card hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Profil</h3>
                        <p class="text-sm text-gray-600">Sozlamalar</p>
                    </div>
                </router-link>
            </div>

            <!-- Recent Tests -->
            <div v-if="recentTests && recentTests.length > 0" class="card">
                <h3 class="text-lg font-semibold mb-4">So'nggi testlar</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Test</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Topshirilgan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ball</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Foiz</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="result in recentTests" :key="result.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ result.test?.title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ formatDate(result.finished_at) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">
                                {{ result.score }} / {{ result.test?.total_points }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ result.percentage }}%
                            </td>
                            <td class="px-4 py-3">
                  <span :class="result.passed ? 'badge-success' : 'badge-danger'" class="badge">
                    {{ result.passed ? 'O\'tdi' : 'O\'tmadi' }}
                  </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
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
const recentTests = ref([]);
const availableTests = ref([]);

const loadDashboard = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/dashboard');
        if (response.data.success) {
            user.value = response.data.data.user;
            statistics.value = response.data.data.statistics;
            recentTests.value = response.data.data.recent_tests;
            availableTests.value = response.data.data.available_tests;
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    loadDashboard();
});
</script>
