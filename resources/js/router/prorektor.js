export default [
    {
        path: '/prorektor/dashboard',
        name: 'ProRektorDashboard',
        component: () => import('@/views/prorektor/Dashboard.vue'),
        meta: { requiresAuth: true, role: 'prorektor' }
    },
    {
        path: '/prorektor/test-permissions',
        name: 'ProRektorTestPermissions',
        component: () => import('@/views/prorektor/TestPermissions.vue'),
        meta: { requiresAuth: true, role: 'prorektor' }
    },
    {
        path: '/prorektor/test-results',
        name: 'ProRektorTestResults',
        component: () => import('@/views/prorektor/TestResults.vue'),
        meta: { requiresAuth: true, role: 'prorektor' }
    },
    {
        path: '/prorektor/portfolio-evaluations',
        name: 'ProRektorPortfolioEvaluations',
        component: () => import('@/views/prorektor/PortfolioEvaluations.vue'),
        meta: { requiresAuth: true, role: 'prorektor' }
    },
    {
        path: '/prorektor/portfolio-evaluations/teacher/:userId',
        name: 'ProRektorTeacherPortfolioEvaluation',
        component: () => import('@/views/prorektor/TeacherPortfolioEvaluation.vue'),
        meta: { requiresAuth: true, role: 'prorektor' }
    },
    // YANGI - Reports ←
    {
        path: '/prorektor/reports',
        name: 'ProRektorReports',
        component: () => import('@/views/prorektor/Reports.vue'),
        meta: { requiresAuth: true, role: 'prorektor' }
    }
];
