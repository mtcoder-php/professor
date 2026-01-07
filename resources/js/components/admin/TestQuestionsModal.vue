<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold">{{ test.title }} - Savollar</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Jami: {{ questions.length }} ta savol | {{ test.total_points || 0 }} ball
                    </p>
                </div>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mb-6">
                <button @click="showAddQuestionForm = true" class="btn-primary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Qo'lda qo'shish
                </button>
                <button @click="showImportForm = true" class="btn-secondary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Fayldan import
                </button>
                <button @click="downloadTemplate" class="btn-secondary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Shablon
                </button>
            </div>

            <!-- Add Question Form -->
            <div v-if="showAddQuestionForm" class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold mb-4">Yangi savol qo'shish</h3>
                <form @submit.prevent="addQuestion" class="space-y-4">
                    <!-- Question Text -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Savol matni <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="newQuestion.question_text"
                            class="form-input w-full"
                            rows="3"
                            placeholder="Savol matnini kiriting..."
                            required
                        ></textarea>
                    </div>

                    <!-- Question Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Savol turi <span class="text-red-500">*</span>
                        </label>
                        <select v-model="newQuestion.question_type" class="form-input w-full" required>
                            <option value="single">Bitta to'g'ri javob</option>
                            <option value="multiple">Bir nechta to'g'ri javob</option>
                        </select>
                    </div>

                    <!-- Answers -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Javoblar (kamida 2 ta) <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <div v-for="(answer, index) in newQuestion.answers" :key="index" class="flex gap-2">
                                <input
                                    v-model="answer.text"
                                    type="text"
                                    class="form-input flex-1"
                                    :placeholder="`Javob ${index + 1}`"
                                    required
                                />
                                <label class="flex items-center gap-2 px-3 border border-gray-300 rounded-lg bg-white">
                                    <input
                                        v-model="answer.is_correct"
                                        type="checkbox"
                                        class="w-4 h-4 text-green-600"
                                    />
                                    <span class="text-sm">To'g'ri</span>
                                </label>
                                <button
                                    v-if="newQuestion.answers.length > 2"
                                    @click="removeAnswer(index)"
                                    type="button"
                                    class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addAnswer"
                            type="button"
                            class="mt-2 text-sm text-blue-600 hover:text-blue-800"
                        >
                            + Javob qo'shish
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="submitting" class="btn-primary disabled:opacity-50">
                            {{ submitting ? 'Qo\'shilmoqda...' : 'Qo\'shish' }}
                        </button>
                        <button @click="cancelAddQuestion" type="button" class="btn-secondary">
                            Bekor qilish
                        </button>
                    </div>
                </form>
            </div>

            <!-- Import Form -->
            <div v-if="showImportForm" class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold mb-4">Fayldan savollarni import qilish</h3>

                <!-- Format Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-blue-800 font-medium mb-2">Format qoidalari:</p>
                    <pre class="text-xs bg-white p-3 rounded border border-blue-200 overflow-x-auto">1.Savol matni?
- Javob varianti 1
- Javob varianti 2
# To'g'ri javob
- Javob varianti 4

2.Keyingi savol?
- Variant 1
# To'g'ri javob</pre>
                    <p class="text-xs text-blue-700 mt-2"><strong>Eslatma:</strong> # belgisi to'g'ri javobni bildiradi</p>
                </div>

                <!-- File Upload -->
                <div class="mb-4">
                    <input
                        @change="handleFileSelect"
                        type="file"
                        accept=".txt,.docx"
                        ref="fileInput"
                        class="form-input w-full"
                    />
                    <p class="text-xs text-gray-500 mt-1">Format: .txt yoki .docx, Max: 5MB</p>
                </div>

                <!-- Import Results -->
                <div v-if="importResults" class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold mb-3">Import natijalari:</h4>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ importResults.total }}</p>
                            <p class="text-xs text-gray-600">Jami</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ importResults.success }}</p>
                            <p class="text-xs text-gray-600">Muvaffaqiyatli</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-600">{{ importResults.failed }}</p>
                            <p class="text-xs text-gray-600">Xatolik</p>
                        </div>
                    </div>

                    <!-- Errors -->
                    <div v-if="importResults.errors && importResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded p-3 max-h-40 overflow-y-auto">
                        <p class="text-sm font-semibold text-red-900 mb-2">Xatoliklar:</p>
                        <div v-for="(error, index) in importResults.errors" :key="index" class="text-xs text-red-800 mb-1">
                            Savol {{ error.question_number }}: {{ error.error }}
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button
                        @click="importQuestions"
                        :disabled="!selectedFile || importing"
                        class="btn-primary disabled:opacity-50"
                    >
                        {{ importing ? 'Import qilinmoqda...' : 'Import qilish' }}
                    </button>
                    <button @click="cancelImport" type="button" class="btn-secondary">
                        Bekor qilish
                    </button>
                </div>
            </div>

            <!-- Questions List -->
            <div v-if="!showAddQuestionForm && !showImportForm">
                <!-- Loading -->
                <div v-if="loadingQuestions" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                    <p class="text-gray-600 mt-2">Yuklanmoqda...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="questions.length === 0" class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-600 mt-4 font-semibold">Hali savollar qo'shilmagan</p>
                    <p class="text-gray-500 text-sm mt-1">Yuqoridagi tugmalar orqali savol qo'shing</p>
                </div>

                <!-- Questions -->
                <div v-else class="space-y-4">
                    <div
                        v-for="(question, index) in questions"
                        :key="question.id"
                        class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                  <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded">
                    {{ index + 1 }}
                  </span>
                                    <span :class="question.question_type === 'single' ? 'badge-blue' : 'badge-purple'" class="badge text-xs">
                    {{ question.question_type === 'single' ? 'Bitta javob' : 'Ko\'p javob' }}
                  </span>
                                    <span class="badge badge-success text-xs">
                    {{ question.points }} ball
                  </span>
                                </div>
                                <p class="text-gray-900 font-medium">{{ question.question_text }}</p>
                            </div>
                            <button
                                @click="deleteQuestion(question.id)"
                                class="text-red-600 hover:text-red-800 ml-4"
                                title="O'chirish"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Answers -->
                        <div class="space-y-1 ml-8">
                            <div
                                v-for="(answer, aIndex) in question.answers"
                                :key="answer.id"
                                class="flex items-center gap-2 text-sm"
                            >
                <span
                    :class="answer.is_correct ? 'text-green-600 font-bold' : 'text-gray-400'"
                    class="w-5"
                >
                  {{ answer.is_correct ? '✓' : '○' }}
                </span>
                                <span :class="answer.is_correct ? 'text-green-900 font-medium' : 'text-gray-700'">
                  {{ answer.answer_text }}
                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    test: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'success']);

