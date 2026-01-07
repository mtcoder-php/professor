<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Test Natijasi</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ result.test?.title }}</p>
                </div>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- User Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">O'qituvchi</p>
                            <p class="font-semibold text-gray-900">{{ result.user?.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Fakultet</p>
                            <p class="font-semibold text-gray-900">{{ result.user?.faculty?.name || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kafedra</p>
                            <p class="font-semibold text-gray-900">{{ result.user?.department?.name || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Ilmiy daraja</p>
                            <p class="font-semibold text-gray-900">{{ result.user?.scientific_degree || '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-blue-800 mb-1">Ball</p>
                        <p class="text-3xl font-bold text-blue-900">{{ result.score }}</p>
                        <p class="text-xs text-blue-700 mt-1">/ {{ result.test?.total_points }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-green-800 mb-1">Foiz</p>
                        <p class="text-3xl font-bold text-green-900">{{ result.percentage }}%</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-purple-800 mb-1">Holat</p>
                        <span :class="result.passed ? 'badge-success' : 'badge-danger'" class="badge text-lg">
              {{ result.passed ? 'O\'tdi' : 'O\'tmadi' }}
            </span>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-orange-800 mb-1">Vaqt</p>
                        <p class="text-sm font-bold text-orange-900">{{ calculateDuration() }}</p>
                    </div>
                </div>

                <!-- Test Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Test ma'lumotlari:</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Boshlanish:</p>
                            <p class="font-medium text-gray-900">{{ formatDateTime(result.started_at) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Tugash:</p>
                            <p class="font-medium text-gray-900">{{ formatDateTime(result.finished_at) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Savollar soni:</p>
                            <p class="font-medium text-gray-900">{{ result.test?.questions_count }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">O'tish bali:</p>
                            <p class="font-medium text-gray-900">{{ result.test?.pass_score }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Questions and Answers -->
                <div v-if="result.test?.questions" class="space-y-4">
                    <h3 class="font-semibold text-lg mb-4">Savollar va javoblar:</h3>

                    <div
                        v-for="(question, qIndex) in result.test.questions"
                        :key="question.id"
                        class="border border-gray-200 rounded-lg p-4"
                    >
                        <div class="flex items-start gap-3 mb-3">
              <span class="bg-gray-600 text-white text-sm font-bold px-3 py-1 rounded">
                {{ qIndex + 1 }}
              </span>
                            <p class="flex-1 font-medium text-gray-900">{{ question.question_text }}</p>
                        </div>

                        <div class="space-y-2 ml-11">
                            <div
                                v-for="answer in question.answers"
                                :key="answer.id"
                                class="flex items-center gap-2 p-2 rounded"
                                :class="{
                  'bg-green-50 border border-green-200': answer.is_correct && isAnswerSelected(question.id, answer.id),
                  'bg-red-50 border border-red-200': !answer.is_correct && isAnswerSelected(question.id, answer.id),
                  'bg-green-50': answer.is_correct && !isAnswerSelected(question.id, answer.id),
                }"
                            >
                <span class="text-sm">
                  <span v-if="answer.is_correct" class="text-green-600 font-bold">✓</span>
                  <span v-else-if="isAnswerSelected(question.id, answer.id)" class="text-red-600 font-bold">✗</span>
                  <span v-else class="text-gray-400">○</span>
                </span>
                                <span class="text-sm" :class="{
                  'font-semibold text-green-900': answer.is_correct,
                  'text-gray-700': !answer.is_correct
                }">
                  {{ answer.answer_text }}
                </span>
                                <span v-if="isAnswerSelected(question.id, answer.id)" class="ml-auto text-xs text-blue-600">
                  (Tanlangan)
                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4">
                <button @click="$emit('close')" class="btn-secondary w-full">
                    Yopish
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    result: {
        type: Object,
        required: true
    }
});

defineEmits(['close']);

const isAnswerSelected = (questionId, answerId) => {
    const userAnswer = props.result.answers?.find(a => a.question_id === questionId);
    return userAnswer?.answer_ids?.includes(answerId);
};

const calculateDuration = () => {
    if (!props.result.started_at || !props.result.finished_at) return '—';

    const start = new Date(props.result.started_at);
    const end = new Date(props.result.finished_at);
    const diff = Math.floor((end - start) / 1000 / 60); // minutes

    return `${diff} daqiqa`;
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString('uz-UZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
