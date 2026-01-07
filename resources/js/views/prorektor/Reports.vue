<template>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gradient">Hisobotlar</h1>
            <p class="text-gray-600 mt-1">Umumiy statistika va hisobotlar</p>
        </div>

        <!-- Report Type Selector -->
        <div class="card">
            <div class="flex gap-4">
                <button
                    @click="reportType = 'overall'"
                    :class="[
            'px-6 py-3 rounded-lg font-semibold transition-all',
            reportType === 'overall'
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
                >
                    Umumiy Statistika
                </button>
                <button
                    @click="reportType = 'faculty'"
                    :class="[
            'px-6 py-3 rounded-lg font-semibold transition-all',
            reportType === 'faculty'
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
                >
                    Fakultet bo'yicha
                </button>
                <button
                    @click="reportType = 'teacher'"
                    :class="[
            'px-6 py-3 rounded-lg font-semibold transition-all',
            reportType === 'teacher'
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
                >
                    O'qituvchi bo'yicha
                </button>
            </div>
        </div>

        <!-- Overall Report -->
        <div v-if="reportType === 'overall'">
            <OverallReport />
        </div>

        <!-- Faculty Report -->
        <div v-else-if="reportType === 'faculty'">
            <FacultyReport />
        </div>

        <!-- Teacher Report -->
        <div v-else-if="reportType === 'teacher'">
            <TeacherReport />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import OverallReport from '@/components/prorektor/reports/OverallReport.vue';
import FacultyReport from '@/components/prorektor/reports/FacultyReport.vue';
import TeacherReport from '@/components/prorektor/reports/TeacherReport.vue';

const reportType = ref('overall');
</script>