const loadingQuestions = ref(false);
const questions = ref([]);
const showAddQuestionForm = ref(false);
const showImportForm = ref(false);
const submitting = ref(false);
const importing = ref(false);
const selectedFile = ref(null);
const fileInput = ref(null);
const importResults = ref(null);

const newQuestion = ref({
    question_text: '',
    question_type: 'single',
    answers: [
        { text: '', is_correct: false },
        { text: '', is_correct: false }
    ]
});

const loadQuestions = async () => {
    loadingQuestions.value = true;
    try {
        const response = await axios.get(`/api/admin/tests/${props.test.id}/questions`);
        if (response.data.success) {
            questions.value = response.data.data;
        }
    } catch (error) {
        console.error('Savollarni yuklashda xatolik:', error);
    } finally {
        loadingQuestions.value = false;
    }
};

const addAnswer = () => {
    newQuestion.value.answers.push({ text: '', is_correct: false });
};

const removeAnswer = (index) => {
    newQuestion.value.answers.splice(index, 1);
};

const addQuestion = async () => {
    // Validate
    const hasCorrect = newQuestion.value.answers.some(a => a.is_correct);
    if (!hasCorrect) {
        alert('Kamida bitta to\'g\'ri javob belgilang!');
        return;
    }

    submitting.value = true;
    try {
        const response = await axios.post(`/api/admin/tests/${props.test.id}/questions`, newQuestion.value);
        if (response.data.success) {
            alert('Savol qo\'shildi');
            cancelAddQuestion();
            loadQuestions();
            emit('success');
        }
    } catch (error) {
        console.error('Savol qo\'shishda xatolik:', error);
        alert(error.response?.data?.message || 'Xatolik yuz berdi');
    } finally {
        submitting.value = false;
    }
};

const cancelAddQuestion = () => {
    showAddQuestionForm.value = false;
    newQuestion.value = {
        question_text: '',
        question_type: 'single',
        answers: [
            { text: '', is_correct: false },
            { text: '', is_correct: false }
        ]
    };
};

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0];
    importResults.value = null;
};

const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/admin/tests/template/download', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'test_questions_template.txt');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Shablon yuklashda xatolik:', error);
        alert('Shablon yuklashda xatolik yuz berdi');
    }
};

const importQuestions = async () => {
    if (!selectedFile.value) return;

    importing.value = true;
    importResults.value = null;

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        const response = await axios.post(
            `/api/admin/tests/${props.test.id}/import-questions`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        importResults.value = response.data.data;

        if (response.data.data.success > 0) {
            loadQuestions();
            emit('success');
        }
    } catch (error) {
        console.error('Import xatolik:', error);
        alert(error.response?.data?.message || 'Import jarayonida xatolik yuz berdi');
    } finally {
        importing.value = false;
    }
};

const cancelImport = () => {
    showImportForm.value = false;
    selectedFile.value = null;
    importResults.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const deleteQuestion = async (questionId) => {
    if (!confirm('Savolni o\'chirmoqchimisiz?')) return;

    try {
        const response = await axios.delete(`/api/admin/tests/${props.test.id}/questions/${questionId}`);
        if (response.data.success) {
            loadQuestions();
            emit('success');
            alert('Savol o\'chirildi');
        }
    } catch (error) {
        console.error('Savol o\'chirishda xatolik:', error);
        alert('Savol o\'chirishda xatolik yuz berdi');
    }
};

onMounted(() => {
    loadQuestions();
});
</script>
