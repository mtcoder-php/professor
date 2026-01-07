<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="close"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl transform transition-all animate-fade-in">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">{{ faculty.name }}</h3>
                            <p class="text-sm text-gray-500">Fakultet ma'lumotlari</p>
                        </div>
                    </div>
                    <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Status Badge -->
                    <div class="flex items-center gap-3">
            <span :class="faculty.is_active ? 'badge-success' : 'badge-warning'" class="badge">
              {{ faculty.is_active ? 'Faol' : 'Nofaol' }}
            </span>
                        <span class="badge badge-info">{{ faculty.code }}</span>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dekan -->
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Dekan</p>
                            <p class="text-lg font-medium text-gray-900">{{ faculty.dean_name || '—' }}</p>
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Telefon</p>
                            <p class="text-lg font-medium text-gray-900">{{ faculty.phone || '—' }}</p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Email</p>
                            <p class="text-lg font-medium text-gray-900">{{ faculty.email || '—' }}</p>
                        </div>

                        <!-- Kafedralar -->
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Kafedralar soni</p>
                            <p class="text-lg font-medium text-gray-900">{{ faculty.departments?.length || 0 }} ta</p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div v-if="faculty.address" class="space-y-2">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Manzil</p>
                        <p class="text-gray-900 p-4 bg-gray-50 rounded-xl">{{ faculty.address }}</p>
                    </div>

                    <!-- Departments List -->
                    <div v-if="faculty.departments && faculty.departments.length > 0" class="space-y-3">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Kafedralar</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div
                                v-for="dept in faculty.departments"
                                :key="dept.id"
                                class="p-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl border border-blue-100"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ dept.name }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ dept.code }}</p>
                                    </div>
                                    <span :class="dept.is_active ? 'badge-success' : 'badge-warning'" class="badge text-xs">
                    {{ dept.is_active ? 'Faol' : 'Nofaol' }}
                  </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-gray-500 uppercase">Yaratilgan</p>
                            <p class="text-sm text-gray-700">{{ formatDate(faculty.created_at) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-gray-500 uppercase">Yangilangan</p>
                            <p class="text-sm text-gray-700">{{ formatDate(faculty.updated_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    <button
                        @click="close"
                        class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-white transition-colors"
                    >
                        Yopish
                    </button>
                    <button
                        @click="editFaculty"
                        class="btn-primary px-6 py-3 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Tahrirlash</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    faculty: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'edit']);

const close = () => {
    emit('close');
};

const editFaculty = () => {
    emit('edit');
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
</script>
