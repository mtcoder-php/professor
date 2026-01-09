<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Testlar</h1>
            <p class="text-gray-600 mt-1">Ruxsat etilgan testlar ro'yxati</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Tests Grid -->
        <div v-else-if="tests.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                v-for="test in tests"
                :key="test.id"
                class="card hover:shadow-lg transition-shadow"
            >
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ test.title }}</h3>
                        <div class="flex gap-2 mt-2">
                            <span :class="test.type === 'entry' ? 'badge-blue' : 'badge-purple'" class="badge">
                                {{ test.type === 'entry' ? 'Kiruvchi' : 'Chiquvchi' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">
                            {{ test.questions_per_attempt || test.questions_count }}
                        </p>
                        <p class="text-xs text-gray-600">Savollar</p>
                        <p v-if="test.questions_per_attempt" class="text-[10px] text-gray-500">
                            ({{ test.questions_count }} ta dan)
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">
                            {{ getEffectiveTotalPoints(test) }}
                        </p>
                        <p class="text-xs text-gray-600">Ball</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ test.duration_minutes }}</p>
                        <p class="text-xs text-gray-600">Daqiqa</p>
                    </div>
                </div>

                <!-- Latest Result -->
                <div v-if="test.latest_result" class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Oxirgi natija:</p>
                            <p class="text-2xl font-bold" :class="test.latest_result.passed ? 'text-green-600' : 'text-red-600'">
                                {{ test.latest_result.score }} / {{ getEffectiveTotalPoints(test) }}
                            </p>
                            <p class="text-sm text-gray-600">{{ test.latest_result.percentage }}%</p>
                        </div>
                        <div :class="test.latest_result.passed ? 'badge-success' : 'badge-danger'" class="badge">
                            {{ test.latest_result.passed ? 'O\'tdi' : 'O\'tmadi' }}
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ formatDate(test.latest_result.finished_at) }}
                    </p>
                </div>

                <!-- Info -->
                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">O'tish bali:</span>
                        <span class="font-semibold text-gray-900">{{ test.pass_score }}%</span>
                    </div>
                    <div v-if="test.start_date" class="flex justify-between">
                        <span class="text-gray-600">Boshlanish:</span>
                        <span class="font-semibold text-gray-900">{{ formatDate(test.start_date) }}</span>
                    </div>
                    <div v-if="test.end_date" class="flex justify-between">
                        <span class="text-gray-600">Tugash:</span>
                        <span class="font-semibold text-gray-900">{{ formatDate(test.end_date) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t">
                    <button
                        v-if="test.can_take"
                        @click="startTest(test)"
                        class="btn-primary w-full flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ test.latest_result ? 'Qayta topshirish' : 'Testni boshlash' }}
                    </button>

                    <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm font-medium text-yellow-800 mb-1">
                            <span v-if="!test.is_active">❌ Test faol emas</span>
                            <span v-else-if="!test.allow_retake && test.latest_result">❌ Qayta topshirish mumkin emas</span>
                            <span v-else-if="isBeforeStart(test)">⏰ Test hali boshlanmagan</span>
                            <span v-else-if="isAfterEnd(test)">⏱️ Test tugagan</span>
                            <span v-else>❓ Test hozirda mavjud emas</span>
                        </p>

                        <p v-if="isBeforeStart(test)" class="text-xs text-yellow-700 mt-1">
                            Boshlanish: {{ formatDate(test.start_date) }}
                        </p>

                        <p v-if="isAfterEnd(test)" class="text-xs text-yellow-700 mt-1">
                            Tugagan: {{ formatDate(test.end_date) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600">Sizga hech qanday test ruxsat etilmagan</p>
        </div>

        <!-- Test Taking Modal -->
        <TestTakingModal
            v-if="showTakeTestModal"
            :test="selectedTest"
            @close="closeTakeTestModal"
            @success="handleTestSuccess"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TestTakingModal from '@/components/teacher/TestTakingModal.vue';

const loading = ref(false);
const tests = ref([]);
const showTakeTestModal = ref(false);
const selectedTest = ref(null);

const loadTests = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/tests');

        if (response.data.success) {
            tests.value = response.data.data;
            console.log('✅ Tests loaded:', tests.value);
            console.log('📊 Total tests:', tests.value.length);

            tests.value.forEach((test, index) => {
                console.log(`Test ${index + 1}:`, {
                    id: test.id,
                    title: test.title,
                    can_take: test.can_take,
                    questions_count: test.questions_count,
                    questions_per_attempt: test.questions_per_attempt,
                    points_per_question: test.points_per_question,
                    effective_total: getEffectiveTotalPoints(test)
                });
            });
        }
    } catch (error) {
        console.error('❌ Load tests error:', error);
    } finally {
        loading.value = false;
    }
};

const startTest = async (test) => {
    console.log('🚀 Starting test:', test.id);

    try {
        // Load test with questions
        const response = await axios.get(`/api/teacher/tests/${test.id}`);

        if (response.data.success) {
            selectedTest.value = response.data.data;
            showTakeTestModal.value = true;

            console.log('✅ Test loaded for modal:', selectedTest.value);
        }
    } catch (error) {
        console.error('❌ Load test error:', error);
        alert(error.response?.data?.message || 'Xatolik yuz berdi');
    }
};

const closeTakeTestModal = () => {
    showTakeTestModal.value = false;
    selectedTest.value = null;
};

const handleTestSuccess = () => {
    closeTakeTestModal();
    loadTests(); // Reload tests to update results
};

const getEffectiveTotalPoints = (test) => {
    if (!test) return 0;

    const effectiveQuestions = test.questions_per_attempt || test.questions_count || 0;
    const pointsPerQuestion = test.points_per_question || 0;

    return effectiveQuestions * pointsPerQuestion;
};

const isBeforeStart = (test) => {
    if (!test.start_date) return false;
    return new Date() < new Date(test.start_date);
};

const isAfterEnd = (test) => {
    if (!test.end_date) return false;
    return new Date() > new Date(test.end_date);
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
