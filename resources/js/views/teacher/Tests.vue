<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Testlar</h1>
            <p class="text-gray-600 mt-1">Sizga ruxsat berilgan testlar</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="tests.length === 0" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 mt-4 text-lg font-semibold">Sizga hali testlar ruxsat berilmagan</p>
            <p class="text-gray-500 mt-2">ProRektor sizga testlarni faollashtirishi kerak</p>
        </div>

        <!-- Tests List -->
        <div v-else class="grid grid-cols-1 gap-6">
            <div
                v-for="item in tests"
                :key="item.test.id"
                class="card hover:shadow-lg transition-shadow"
            >
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ item.test.title }}</h3>
                        <div class="flex flex-wrap gap-2">
              <span :class="item.test.type === 'entry' ? 'badge-blue' : 'badge-purple'" class="badge">
                {{ item.test.type === 'entry' ? 'Kiruvchi' : 'Chiquvchi' }}
              </span>
                            <span
                                :class="{
                  'badge-success': item.status === 'passed',
                  'badge-danger': item.status === 'failed',
                  'badge-warning': item.status === 'not_started'
                }"
                                class="badge"
                            >
                {{
                                    item.status === 'passed' ? 'O\'tdingiz' :
                                        item.status === 'failed' ? 'O\'tmadingiz' :
                                            'Topshirilmagan'
                                }}
              </span>
                        </div>
                    </div>
                </div>

                <!-- Test Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Savollar soni</p>
                        <p class="text-lg font-bold text-gray-900">{{ item.test.questions_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Vaqt</p>
                        <p class="text-lg font-bold text-gray-900">{{ item.test.duration_minutes }} daqiqa</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jami ball</p>
                        <p class="text-lg font-bold text-gray-900">{{ item.test.total_points }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">O'tish bali</p>
                        <p class="text-lg font-bold text-gray-900">{{ item.test.pass_score }}%</p>
                    </div>
                </div>

                <!-- Last Result -->
                <div v-if="item.last_result" class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-900 mb-2">Oxirgi natija:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Ball</p>
                            <p class="font-bold text-gray-900">{{ item.last_result.score }} / {{ item.test.total_points }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Foiz</p>
                            <p class="font-bold text-gray-900">{{ item.last_result.percentage }}%</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Holat</p>
                            <span :class="item.last_result.passed ? 'text-green-600' : 'text-red-600'" class="font-bold">
                {{ item.last_result.passed ? 'O\'tdi' : 'O\'tmadi' }}
              </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Sana</p>
                            <p class="text-sm text-gray-900">{{ formatDate(item.last_result.finished_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button
                        v-if="item.can_take"
                        @click="startTest(item.test)"
                        class="btn-primary flex-1"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Testni boshlash
                    </button>
                    <button
                        v-else
                        disabled
                        class="btn-secondary flex-1 opacity-50 cursor-not-allowed"
                    >
                        {{ item.test.allow_retake ? 'Qayta topshirish mumkin emas' : 'Allaqachon topshirgansiz' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Test Taking Modal -->
        <TestTakingModal
            v-if="showTestModal"
            :test="selectedTest"
            @close="showTestModal = false"
            @success="handleTestSubmit"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TestTakingModal from '@/components/teacher/TestTakingModal.vue';

const loading = ref(false);
const tests = ref([]);
const selectedTest = ref(null);
const showTestModal = ref(false);

const loadTests = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/tests');
        if (response.data.success) {
            tests.value = response.data.data;
        }
    } catch (error) {
        console.error('Testlarni yuklashda xatolik:', error);
        alert('Testlarni yuklashda xatolik yuz berdi');
    } finally {
        loading.value = false;
    }
};

const startTest = async (test) => {
    try {
        const response = await axios.get(`/api/teacher/tests/${test.id}`);
        if (response.data.success) {
            selectedTest.value = response.data.data;
            showTestModal.value = true;
        }
    } catch (error) {
        console.error('Test yuklashda xatolik:', error);
        alert(error.response?.data?.message || 'Test yuklashda xatolik yuz berdi');
    }
};

const handleTestSubmit = () => {
    showTestModal.value = false;
    selectedTest.value = null;
    loadTests();
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
    loadTests();
});
</script>
