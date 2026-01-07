<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <img
                            v-if="user.avatar"
                            :src="`/storage/${user.avatar}`"
                            :alt="user.full_name"
                            class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
                        />
                        <div v-else class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
              <span class="text-green-600 font-bold text-2xl">
                {{ user.full_name.charAt(0) }}
              </span>
                        </div>
                    </div>

                    <!-- Name & Role -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ user.full_name }}</h2>
                        <div class="flex gap-2 mt-2">
                            <span class="badge badge-info">{{ user.passport_series }}</span>
                            <span
                                :class="{
                  'badge-purple': user.roles?.[0]?.name === 'admin',
                  'badge-blue': user.roles?.[0]?.name === 'prorektor',
                  'badge-green': user.roles?.[0]?.name === 'teacher'
                }"
                                class="badge"
                            >
                {{ user.roles?.[0]?.name || 'teacher' }}
              </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Faculty -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Fakultet</p>
                    <p class="text-base text-gray-900">{{ user.faculty?.name || '—' }}</p>
                </div>

                <!-- Department -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Kafedra</p>
                    <p class="text-base text-gray-900">{{ user.department?.name || '—' }}</p>
                </div>

                <!-- Scientific Degree -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Ilmiy daraja</p>
                    <p class="text-base text-gray-900">{{ user.scientific_degree || '—' }}</p>
                </div>

                <!-- Birth Date -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Tug'ilgan kun</p>
                    <p class="text-base text-gray-900">{{ formatDate(user.birth_date) }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Telefon</p>
                    <p class="text-base text-gray-900">{{ user.phone || '—' }}</p>
                </div>

                <!-- Email -->
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Email</p>
                    <p class="text-base text-gray-900">{{ user.email || '—' }}</p>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Yaratilgan:</p>
                        <p class="text-gray-900">{{ formatDateTime(user.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Yangilangan:</p>
                        <p class="text-gray-900">{{ formatDateTime(user.updated_at) }}</p>
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
    user: {
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
        day: 'numeric'
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
