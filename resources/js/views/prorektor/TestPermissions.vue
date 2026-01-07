<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Test Ruxsatlari</h1>
            <p class="text-gray-600 mt-1">O'qituvchilarga test ruxsatlarini boshqarish</p>
        </div>

        <!-- Filters & Bulk Actions -->
        <div class="card">
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Search -->
                <div>
                    <input
                        v-model="filters.search"
                        @input="searchTeachers"
                        type="text"
                        placeholder="O'qituvchi ismi..."
                        class="form-input w-full"
                    />
                </div>

                <!-- Faculty Filter -->
                <div>
                    <select v-model="filters.faculty_id" @change="onFacultyChange" class="form-input w-full">
                        <option value="">Barcha fakultetlar</option>
                        <template v-if="faculties && faculties.length > 0">
                            <option v-for="faculty in faculties" :key="faculty.id" :value="faculty.id">
                                {{ faculty.name }}
                            </option>
                        </template>
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <select v-model="filters.department_id" @change="onDepartmentChange" class="form-input w-full">
                        <option value="">Barcha kafedralar</option>
                        <template v-if="filteredDepartments && filteredDepartments.length > 0">
                            <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </template>
                    </select>
                </div>

                <!-- Test Selector -->
                <div>
                    <select v-model="selectedTestId" class="form-input w-full">
                        <option value="">Test tanlang</option>
                        <template v-if="tests && tests.length > 0">
                            <option v-for="test in tests" :key="test.id" :value="test.id">
                                {{ test.title }}
                            </option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Per Page -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <label class="text-sm text-gray-600">Sahifada ko'rsatish:</label>
                    <select v-model.number="filters.per_page" @change="onPerPageChange" class="form-input w-32">
                        <option :value="10">10 ta</option>
                        <option :value="25">25 ta</option>
                        <option :value="50">50 ta</option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    Jami: <span class="font-semibold">{{ pagination.total }}</span> ta o'qituvchi
                </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedTestId" class="border-t pt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Ommaviy operatsiyalar:</p>
                        <p class="text-xs text-gray-500">Tanlangan test: <strong>{{ getTestName(selectedTestId) }}</strong></p>
                    </div>
                    <button
                        v-if="selectedTeachers.length > 0"
                        @click="clearSelection"
                        class="text-sm text-gray-600 hover:text-gray-800"
                    >
                        Tanlovni tozalash
                    </button>
                </div>

                <!-- Grant Permissions -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="text-sm font-semibold text-green-800 mb-2">✓ Ruxsat berish:</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="selectedTeachers.length > 0"
                            @click="bulkGrantToSelected"
                            class="btn-primary text-sm"
                        >
                            Tanlanganlarga ({{ selectedTeachers.length }})
                        </button>

                        <button
                            @click="bulkGrantToCurrentPage"
                            class="btn-primary text-sm"
                        >
                            Sahifadagi barchaga ({{ teachers.length }})
                        </button>

                        <button
                            v-if="hasActiveFilters"
                            @click="bulkGrantToFiltered"
                            class="btn-primary text-sm"
                        >
                            Filtrlanganlarga ({{ pagination.total }})
                        </button>
                    </div>
                </div>

                <!-- Revoke Permissions -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm font-semibold text-red-800 mb-2">✗ Ruxsatni bekor qilish:</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="selectedTeachers.length > 0"
                            @click="bulkRevokeFromSelected"
                            class="btn-danger text-sm"
                        >
                            Tanlanganlardan ({{ selectedTeachers.length }})
                        </button>

                        <button
                            @click="bulkRevokeFromCurrentPage"
                            class="btn-danger text-sm"
                        >
                            Sahifadagi barchasidan ({{ teachers.length }})
                        </button>

                        <button
                            v-if="hasActiveFilters"
                            @click="bulkRevokeFromFiltered"
                            class="btn-danger text-sm"
                        >
                            Filtrlanganlardan ({{ pagination.total }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="text-gray-600 mt-4">Yuklanmoqda...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!teachers || teachers.length === 0" class="card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-gray-600 mt-4 text-lg font-semibold">O'qituvchilar topilmadi</p>
        </div>

        <!-- Teachers List -->
        <div v-else class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-4 py-3">
                            <input
                                type="checkbox"
                                @change="toggleSelectAll"
                                :checked="allSelected"
                                class="w-4 h-4"
                                title="Sahifadagi barchasini tanlash"
                            />
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">O'qituvchi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fakultet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kafedra</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Test Ruxsatlari</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    <tr v-for="teacher in teachers" :key="teacher.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                :value="teacher.id"
                                v-model="selectedTeachers"
                                class="w-4 h-4"
                            />
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ teacher.full_name }}</p>
                            <p class="text-xs text-gray-500">{{ teacher.scientific_degree }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ teacher.faculty?.name || '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ teacher.department?.name || '—' }}</td>
                        <td class="px-6 py-4">
                            <div v-if="tests && tests.length > 0" class="flex flex-wrap gap-2">
                                <template v-for="test in tests" :key="test.id">
                                    <button
                                        @click.prevent="handleToggleClick(teacher.id, test.id)"
                                        type="button"
                                        :class="[
                        hasPermission(teacher, test.id)
                          ? 'bg-green-100 text-green-800 border-green-300 hover:bg-green-200'
                          : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200',
                        'px-3 py-1.5 rounded-lg text-sm font-medium border-2 transition-all cursor-pointer'
                      ]"
                                    >
                                        <span v-if="hasPermission(teacher, test.id)">✓</span>
                                        <span v-else>○</span>
                                        {{ test.title }}
                                    </button>
                                </template>
                            </div>
                            <p v-else class="text-sm text-gray-500">
                                Testlar yuklanmoqda...
                            </p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination - MUKAMMAL ← -->
            <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Ko'rsatilmoqda: {{ pagination.from }} - {{ pagination.to }} / {{ pagination.total }}
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="goToPage(1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            «
                        </button>
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            Oldingi
                        </button>

                        <template v-for="page in getPageNumbers()" :key="page">
                            <button
                                v-if="page !== '...'"
                                @click="goToPage(page)"
                                :class="[
                  'px-4 py-2 border rounded-lg',
                  page === pagination.current_page
                    ? 'bg-green-600 text-white border-green-600'
                    : 'border-gray-300 hover:bg-gray-50'
                ]"
                            >
                                {{ page }}
                            </button>
                            <span v-else class="px-2 py-2">...</span>
                        </template>

                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            Keyingi
                        </button>
                        <button
                            @click="goToPage(pagination.last_page)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        >
                            »
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const teachers = ref([]);
const tests = ref([]);
const faculties = ref([]);
const departments = ref([]);
const selectedTestId = ref('');
const selectedTeachers = ref([]);

