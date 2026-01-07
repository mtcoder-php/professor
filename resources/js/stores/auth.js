import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        userRole: (state) => {
            if (!state.user || !state.user.roles || state.user.roles.length === 0) {
                return null;
            }
            return state.user.roles[0].name.toLowerCase();
        },
    },

    actions: {
        async login(credentials) {
            try {
                const response = await axios.post('/api/login', {
                    passport_series: credentials.passport_series,
                    password: credentials.password,
                });

                this.user = response.data.user;
                this.token = response.data.token;

                localStorage.setItem('token', response.data.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;

                // Role-based redirect ✅
                const role = this.userRole || 'admin';

                console.log('Login successful, role:', role);

                // Redirect qilish
                if (role === 'admin') {
                    router.push('/admin/dashboard');
                } else if (role === 'prorektor') {
                    router.push('/prorektor/dashboard');
                } else if (role === 'teacher') {
                    router.push('/teacher/dashboard');
                } else {
                    // Default - admin
                    router.push('/admin/dashboard');
                }

                return response.data;
            } catch (error) {
                console.error('Login error:', error);
                throw error;
            }
        },

        async fetchUser() {
            if (!this.token) return null;

            try {
                const response = await axios.get('/api/user');
                this.user = response.data.user;
                return response.data.user;
            } catch (error) {
                console.error('Fetch user error:', error);
                if (error.response?.status === 401) {
                    this.logout();
                }
                throw error;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post('/api/logout');
                }
            } catch (error) {
                console.error('Logout API error:', error);
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
                router.push('/login');
            }
        },
    },
});
