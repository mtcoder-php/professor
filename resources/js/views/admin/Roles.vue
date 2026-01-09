<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gradient">Rollar va Huquqlar</h1>
                <p class="text-gray-600 mt-1">Foydalanuvchi rollari va huquqlarini boshqarish</p>
            </div>
            <button @click="showCreateModal = true" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Yangi Rol
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Roles List -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="role in roles" :key="role.id" class="card hover:shadow-xl transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 capitalize">{{ role.name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ role.permissions?.length || 0 }} ta huquq</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editRole(role)" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button
                            v-if="!['admin', 'prorektor', 'teacher'].includes(role.name)"
                            @click="deleteRole(role)"
                            class="text-red-600 hover:text-red-800"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Permissions Badge -->
                <div class="flex flex-wrap gap-2">
          <span
              v-for="permission in role.permissions?.slice(0, 6)"
              :key="permission.id"
              class="badge badge-info text-xs"
          >
            {{ formatPermission(permission.name) }}
          </span>
                    <span v-if="role.permissions?.length > 6" class="badge badge-secondary text-xs">
            +{{ role.permissions.length - 6 }}
          </span>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <RoleModal
            v-if="showCreateModal || showEditModal"
            :role="selectedRole"
            :permissions="permissions"
            @close="closeModal"
            @save="handleSave"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import RoleModal from '@/components/admin/RoleModal.vue';

const loading = ref(false);
const roles = ref([]);
const permissions = ref({});
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedRole = ref(null);

const loadRoles = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/roles');

        if (response.data.success) {
            roles.value = response.data.data;
        }
    } catch (error) {
        console.error('Load roles error:', error);
    } finally {
        loading.value = false;
    }
};

const loadPermissions = async () => {
    try {
        const response = await axios.get('/api/admin/permissions');

        if (response.data.success) {
            permissions.value = response.data.data;
        }
    } catch (error) {
        console.error('Load permissions error:', error);
    }
};

const editRole = (role) => {
    selectedRole.value = { ...role };
    showEditModal.value = true;
};

const deleteRole = async (role) => {
    if (!confirm(`"${role.name}" rolini o'chirishni tasdiqlaysizmi?`)) return;

    try {
        const response = await axios.delete(`/api/admin/roles/${role.id}`);

        if (response.data.success) {
            alert('Rol muvaffaqiyatli o\'chirildi!');
            await loadRoles();
        }
    } catch (error) {
        console.error('Delete role error:', error);
        alert(error.response?.data?.message || 'Xatolik yuz berdi!');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    selectedRole.value = null;
};

const handleSave = async () => {
    closeModal();
    await loadRoles();
};

const formatPermission = (name) => {
    return name.replace(/_/g, ' ');
};

onMounted(async () => {
    await loadPermissions();
    await loadRoles();
});
</script>