const filters = ref({
    search: '',
    faculty_id: '',
    department_id: '',
    page: 1,
    per_page: 15
});

const pagination = ref({
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0
});

const allSelected = computed(() => {
    return teachers.value && teachers.value.length > 0 && selectedTeachers.value.length === teachers.value.length;
});

const hasActiveFilters = computed(() => {
    return filters.value.search || filters.value.faculty_id || filters.value.department_id;
});

const filteredDepartments = computed(() => {
    if (!departments.value || departments.value.length === 0) {
        return [];
    }

    if (!filters.value.faculty_id) {
        return departments.value;
    }

    return departments.value.filter(d => d.faculty_id == filters.value.faculty_id);
});

const getTestName = (testId) => {
    if (!tests.value || tests.value.length === 0) return '';
    const test = tests.value.find(t => t.id == testId);
    return test ? test.title : '';
};

const onFacultyChange = () => {
    filters.value.department_id = '';
    filters.value.page = 1;
    loadTeachers();
};

const onDepartmentChange = () => {
    filters.value.page = 1;
    loadTeachers();
};

const onPerPageChange = () => {
    filters.value.page = 1;
    loadTeachers();
};

const loadTeachers = async () => {
    loading.value = true;
    try {
        const params = {
            page: filters.value.page,
            per_page: filters.value.per_page
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;

        const response = await axios.get('/api/prorektor/test-permissions', { params });

        if (response.data.success) {
            teachers.value = response.data.data.data || [];
            pagination.value = {
                total: response.data.data.total || 0,
                per_page: response.data.data.per_page || 15,
                current_page: response.data.data.current_page || 1,
                last_page: response.data.data.last_page || 1,
                from: response.data.data.from || 0,
                to: response.data.data.to || 0
            };
        }
    } catch (error) {
        console.error('Teachers load error:', error);
        teachers.value = [];
    } finally {
        loading.value = false;
    }
};

const loadTests = async () => {
    try {
        const response = await axios.get('/api/prorektor/tests');

        if (response.data.success) {
            tests.value = response.data.data || [];
        } else {
            tests.value = [];
        }
    } catch (error) {
        console.error('Tests load error:', error);
        tests.value = [];
    }
};

const loadFaculties = async () => {
    try {
        const response = await axios.get('/api/faculties');

        if (response.data.success) {
            faculties.value = response.data.data || [];
        } else if (Array.isArray(response.data)) {
            faculties.value = response.data;
        } else {
            faculties.value = [];
        }
    } catch (error) {
        console.error('Faculties load error:', error);
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
        } else {
            departments.value = [];
        }
    } catch (error) {
        console.error('Departments load error:', error);
        departments.value = [];
    }
};

let searchTimeout = null;
const searchTeachers = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filters.value.page = 1;
        loadTeachers();
    }, 500);
};

const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    loadTeachers();
};

const getPageNumbers = () => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3, 4, '...', last);
        } else if (current >= last - 2) {
            pages.push(1, '...', last - 3, last - 2, last - 1, last);
        } else {
            pages.push(1, '...', current - 1, current, current + 1, '...', last);
        }
    }

    return pages;
};

