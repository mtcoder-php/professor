<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Sozlamalar</h1>
            <p class="text-gray-600 mt-1">Tizim sozlamalarini boshqarish</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <template v-else>
            <!-- Tabs -->
            <div class="card">
                <div class="flex border-b border-gray-200">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
              'px-6 py-3 font-semibold transition-all',
              activeTab === tab.key
                ? 'text-green-600 border-b-2 border-green-600'
                : 'text-gray-600 hover:text-gray-900'
            ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- General Settings -->
            <div v-if="activeTab === 'general'" class="card">
                <h3 class="text-xl font-bold mb-4">Umumiy Sozlamalar</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tizim nomi</label>
                        <input v-model="settings.app_name" type="text" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tizim tavsifi</label>
                        <textarea v-model="settings.app_description" rows="3" class="form-input w-full"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Administrator Email</label>
                            <input v-model="settings.admin_email" type="email" class="form-input w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Qo'llab-quvvatlash telefoni</label>
                            <input v-model="settings.support_phone" type="text" class="form-input w-full" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Settings -->
            <div v-if="activeTab === 'test'" class="card">
                <h3 class="text-xl font-bold mb-4">Test Sozlamalari</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default test davomiyligi (daqiqa)</label>
                            <input v-model.number="settings.test_duration_default" type="number" min="10" max="180" class="form-input w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default o'tish bali (%)</label>
                            <input v-model.number="settings.test_pass_score" type="number" min="0" max="100" class="form-input w-full" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                        <input
                            v-model="settings.test_allow_retake"
                            type="checkbox"
                            :true-value="'true'"
                            :false-value="'false'"
                            class="w-5 h-5 text-green-600"
                        />
                        <div>
                            <p class="font-medium text-gray-900">Testni qayta topshirish</p>
                            <p class="text-sm text-gray-600">O'qituvchilarga testni qayta topshirish imkonini berish</p>
                        </div>
                    </div>

                    <div v-if="settings.test_allow_retake === 'true'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qayta topshirish uchun kutish vaqti (soat)</label>
                        <input v-model.number="settings.test_retake_delay" type="number" min="0" max="168" class="form-input w-full max-w-md" />
                    </div>
                </div>
            </div>

            <!-- Portfolio Settings -->
            <div v-if="activeTab === 'portfolio'" class="card">
                <h3 class="text-xl font-bold mb-4">Portfolio Sozlamalari</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal fayl hajmi (MB)</label>
                            <input v-model.number="settings.portfolio_max_file_size" type="number" min="1" max="20" class="form-input w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Baholash muddati (kun)</label>
                            <input v-model.number="settings.portfolio_evaluation_deadline" type="number" min="1" max="30" class="form-input w-full" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ruxsat etilgan fayl turlari</label>
                        <input v-model="settings.portfolio_allowed_types" type="text" class="form-input w-full" placeholder="pdf,doc,docx" />
                        <p class="text-xs text-gray-500 mt-1">Vergul bilan ajrating</p>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div v-if="activeTab === 'notification'" class="card">
                <h3 class="text-xl font-bold mb-4">Bildirishnoma Sozlamalari</h3>
                <div class="space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                            <input
                                v-model="settings.notification_email_enabled"
                                type="checkbox"
                                :true-value="'true'"
                                :false-value="'false'"
                                class="w-5 h-5 text-green-600"
                            />
                            <div>
                                <p class="font-medium text-gray-900">Email bildirishnomalar</p>
                                <p class="text-sm text-gray-600">Email orqali xabarlar yuborish</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                            <input
                                v-model="settings.notification_test_result"
                                type="checkbox"
                                :true-value="'true'"
                                :false-value="'false'"
                                class="w-5 h-5 text-green-600"
                            />
                            <div>
                                <p class="font-medium text-gray-900">Test natijalari</p>
                                <p class="text-sm text-gray-600">Test tugagandan keyin xabar yuborish</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                            <input
                                v-model="settings.notification_portfolio_evaluated"
                                type="checkbox"
                                :true-value="'true'"
                                :false-value="'false'"
                                class="w-5 h-5 text-green-600"
                            />
                            <div>
                                <p class="font-medium text-gray-900">Portfolio baholandi</p>
                                <p class="text-sm text-gray-600">Portfolio baholanganda xabar yuborish</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button @click="saveSettings" :disabled="saving" class="btn-primary">
                    <span v-if="saving">Saqlanmoqda...</span>
                    <span v-else>Saqlash</span>
                </button>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const saving = ref(false);
const activeTab = ref('general');

const tabs = [
    { key: 'general', label: 'Umumiy' },
    { key: 'test', label: 'Test' },
    { key: 'portfolio', label: 'Portfolio' },
    { key: 'notification', label: 'Bildirishnomalar' }
];

const settings = reactive({
    // General
    app_name: '',
    app_description: '',
    admin_email: '',
    support_phone: '',

    // Test
    test_duration_default: 60,
    test_pass_score: 60,
    test_allow_retake: 'true',
    test_retake_delay: 24,

    // Portfolio
    portfolio_max_file_size: 5,
    portfolio_allowed_types: 'pdf,doc,docx',
    portfolio_evaluation_deadline: 7,

    // Notification
    notification_email_enabled: 'true',
    notification_test_result: 'true',
    notification_portfolio_evaluated: 'true'
});

const loadSettings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/settings');

        if (response.data.success) {
            Object.assign(settings, response.data.data);
        }
    } catch (error) {
        console.error('Load settings error:', error);
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        const settingsArray = Object.entries(settings).map(([key, value]) => ({
            key,
            value: String(value)
        }));

        const response = await axios.put('/api/admin/settings', {
            settings: settingsArray
        });

        if (response.data.success) {
            alert('Sozlamalar muvaffaqiyatli saqlandi!');
        }
    } catch (error) {
        console.error('Save settings error:', error);
        alert('Xatolik yuz berdi!');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadSettings();
});
</script>
