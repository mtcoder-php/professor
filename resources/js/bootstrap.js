import axios from 'axios';

window.axios = axios;

// CRITICAL: Accept header
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// CSRF token
const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Base URL
window.axios.defaults.baseURL = 'http://localhost:8000';

// With credentials for CORS
window.axios.defaults.withCredentials = true;

// Response interceptor
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            delete window.axios.defaults.headers.common['Authorization'];
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);
