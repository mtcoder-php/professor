<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ department.name }}</h2>
                    <div class="flex gap-2 mt-2">
                        <span class="badge badge-success">{{ department.code }}</span>
                        <span :class="department.is_active ? 'badge-success' : 'badge-warning'" class="badge">
              {{ department.is_active ? 'Faol' : 'Nofaol' }}
            </span>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Faculty -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Fakultet</p>
                    <p class="text-base text-gray-900">{{ department.faculty?.name || '—' }}</p>
                </div>

                <!-- Head -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Kafedra mudiri</p>
                    <p class="text-base text-gray-900">{{ department.head_name || '—' }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Telefon</p>
                    <p class="text-base text-gray-900">{{ department.phone || '—' }}</p>
                </div>

                <!-- Email -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Email</p>
                    <p class="text-base text-gray-900">{{ department.email || '—' }}</p>
                </div>

                <!-- Room -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Xona raqami</p>
                    <p class="text-base text-gray-900">{{ department.room_number || '—' }}</p>
                </div>

                <!-- Users Count -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">O'qituvchilar soni</p>
                    <p class="text-base text-gray-900">{{ department.users_count || 0 }} ta</p>
                </div>
            </div>

            <!-- Users List (if available) -->
            <div v-if="department.users && department.users.length > 0" class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-3">O'qituvchilar:</p>
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div
                        v-for="user in department.users"
                        :key="user.id"
                        class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ user.full_name }}</p>
                            <p class="text-xs text-gray-500">{{ user.scientific_degree || 'O\'qituvchi' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Yaratilgan:</p>
                        <p class="text-gray-900">{{ formatDate(department.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Yangilangan:</p>
                        <p class="text-gray-900">{{ formatDate(department.updated_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button @click="$emit('edit')" class="btn-primary flex-1">
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
    department: {
        type: Object,
        required: true
    }
});

defineEmits(['close', 'edit']);

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
