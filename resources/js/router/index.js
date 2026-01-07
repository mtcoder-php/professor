import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Layouts
import AdminLayout from '@/layouts/AdminLayout.vue';
import ProRektorLayout from '@/layouts/ProRectorLayout.vue';
import TeacherLayout from '@/layouts/TeacherLayout.vue';

// Auth
import Login from '@/views/auth/Login.vue';

// Admin Views
import AdminDashboard from '@/views/admin/Dashboard.vue';
import Faculties from '@/views/admin/Faculties.vue';
import Departments from '@/views/admin/Departments.vue';
import Users from '@/views/admin/Users.vue';
import Tests from '@/views/admin/Tests.vue';

// ProRektor Views
import ProRektorDashboard from '@/views/prorektor/Dashboard.vue';
import TestPermissions from '@/views/prorektor/TestPermissions.vue';
import TestResults from '@/views/prorektor/TestResults.vue';
import PortfolioEvaluations from '@/views/prorektor/PortfolioEvaluations.vue';
import TeacherPortfolioEvaluation from '@/views/prorektor/TeacherPortfolioEvaluation.vue';
import Reports from '@/views/prorektor/Reports.vue'; // ← YANGI

// Teacher Views
import TeacherDashboard from '@/views/teacher/Dashboard.vue';
import TeacherProfile from '@/views/teacher/Profile.vue';
import TeacherTests from '@/views/teacher/Tests.vue';
import TeacherPortfolio from '@/views/teacher/Portfolio.vue';

const routes = [
    // Auth Routes
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },

    // Admin Routes
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, role: 'admin' },
        children: [
            {
                path: 'dashboard',
                name: 'AdminDashboard',
                component: AdminDashboard
            },
            {
                path: 'faculties',
                name: 'Faculties',
                component: Faculties
            },
            {
                path: 'departments',
                name: 'Departments',
                component: Departments
            },
            {
                path: 'users',
                name: 'Users',
                component: Users
            },
            {
                path: 'tests',
                name: 'Tests',
                component: Tests
            }
        ]
    },

    // ProRektor Routes
    {
        path: '/prorektor',
        component: ProRektorLayout,
        meta: { requiresAuth: true, role: 'prorektor' },
        children: [
            {
                path: 'dashboard',
                name: 'ProRektorDashboard',
                component: ProRektorDashboard
            },
            {
                path: 'test-permissions',
                name: 'TestPermissions',
                component: TestPermissions
            },
            {
                path: 'test-results',
                name: 'TestResults',
                component: TestResults
            },
            {
                path: 'portfolio-evaluations',
                name: 'PortfolioEvaluations',
                component: PortfolioEvaluations
            },
            {
                path: 'portfolio-evaluations/teacher/:userId',
                name: 'TeacherPortfolioEvaluation',
                component: TeacherPortfolioEvaluation
            },
            // Reports - YANGI ←
            {
                path: 'reports',
                name: 'Reports',
                component: Reports
            }
        ]
    },

    // Teacher Routes
    {
        path: '/teacher',
        component: TeacherLayout,
        meta: { requiresAuth: true, role: 'teacher' },
        children: [
            {
                path: 'dashboard',
                name: 'TeacherDashboard',
                component: TeacherDashboard
            },
            {
                path: 'profile',
                name: 'TeacherProfile',
                component: TeacherProfile
            },
            {
                path: 'tests',
                name: 'TeacherTests',
                component: TeacherTests
            },
            {
                path: 'portfolio',
                name: 'TeacherPortfolio',
                component: TeacherPortfolio
            }
        ]
    },

    // Default Redirect
    {
        path: '/',
        redirect: '/login'
    },

    // 404 - Catch all
    {
        path: '/:pathMatch(.*)*',
        redirect: '/login'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    if (authStore.token && !authStore.user) {
        try {
            await authStore.fetchUser();
        } catch (error) {
            authStore.token = null;
            localStorage.removeItem('token');
        }
    }

    const isAuthenticated = authStore.isAuthenticated;
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const isGuest = to.matched.some(record => record.meta.guest);

    if (isGuest && isAuthenticated) {
        const role = authStore.userRole || 'admin';
        return next(`/${role}/dashboard`);
    }

    if (requiresAuth && !isAuthenticated) {
        return next('/login');
    }

    next();
});

export default router;
