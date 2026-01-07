<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="cancel"></div>

        <!-- Dialog -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all animate-fade-in">
                <!-- Icon -->
                <div class="flex items-center justify-center pt-8 pb-4">
                    <div class="p-4 bg-red-100 rounded-full">
                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 pb-6 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ title }}</h3>
                    <p class="text-gray-600">{{ message }}</p>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 px-6 pb-6">
                    <button
                        @click="cancel"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
                    >
                        {{ cancelText }}
                    </button>
                    <button
                        @click="confirm"
                        :class="confirmClass"
                        class="flex-1 px-6 py-3 rounded-xl text-white font-semibold transition-colors"
                    >
                        {{ confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Tasdiqlash'
    },
    message: {
        type: String,
        default: 'Ishonchingiz komilmi?'
    },
    confirmText: {
        type: String,
        default: 'Ha'
    },
    cancelText: {
        type: String,
        default: 'Yo\'q'
    },
    confirmClass: {
        type: String,
        default: 'bg-red-600 hover:bg-red-700'
    }
});

const emit = defineEmits(['confirm', 'cancel']);

const confirm = () => {
    emit('confirm');
};

const cancel = () => {
    emit('cancel');
};
</script>
