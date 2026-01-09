<template>
    <div class="space-y-6">
        <!-- Teacher Selector -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Faculty Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fakultet:</label>
                    <select v-model="filters.faculty_id" @change="onFacultyChange" class="form-input w-full">
                        <option value="">Barcha fakultetlar</option>
                        <option v-for="(faculty, index) in faculties"
                                :key="faculty?.id || index"
                                :value="faculty?.id">
                            {{ faculty?.name || 'Nomsiz fakultet' }}
                        </option>
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kafedra:</label>
                    <select v-model="filters.department_id" @change="loadTeachers" class="form-input w-full">
                        <option value="">Barcha kafedralar</option>
                        <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>

                <!-- Teacher Selector -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">O'qituvchi:</label>
                    <select v-model="selectedTeacherId" @change="loadTeacherReport" class="form-input w-full">
                        <option value="">O'qituvchi tanlang...</option>
                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                            {{ teacher.full_name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- No Teacher Selected -->
        <div v-else-if="!selectedTeacherId" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <p class="text-gray-600">O'qituvchini tanlang</p>
        </div>

        <!-- Teacher Report -->
        <template v-else-if="report">
            <!-- Teacher Info -->
            <div class="card">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        {{ getInitials(report.teacher?.full_name) }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold mb-2">{{ report.teacher?.full_name }}</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Fakultet</p>
                                <p class="font-semibold">{{ report.teacher?.faculty?.name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Kafedra</p>
                                <p class="font-semibold">{{ report.teacher?.department?.name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Ilmiy daraja</p>
                                <p class="font-semibold">{{ report.teacher?.scientific_degree || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Telefon</p>
                                <p class="font-semibold">{{ report.teacher?.phone || '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Stats -->
            <div class="card">
                <h3 class="text-xl font-bold mb-4">Test Natijalari</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-800">Jami Testlar</p>
                        <p class="text-3xl font-bold text-blue-900">{{ report.test_stats?.total || 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-800">O'tgan</p>
                        <p class="text-3xl font-bold text-green-900">{{ report.test_stats?.passed || 0 }}</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">O'rtacha Ball</p>
                        <p class="text-3xl font-bold text-yellow-900">{{ report.test_stats?.average_score?.toFixed(1) || 0 }}%</p>
                    </div>
                </div>

                <!-- Test Results List -->
                <div v-if="report.test_results && report.test_results.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Test</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ball</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Foiz</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sana</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="result in report.test_results" :key="result.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ result.test?.title }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ result.score }} / {{ result.test?.total_points }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ result.percentage }}%</td>
                            <td class="px-4 py-3">
                  <span :class="result.passed ? 'badge-success' : 'badge-danger'" class="badge">
                    {{ result.passed ? "O'tdi" : "O'tmadi" }}
                  </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(result.finished_at) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-center text-gray-500 py-8">Test natijalari yo'q</p>
            </div>

            <!-- Portfolio Stats -->
            <div class="card">
                <h3 class="text-xl font-bold mb-4">Portfolio</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-800">Jami Fayllar</p>
                        <p class="text-3xl font-bold text-blue-900">{{ report.portfolio_stats?.total || 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-800">Baholangan</p>
                        <p class="text-3xl font-bold text-green-900">{{ report.portfolio_stats?.evaluated || 0 }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-800">O'rtacha Ball</p>
                        <p class="text-3xl font-bold text-purple-900">{{ report.portfolio_stats?.average_score?.toFixed(1) || 0 }}</p>
                    </div>
                </div>

                <!-- Portfolio Files -->
                <div v-if="report.portfolio_files && report.portfolio_files.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategoriya</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fayl</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Holat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ball</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sana</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="file in report.portfolio_files" :key="file.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ file.category?.name }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ file.file_name }}</td>
                            <td class="px-4 py-3">
                  <span
                      :class="{
                      'badge-warning': file.status === 'pending',
                      'badge-success': file.status === 'evaluated',
                      'badge-danger': file.status === 'rejected'
                    }"
                      class="badge text-xs"
                  >
                    {{ getStatusText(file.status) }}
                  </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-green-600">
                                {{ file.evaluation?.score || '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(file.uploaded_at) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-center text-gray-500 py-8">Portfolio fayllari yo'q</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const faculties = ref([]);
const departments = ref([]);
const teachers = ref([]);
const selectedTeacherId = ref('');
const report = ref(null);

const filters = ref({
    faculty_id: '',
    department_id: ''
});

const filteredDepartments = computed(() => {
    if (!filters.value.faculty_id) return departments.value;
    return departments.value.filter(d => d.faculty_id == filters.value.faculty_id);
});

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties');
        console.log("Fakultetlar datasi:", response.data); // Konsolda tekshirish uchun

        let rawData = [];

        // Ma'lumot qayerda kelganini aniqlash (Struktura har xil bo'lishi mumkin)
        if (response.data.success && Array.isArray(response.data.data)) {
            rawData = response.data.data;
        } else if (Array.isArray(response.data)) {
            rawData = response.data;
        } else if (response.data.data && Array.isArray(response.data.data)) {
            rawData = response.data.data;
        }

        // Ma'lumotni tozalash va o'zlashtirish
        faculties.value = rawData.filter(f => f && (f.id || f.ID));

    } catch (error) {
        console.error('Fakultetlarni yuklashda xatolik:', error);
        faculties.value = [];
    }
};

const loadDepartments = async () => {
    try {
        const response = await axios.get('/api/departments/all');
        if (response.data.success) {
            departments.value = response.data.data || [];
        } else if (Array.isArray(response.data)) {
            departments.value = response.data;
        }
    } catch (error) {
        console.error('Departments load error:', error);
    }
};

const loadTeachers = async () => {
    try {
        const params = { per_page: 999 };
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;

        const response = await axios.get('/api/prorektor/test-permissions', { params });

        if (response.data.success) {
            teachers.value = response.data.data.data || [];
        }
    } catch (error) {
        console.error('Teachers load error:', error);
    }
};

const onFacultyChange = () => {
    filters.value.department_id = '';
    loadTeachers();
};

const loadTeacherReport = async () => {
    if (!selectedTeacherId.value) {
        report.value = null;
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(`/api/prorektor/reports/teacher/${selectedTeacherId.value}`);

        if (response.data.success) {
            report.value = response.data.data;
        }
    } catch (error) {
        console.error('Teacher report load error:', error);
    } finally {
        loading.value = false;
    }
};

const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.split(' ');
    return parts.length >= 2
        ? `${parts[0][0]}${parts[1][0]}`.toUpperCase()
        : name.substring(0, 2).toUpperCase();
};

const getStatusText = (status) => {
    const statuses = {
        'pending': 'Kutilmoqda',
        'evaluated': 'Baholangan',
        'rejected': 'Rad etilgan'
    };
    return statuses[status] || status;
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

onMounted(async () => {
    await loadFaculties();
    await loadDepartments();
    await loadTeachers();
});
</script>
