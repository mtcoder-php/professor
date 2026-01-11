<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Welcome -->
        <div class="card-gradient">
            <h1 class="text-3xl font-bold text-gradient mb-2">
                Xush kelibsiz, {{ user?.full_name }}! 👋
            </h1>
            <p class="text-gray-600">ProRektor Dashboard - Baholash tizimi</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Stats Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Teachers -->
            <div class="stat-card">
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
            </div>

            <!-- Total Tests -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
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
            </div>

            <!-- Pending Portfolios -->
            <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-white/80 text-sm font-medium">Kutilmoqda</p>
                        <p class="text-4xl font-bold text-white">{{ stats.pending_portfolios || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pass Rate -->
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-white/80 text-sm font-medium">O'tish foizi</p>
                        <p class="text-4xl font-bold text-white">{{ stats.pass_rate || 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <router-link to="/prorektor/test-permissions" class="card hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-blue-100 rounded-xl">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Test ruxsatlari</h3>
                        <p class="text-sm text-gray-600">Ruxsat berish va boshqarish</p>
                    </div>
                </div>
            </router-link>

            <router-link to="/prorektor/test-results" class="card hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-green-100 rounded-xl">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Test natijalari</h3>
                        <p class="text-sm text-gray-600">Natijalarni ko'rish</p>
                    </div>
                </div>
            </router-link>

            <router-link to="/prorektor/portfolio-evaluation" class="card hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-purple-100 rounded-xl">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Portfolio baholash</h3>
                        <p class="text-sm text-gray-600">Fayllarni baholash</p>
                    </div>
                </div>
            </router-link>
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
const stats = ref({
    total_teachers: 0,
    total_tests: 0,
    pending_portfolios: 0,
    pass_rate: 0
});

const loadDashboard = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/prorektor/dashboard');

        if (response.data.success) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Dashboard yuklashda xatolik:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadDashboard();
});
</script>
