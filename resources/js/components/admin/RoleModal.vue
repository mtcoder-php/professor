<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
        <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">{{ role ? 'Rolni tahrirlash' : 'Yangi rol' }}</h2>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="saveRole" class="p-6 space-y-6">
                <!-- Role Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol nomi *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="form-input w-full"
                        placeholder="manager, moderator, ..."
                    />
                </div>

                <!-- Permissions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Huquqlar:</label>
                    <div class="space-y-4">
                        <div v-for="(perms, group) in permissions" :key="group" class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-3 capitalize">{{ group }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="permission in perms" :key="permission.id" class="flex items-center gap-2">
                                    <input
                                        v-model="form.permissions"
                                        type="checkbox"
                                        :value="permission.name"
                                        :id="`perm-${permission.id}`"
                                        class="w-4 h-4 text-green-600"
                                    />
                                    <label :for="`perm-${permission.id}`" class="text-sm text-gray-700 cursor-pointer">
                                        {{ formatPermission(permission.name) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" :disabled="saving" class="btn-primary flex-1">
                        <span v-if="saving">Saqlanmoqda...</span>
                        <span v-else>Saqlash</span>
                    </button>
                    <button type="button" @click="$emit('close')" class="btn-secondary flex-1">
                        Bekor qilish
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    role: {
        type: Object,
        default: null
    },
    permissions: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'save']);

const saving = ref(false);

const form = reactive({
    name: '',
    permissions: []
});

// Initialize form
if (props.role) {
    form.name = props.role.name;
    form.permissions = props.role.permissions?.map(p => p.name) || [];
}

const formatPermission = (name) => {
    return name.replace(/_/g, ' ');
};

const saveRole = async () => {
    saving.value = true;
    try {
        let response;

        if (props.role) {
            // Update
            response = await axios.put(`/api/admin/roles/${props.role.id}`, form);
        } else {
            // Create
            response = await axios.post('/api/admin/roles', form);
        }

        if (response.data.success) {
            alert(props.role ? 'Rol yangilandi!' : 'Rol yaratildi!');
            emit('save');
        }
    } catch (error) {
        console.error('Save role error:', error);
        alert(error.response?.data?.message || 'Xatolik yuz berdi!');
    } finally {
        saving.value = false;
    }
};
</script>
