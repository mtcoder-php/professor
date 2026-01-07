<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ test.title }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ test.questions_count }} ta savol | {{ test.total_points }} ball
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Qolgan vaqt:</p>
                    <p class="text-3xl font-bold" :class="timeRemaining < 300 ? 'text-red-600' : 'text-green-600'">
                        {{ formatTime(timeRemaining) }}
                    </p>
                </div>
            </div>

            <!-- Questions -->
            <div class="p-6 space-y-6">
                <div
                    v-for="(question, qIndex) in test.questions"
                    :key="question.id"
                    class="border border-gray-200 rounded-lg p-4"
                >
                    <!-- Question Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                <span class="bg-green-600 text-white text-sm font-bold px-3 py-1 rounded">
                  {{ qIndex + 1 }}
                </span>
                                <span class="badge badge-info text-xs">
                  {{ question.points }} ball
                </span>
                                <span :class="question.question_type === 'single' ? 'badge-blue' : 'badge-purple'" class="badge text-xs">
                  {{ question.question_type === 'single' ? 'Bitta javob' : 'Ko\'p javob' }}
                </span>
                            </div>
                            <p class="text-lg font-medium text-gray-900">{{ question.question_text }}</p>
                        </div>
                    </div>

                    <!-- Answers -->
                    <div class="space-y-2 ml-4">
                        <label
                            v-for="answer in question.answers"
                            :key="answer.id"
                            class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                            :class="isAnswerSelected(question.id, answer.id) ? 'bg-green-50 border border-green-200' : 'border border-gray-200'"
                        >
                            <input
                                v-if="question.question_type === 'single'"
                                type="radio"
                                :name="`question_${question.id}`"
                                :value="answer.id"
                                @change="selectAnswer(question.id, answer.id, 'single')"
                                class="mt-1"
                            />
                            <input
                                v-else
                                type="checkbox"
                                :value="answer.id"
                                :checked="isAnswerSelected(question.id, answer.id)"
                                @change="selectAnswer(question.id, answer.id, 'multiple')"
                                class="mt-1"
                            />
                            <span class="flex-1 text-gray-900">{{ answer.answer_text }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex gap-3">
                <button
                    @click="submitTest"
                    :disabled="submitting || !allQuestionsAnswered"
                    class="btn-primary flex-1 disabled:opacity-50"
                >
                    {{ submitting ? 'Yuborilmoqda...' : 'Testni yakunlash' }}
                </button>
                <button
                    @click="confirmClose"
                    :disabled="submitting"
                    class="btn-secondary"
                >
                    Bekor qilish
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    test: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'success']);

const answers = ref({});
const startedAt = ref(null);
const timeRemaining = ref(0);
const submitting = ref(false);
let timer = null;

const initializeAnswers = () => {
    props.test.questions.forEach(question => {
        answers.value[question.id] = [];
    });
};

const allQuestionsAnswered = computed(() => {
    return props.test.questions.every(question => {
        return answers.value[question.id] && answers.value[question.id].length > 0;
    });
});

const isAnswerSelected = (questionId, answerId) => {
    return answers.value[questionId]?.includes(answerId);
};

const selectAnswer = (questionId, answerId, type) => {
    if (type === 'single') {
        answers.value[questionId] = [answerId];
    } else {
        if (!answers.value[questionId]) {
            answers.value[questionId] = [];
        }

        const index = answers.value[questionId].indexOf(answerId);
        if (index > -1) {
            answers.value[questionId].splice(index, 1);
        } else {
            answers.value[questionId].push(answerId);
        }
    }
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const startTimer = () => {
    startedAt.value = new Date();
    timeRemaining.value = props.test.duration_minutes * 60;

    timer = setInterval(() => {
        timeRemaining.value--;

        if (timeRemaining.value <= 0) {
            clearInterval(timer);
            submitTest(true);
        }
    }, 1000);
};

const submitTest = async (autoSubmit = false) => {
    if (!autoSubmit && !confirm('Testni yakunlamoqchimisiz?')) return;

    submitting.value = true;

    try {
        const payload = {
            started_at: startedAt.value.toISOString(),
            answers: Object.entries(answers.value).map(([questionId, answerIds]) => ({
                question_id: parseInt(questionId),
                answer_ids: answerIds
            }))
        };

        const response = await axios.post(`/api/teacher/tests/${props.test.id}/submit`, payload);

        if (response.data.success) {
            const result = response.data.data.result;

            if (props.test.show_results) {
                alert(
                    `Test yakunlandi!\n\n` +
                    `Ball: ${result.score} / ${props.test.total_points}\n` +
                    `Foiz: ${result.percentage}%\n` +
                    `Natija: ${result.passed ? 'O\'tdingiz ✓' : 'O\'tmadingiz ✗'}`
                );
            } else {
                alert('Test yakunlandi! Natijalar keyinroq e\'lon qilinadi.');
            }

            emit('success');
        }
    } catch (error) {
        console.error('Test yuborishda xatolik:', error);
        alert(error.response?.data?.message || 'Test yuborishda xatolik yuz berdi');
    } finally {
        submitting.value = false;
    }
};

const confirmClose = () => {
    if (confirm('Testni yakunlamasdan chiqmoqchimisiz? Barcha javoblar yo\'qoladi!')) {
        emit('close');
    }
};

onMounted(() => {
    initializeAnswers();
    startTimer();
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>
