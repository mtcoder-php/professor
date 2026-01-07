<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Excel orqali import</h2>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">📋 Ko'rsatma:</h3>
                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                    <li>Shablonni yuklab oling</li>
                    <li>Excel faylni to'ldiring (barcha maydonlar majburiy)</li>
                    <li>Faylni yuklang va import qiling</li>
                </ol>
            </div>

            <!-- Template Format -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">📊 Excel format:</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead class="bg-gray-200">
                        <tr>
                            <th class="border border-gray-300 px-2 py-1">FIO</th>
                            <th class="border border-gray-300 px-2 py-1">Kafedra kodi</th>
                            <th class="border border-gray-300 px-2 py-1">Ilmiy darajasi</th>
                            <th class="border border-gray-300 px-2 py-1">Passport seriya</th>
                            <th class="border border-gray-300 px-2 py-1">Tugilgan kun</th>
                            <th class="border border-gray-300 px-2 py-1">Telefon</th>
                            <th class="border border-gray-300 px-2 py-1">Email</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="border border-gray-300 px-2 py-1">Ahmadov Jamshid</td>
                            <td class="border border-gray-300 px-2 py-1">TK</td>
                            <td class="border border-gray-300 px-2 py-1">PhD</td>
                            <td class="border border-gray-300 px-2 py-1">AB1234567</td>
                            <td class="border border-gray-300 px-2 py-1">15.03.1985</td>
                            <td class="border border-gray-300 px-2 py-1">998901234567</td>
                            <td class="border border-gray-300 px-2 py-1">ahmadov@uz</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 space-y-1 text-xs text-gray-600">
                    <p><strong>Format qoidalari:</strong></p>
                    <ul class="list-disc list-inside ml-2">
                        <li>Passport: 2 ta harf + 7 ta raqam (AB1234567)</li>
                        <li>Tug'ilgan kun: dd.mm.yyyy (15.03.1985)</li>
                        <li>Telefon: 998XXXXXXXXX</li>
                        <li>Kafedra kodi: Mavjud kafedra kodi (TK, MTK, va h.k.)</li>
                    </ul>
                </div>
            </div>

            <!-- Download Template Button -->
            <div class="mb-6">
                <button @click="downloadTemplate" :disabled="downloading" class="btn-secondary w-full flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ downloading ? 'Yuklanmoqda...' : 'Shablon yuklab olish' }}
                </button>
            </div>

            <!-- File Upload -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Excel faylni yuklang <span class="text-red-500">*</span>
                </label>
                <input
                    @change="handleFileSelect"
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    ref="fileInput"
                    class="form-input w-full"
                />
                <p class="text-xs text-gray-500 mt-1">Max: 5MB, Format: XLSX, XLS, CSV</p>

                <!-- Selected File Info -->
                <div v-if="selectedFile" class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-green-900">{{ selectedFile.name }}</p>
                                <p class="text-xs text-green-700">{{ formatFileSize(selectedFile.size) }}</p>
                            </div>
                        </div>
                        <button @click="clearFile" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Import Results -->
            <div v-if="importResults" class="mb-6">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Import natijalari:</h3>

                    <!-- Summary -->
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

                    <!-- Errors List -->
                    <div v-if="importResults.errors && importResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-3 max-h-60 overflow-y-auto">
                        <h4 class="font-semibold text-red-900 mb-2">Xatoliklar:</h4>
                        <div v-for="(error, index) in importResults.errors" :key="index" class="mb-3 last:mb-0">
                            <p class="text-sm font-medium text-red-800">Qator {{ error.row }}: {{ error.fio }}</p>
                            <ul class="list-disc list-inside text-xs text-red-700 ml-2">
                                <li v-for="(err, idx) in error.errors" :key="idx">{{ err }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div v-if="importResults.failed === 0" class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
                        <p class="text-green-800 font-medium text-center">
                            ✅ Barcha ma'lumotlar muvaffaqiyatli import qilindi!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div v-if="importing" class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-center gap-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <p class="text-blue-800 font-medium">Import qilinmoqda...</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button
                    @click="importFile"
                    :disabled="!selectedFile || importing"
                    class="btn-primary flex-1 disabled:opacity-50"
                >
                    {{ importing ? 'Import qilinmoqda...' : 'Import qilish' }}
                </button>
                <button
                    @click="$emit('close')"
                    class="btn-secondary flex-1"
                    :disabled="importing"
                >
                    {{ importResults && importResults.success > 0 ? 'Yopish' : 'Bekor qilish' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'success']);

const selectedFile = ref(null);
const fileInput = ref(null);
const downloading = ref(false);
const importing = ref(false);
const importResults = ref(null);

const downloadTemplate = async () => {
    downloading.value = true;
    try {
        const response = await axios.get('/api/admin/users/template/download', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'users_template.csv');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Template download error:', error);
        alert('Shablonni yuklashda xatolik yuz berdi');
    } finally {
        downloading.value = false;
    }
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Fayl hajmi 5MB dan katta bo\'lmasligi kerak');
            event.target.value = '';
            return;
        }

        // Validate file type
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'text/csv' // .csv
        ];

        if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
            alert('Faqat XLSX, XLS va CSV formatdagi fayllar qabul qilinadi');
            event.target.value = '';
            return;
        }

        selectedFile.value = file;
        importResults.value = null; // Clear previous results
    }
};

const clearFile = () => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    importResults.value = null;
};

const importFile = async () => {
    if (!selectedFile.value) return;

    importing.value = true;
    importResults.value = null;

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        const response = await axios.post('/api/admin/users/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        importResults.value = response.data.data;

        if (response.data.data.success > 0) {
            emit('success');
        }

        if (response.data.data.failed === 0) {
            // Auto close after 2 seconds if all success
            setTimeout(() => {
                emit('close');
            }, 2000);
        }
    } catch (error) {
        console.error('Import error:', error);
        alert(error.response?.data?.message || 'Import jarayonida xatolik yuz berdi');
    } finally {
        importing.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
