<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Welcome Section -->
        <div class="card-gradient">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gradient mb-2">
                        Xush kelibsiz, {{ user?.full_name }}! 👋
                    </h1>
                    <p class="text-gray-600">Admin Dashboard - Tizim boshqaruvi</p>
                </div>
                <div class="hidden md:block">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Bugungi sana</p>
                        <p class="text-lg font-semibold text-gray-700">{{ currentDate }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Stats Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Fakultetlar -->
            <div class="stat-card group cursor-pointer" @click="$router.push('/admin/faculties')">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-white/80 text-sm font-medium">Fakultetlar</p>
                        <p class="text-4xl font-bold text-white">{{ stats.total_faculties || 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center text-white/80 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Faol</span>
                </div>
            </div>

            <!-- Kafedralar -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer" @click="$router.push('/admin/departments')">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-white/80 text-sm font-medium">Kafedralar</p>
                        <p class="text-4xl font-bold text-white">{{ stats.total_departments || 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center text-white/80 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Faol</span>
                </div>
            </div>

            <!-- O'qituvchilar -->
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer" @click="$router.push('/admin/users')">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-white/80 text-sm font-medium">O'qituvchilar</p>
                        <p class="text-4xl font-bold text-white">{{ stats.total_teachers || 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center text-white/80 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span>Ro'yxatdan o'tgan</span>
                </div>
            </div>

            <!-- Testlar -->
            <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-6 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer" @click="$router.push('/admin/tests')">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-white/80 text-sm font-medium">Testlar</p>
                        <p class="text-4xl font-bold text-white">{{ stats.total_tests || 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center text-white/80 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ stats.active_tests || 0 }} ta faol</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activity -->
            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">So'nggi faoliyat</h3>
                    <button @click="loadRecentActivity" class="text-sm text-green-600 hover:text-green-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Yangilash
                    </button>
                </div>

                <!-- Loading -->
                <div v-if="loadingActivity" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                </div>

                <!-- Activities -->
                <div v-else-if="recentActivity.length > 0" class="space-y-4">
                    <div
                        v-for="activity in recentActivity"
                        :key="activity.timestamp"
                        class="flex items-start space-x-4 p-4 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="getActivityBgClass(activity.color)"
                    >
                        <div class="p-2 rounded-lg" :class="getActivityIconClass(activity.color)">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <!-- User icon -->
                                <path v-if="activity.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                <!-- Clipboard icon -->
                                <path v-else-if="activity.icon === 'clipboard'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                <!-- Folder icon -->
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">{{ activity.title }}</p>
                            <p class="text-sm text-gray-500">{{ activity.description }} • {{ formatActivityTime(activity.timestamp) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>Hozircha faoliyat yo'q</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Tezkor havolalar</h3>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <router-link to="/admin/faculties" class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:shadow-lg transition-all group">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="p-3 bg-blue-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700">Fakultetlar</span>
                        </div>
                    </router-link>

                    <router-link to="/admin/departments" class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:shadow-lg transition-all group">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="p-3 bg-green-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700">Kafedralar</span>
                        </div>
                    </router-link>

                    <router-link to="/admin/users" class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl hover:shadow-lg transition-all group">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="p-3 bg-purple-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700">O'qituvchilar</span>
                        </div>
                    </router-link>

                    <router-link to="/admin/tests" class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl hover:shadow-lg transition-all group">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="p-3 bg-orange-500 rounded-xl group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700">Testlar</span>
                        </div>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const loading = ref(false);
const loadingActivity = ref(false);
const stats = ref({
    total_faculties: 0,
    total_departments: 0,
    total_teachers: 0,
    total_tests: 0,
    active_tests: 0
});
const recentActivity = ref([]);

const currentDate = computed(() => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date().toLocaleDateString('uz-UZ', options);
});

const loadStats = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/dashboard/stats');

        if (response.data.success) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Load stats error:', error);
    } finally {
        loading.value = false;
    }
};

const loadRecentActivity = async () => {
    loadingActivity.value = true;
    try {
        const response = await axios.get('/api/admin/dashboard/recent-activity');

        if (response.data.success) {
            recentActivity.value = response.data.data;
        }
    } catch (error) {
        console.error('Load activity error:', error);
        recentActivity.value = [];
    } finally {
        loadingActivity.value = false;
    }
};

const getActivityBgClass = (color) => {
    const classes = {
        'green': 'bg-green-50 hover:bg-green-100',
        'blue': 'bg-blue-50 hover:bg-blue-100',
        'purple': 'bg-purple-50 hover:bg-purple-100'
    };
    return classes[color] || 'bg-gray-50 hover:bg-gray-100';
};

const getActivityIconClass = (color) => {
    const classes = {
        'green': 'bg-green-500',
        'blue': 'bg-blue-500',
        'purple': 'bg-purple-500'
    };
    return classes[color] || 'bg-gray-500';
};

const formatActivityTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'hozir';
    if (diffMins < 60) return `${diffMins} daqiqa oldin`;
    if (diffHours < 24) return `${diffHours} soat oldin`;
    if (diffDays === 1) return 'kecha';
    if (diffDays < 7) return `${diffDays} kun oldin`;

    return date.toLocaleDateString('uz-UZ', {
        day: 'numeric',
        month: 'short'
    });
};

onMounted(async () => {
    await loadStats();
    await loadRecentActivity();
});
</script>
