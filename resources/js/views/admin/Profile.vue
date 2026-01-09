<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Profil</h1>
            <p class="text-gray-600 mt-1">Shaxsiy ma'lumotlarni boshqarish</p>
        </div>

        <!-- Profile Info Card -->
        <div class="card">
            <div class="flex items-start gap-6">
                <!-- Avatar -->
                <div class="relative">
                    <div class="w-32 h-32 rounded-full overflow-hidden bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center">
                        <img
                            v-if="profile.avatar"
                            :src="`/storage/${profile.avatar}`"
                            alt="Avatar"
                            class="w-full h-full object-cover"
                        />
                        <span v-else class="text-white text-4xl font-bold">
              {{ getUserInitials() }}
            </span>
                    </div>
                    <button @click="showUploadModal = true" class="absolute bottom-0 right-0 bg-green-600 text-white p-2 rounded-full hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Info -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900">{{ profile.full_name }}</h2>
                    <p class="text-gray-600 mt-1">Administrator</p>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium text-gray-900">{{ profile.email || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Telefon</p>
                            <p class="font-medium text-gray-900">{{ profile.phone || '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="card">
            <h3 class="text-xl font-bold mb-4">Ma'lumotlarni tahrirlash</h3>
            <form @submit.prevent="updateProfile" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">F.I.O *</label>
                        <input v-model="form.full_name" type="text" required class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input v-model="form.phone" type="text" class="form-input w-full" placeholder="+998901234567" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="saving" class="btn-primary">
                        <span v-if="saving">Saqlanmoqda...</span>
                        <span v-else>Saqlash</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card">
            <h3 class="text-xl font-bold mb-4">Parolni o'zgartirish</h3>
            <form @submit.prevent="changePassword" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Joriy parol *</label>
                        <input v-model="passwordForm.current_password" type="password" required class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Yangi parol *</label>
                        <input v-model="passwordForm.new_password" type="password" required minlength="6" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parolni tasdiqlash *</label>
                        <input v-model="passwordForm.new_password_confirmation" type="password" required class="form-input w-full" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="changingPassword" class="btn-secondary">
                        <span v-if="changingPassword">O'zgartirmoqda...</span>
                        <span v-else>Parolni o'zgartirish</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Avatar Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showUploadModal = false">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-4">Rasm yuklash</h3>
                <input type="file" @change="handleFileSelect" accept="image/*" class="w-full" />
                <div v-if="selectedFile" class="mt-4">
                    <img :src="previewUrl" class="w-full h-48 object-cover rounded-lg" />
                </div>
                <div class="flex gap-3 mt-4">
                    <button @click="uploadAvatar" :disabled="!selectedFile || uploading" class="btn-primary flex-1">
                        <span v-if="uploading">Yuklanmoqda...</span>
                        <span v-else>Yuklash</span>
                    </button>
                    <button @click="showUploadModal = false" class="btn-secondary flex-1">Bekor qilish</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const profile = ref({});
const saving = ref(false);
const changingPassword = ref(false);
const showUploadModal = ref(false);
const selectedFile = ref(null);
const previewUrl = ref('');
const uploading = ref(false);

const form = reactive({
    full_name: '',
    email: '',
    phone: ''
});

const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const loadProfile = async () => {
    try {
        const response = await axios.get('/api/admin/profile');

        if (response.data.success) {
            profile.value = response.data.data;
            form.full_name = profile.value.full_name;
            form.email = profile.value.email;
            form.phone = profile.value.phone;
        }
    } catch (error) {
        console.error('Load profile error:', error);
    }
};

const updateProfile = async () => {
    saving.value = true;
    try {
        const response = await axios.put('/api/admin/profile', form);

        if (response.data.success) {
            profile.value = response.data.data;
            alert('Profil muvaffaqiyatli yangilandi!');
        }
    } catch (error) {
        console.error('Update error:', error);
        alert('Xatolik yuz berdi!');
    } finally {
        saving.value = false;
    }
};

const changePassword = async () => {
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
        alert('Parollar mos kelmaydi!');
        return;
    }

    changingPassword.value = true;
    try {
        const response = await axios.post('/api/admin/profile/change-password', passwordForm);

        if (response.data.success) {
            alert('Parol muvaffaqiyatli o\'zgartirildi!');
            passwordForm.current_password = '';
            passwordForm.new_password = '';
            passwordForm.new_password_confirmation = '';
        }
    } catch (error) {
        console.error('Change password error:', error);
        alert(error.response?.data?.message || 'Xatolik yuz berdi!');
    } finally {
        changingPassword.value = false;
    }
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const uploadAvatar = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    try {
        const formData = new FormData();
        formData.append('avatar', selectedFile.value);
        formData.append('full_name', form.full_name);
        formData.append('email', form.email || '');
        formData.append('phone', form.phone || '');

        const response = await axios.post('/api/admin/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            method: 'PUT'
        });

        if (response.data.success) {
            profile.value = response.data.data;
            showUploadModal.value = false;
            selectedFile.value = null;
            previewUrl.value = '';
            alert('Rasm muvaffaqiyatli yuklandi!');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Xatolik yuz berdi!');
    } finally {
        uploading.value = false;
    }
};

const getUserInitials = () => {
    if (!profile.value.full_name) return 'A';
    const names = profile.value.full_name.split(' ');
    return names.length >= 2
        ? `${names[0][0]}${names[1][0]}`.toUpperCase()
        : names[0].substring(0, 2).toUpperCase();
};

onMounted(() => {
    loadProfile();
});
</script>