const hasPermission = (teacher, testId) => {
    if (!teacher || !teacher.test_permissions || !Array.isArray(teacher.test_permissions)) {
        return false;
    }
    return teacher.test_permissions.some(p => p && p.test_id === testId && p.is_allowed);
};

const handleToggleClick = (userId, testId) => {
    togglePermission(userId, testId);
};

const togglePermission = async (userId, testId) => {
    try {
        const teacher = teachers.value.find(t => t.id === userId);
        if (!teacher) return;

        const currentPermission = hasPermission(teacher, testId);
        const isAllowed = !currentPermission;

        const response = await axios.post('/api/prorektor/test-permissions/toggle', {
            user_id: userId,
            test_id: testId,
            is_allowed: isAllowed
        });

        if (response.data.success) {
            const teacherIndex = teachers.value.findIndex(t => t.id === userId);
            if (teacherIndex !== -1) {
                const teacher = teachers.value[teacherIndex];

                if (!teacher.test_permissions) {
                    teacher.test_permissions = [];
                }

                const permIndex = teacher.test_permissions.findIndex(p => p.test_id === testId);

                if (permIndex !== -1) {
                    teacher.test_permissions[permIndex].is_allowed = isAllowed;
                } else {
                    teacher.test_permissions.push({
                        test_id: testId,
                        is_allowed: isAllowed
                    });
                }

                teachers.value = [...teachers.value];
            }
        }
    } catch (error) {
        console.error('Toggle error:', error);
        alert('Ruxsat o\'zgartirishda xatolik');
    }
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedTeachers.value = [];
    } else {
        selectedTeachers.value = teachers.value.map(t => t.id);
    }
};

const clearSelection = () => {
    selectedTeachers.value = [];
};

const bulkGrantToSelected = async () => {
    if (!confirm(`${selectedTeachers.value.length} ta o'qituvchiga "${getTestName(selectedTestId.value)}" testiga ruxsat bermoqchimisiz?`)) return;
    await executeBulkOperation(selectedTeachers.value, true);
};

const bulkGrantToCurrentPage = async () => {
    const userIds = teachers.value.map(t => t.id);
    if (!confirm(`Hozirgi sahifadagi ${userIds.length} ta o'qituvchiga "${getTestName(selectedTestId.value)}" testiga ruxsat bermoqchimisiz?`)) return;
    await executeBulkOperation(userIds, true);
};

const bulkGrantToFiltered = async () => {
    if (!confirm(`Filtrlangan ${pagination.value.total} ta o'qituvchiga "${getTestName(selectedTestId.value)}" testiga ruxsat bermoqchimisiz?`)) return;
    const userIds = await getAllFilteredUserIds();
    await executeBulkOperation(userIds, true);
};

const bulkRevokeFromSelected = async () => {
    if (!confirm(`${selectedTeachers.value.length} ta o'qituvchidan "${getTestName(selectedTestId.value)}" testiga ruxsatni bekor qilmoqchimisiz?`)) return;
    await executeBulkOperation(selectedTeachers.value, false);
};

const bulkRevokeFromCurrentPage = async () => {
    const userIds = teachers.value.map(t => t.id);
    if (!confirm(`Hozirgi sahifadagi ${userIds.length} ta o'qituvchidan "${getTestName(selectedTestId.value)}" testiga ruxsatni bekor qilmoqchimisiz?`)) return;
    await executeBulkOperation(userIds, false);
};

const bulkRevokeFromFiltered = async () => {
    if (!confirm(`Filtrlangan ${pagination.value.total} ta o'qituvchidan "${getTestName(selectedTestId.value)}" testiga ruxsatni bekor qilmoqchimisiz?`)) return;
    const userIds = await getAllFilteredUserIds();
    await executeBulkOperation(userIds, false);
};

const getAllFilteredUserIds = async () => {
    try {
        const params = { per_page: 9999 };
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id;
        if (filters.value.department_id) params.department_id = filters.value.department_id;

        const response = await axios.get('/api/prorektor/test-permissions', { params });

        if (response.data.success) {
            return response.data.data.data.map(t => t.id) || [];
        }
        return [];
    } catch (error) {
        console.error('Get filtered error:', error);
        return [];
    }
};

const executeBulkOperation = async (userIds, isAllowed) => {
    if (!userIds || userIds.length === 0) {
        alert('O\'qituvchilar topilmadi');
        return;
    }

    try {
        const promises = userIds.map(userId =>
            axios.post('/api/prorektor/test-permissions/toggle', {
                user_id: userId,
                test_id: selectedTestId.value,
                is_allowed: isAllowed
            })
        );

        await Promise.all(promises);
        alert(`${userIds.length} ta o'qituvchiga operatsiya muvaffaqiyatli bajarildi!`);
        selectedTeachers.value = [];
        await loadTeachers();
    } catch (error) {
        console.error('Bulk operation error:', error);
        alert('Xatolik yuz berdi');
    }
};

onMounted(async () => {
    await loadTests();
    await loadFaculties();
    await loadDepartments();
    await loadTeachers();
});
</script>

<style scoped>
.btn-danger {
    @apply px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors;
}
</style>
