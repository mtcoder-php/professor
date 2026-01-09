<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ test.title }}</h2>
                    <div class="flex gap-2 mt-2">
                        <span :class="test.type === 'entry' ? 'badge-blue' : 'badge-purple'" class="badge">
                            {{ test.type === 'entry' ? 'Kiruvchi' : 'Chiquvchi' }}
                        </span>
                        <span :class="test.is_active ? 'badge-success' : 'badge-warning'" class="badge">
                            {{ test.is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-blue-600">{{ test.questions_count || 0 }}</p>
                    <p class="text-xs text-blue-800 mt-1">Jami savollar</p>
                </div>

                <!-- Har safar nechta -->
                <div v-if="test.questions_per_attempt" class="bg-indigo-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-indigo-600">{{ test.questions_per_attempt }}</p>
                    <p class="text-xs text-indigo-800 mt-1">Har safar</p>
                </div>

                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-green-600">{{ test.total_points || 0 }}</p>
                    <p class="text-xs text-green-800 mt-1">Jami ball</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-purple-600">{{ test.duration_minutes }}</p>
                    <p class="text-xs text-purple-800 mt-1">Daqiqa</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-orange-600">{{ test.pass_score }}%</p>
                    <p class="text-xs text-orange-800 mt-1">O'tish bali</p>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Har bir savol uchun ball</p>
                    <p class="text-base text-gray-900">{{ test.points_per_question }}</p>
                </div>

                <!-- Har safar ko'rsatiladigan savollar -->
                <div v-if="test.questions_per_attempt">
                    <p class="text-sm font-medium text-gray-500 mb-1">Har safar ko'rsatiladigan savollar</p>
                    <p class="text-base text-gray-900">
                        {{ test.questions_per_attempt }} ta
                        <span class="text-sm text-gray-500">({{ test.questions_count }} ta dan)</span>
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Yaratuvchi</p>
                    <p class="text-base text-gray-900">{{ test.creator?.full_name || 'Admin' }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Boshlanish sanasi</p>
                    <p class="text-base text-gray-900">{{ formatDate(test.start_date) || '—' }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Tugash sanasi</p>
                    <p class="text-base text-gray-900">{{ formatDate(test.end_date) || '—' }}</p>
                </div>
            </div>

            <!-- Settings -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Sozlamalar:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <svg :class="test.is_active ? 'text-green-600' : 'text-gray-400'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span :class="test.is_active ? 'text-gray-900' : 'text-gray-500'">Test faol</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg :class="test.allow_retake ? 'text-green-600' : 'text-gray-400'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span :class="test.allow_retake ? 'text-gray-900' : 'text-gray-500'">Qayta topshirish ruxsat etilgan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg :class="test.show_results ? 'text-green-600' : 'text-gray-400'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span :class="test.show_results ? 'text-gray-900' : 'text-gray-500'">Natijalarni ko'rsatish</span>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Yaratilgan:</p>
                        <p class="text-gray-900">{{ formatDateTime(test.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Yangilangan:</p>
                        <p class="text-gray-900">{{ formatDateTime(test.updated_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button @click="$emit('manage-questions')" class="btn-primary flex-1">
                    Savollarni boshqarish
                </button>
                <button @click="$emit('edit')" class="btn-secondary flex-1">
                    Tahrirlash
                </button>
                <button @click="$emit('close')" class="btn-secondary flex-1">
                    Yopish
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    test: {
        type: Object,
        required: true
    }
});

defineEmits(['close', 'edit', 'manage-questions']);

const formatDate = (date) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('uz-UZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
