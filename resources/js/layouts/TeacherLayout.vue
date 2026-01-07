<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed top-0 left-0 z-40 w-72 h-screen transition-transform lg:translate-x-0 bg-gradient-to-b from-purple-600 to-pink-700 shadow-2xl"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between p-6 border-b border-white/20">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white rounded-xl">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Professor</h2>
                        <p class="text-purple-200 text-xs">O'qituvchi Panel</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-180px)]">
                <!-- Dashboard -->
                <router-link to="/teacher/dashboard" class="sidebar-link" active-class="sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </router-link>

                <!-- Profile -->
                <router-link to="/teacher/profile" class="sidebar-link" active-class="sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Profil</span>
                </router-link>

                <!-- Tests -->
                <router-link to="/teacher/tests" class="sidebar-link" active-class="sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Testlar</span>
                </router-link>

                <!-- Portfolio -->
                <router-link to="/teacher/portfolio" class="sidebar-link" active-class="sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Portfolio</span>
                </router-link>
            </nav>

            <!-- User Info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-black/20 border-t border-white/20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <span class="text-purple-600 font-bold text-sm">{{ userInitials }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-semibold text-sm truncate">{{ user?.full_name }}</p>
                        <p class="text-purple-200 text-xs">O'qituvchi</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:ml-72">
            <!-- Top Navbar -->
            <nav class="bg-white shadow-md sticky top-0 z-30">
                <div class="px-4 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <div class="flex-1"></div>

                        <div class="flex items-center space-x-4">
                            <div class="relative" v-click-outside="closeDropdown">
                                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-xl">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ userInitials }}</span>
                                    </div>
                                    <div class="hidden lg:block text-left">
                                        <p class="text-sm font-semibold text-gray-700">{{ user?.full_name }}</p>
                                        <p class="text-xs text-gray-500">O'qituvchi</p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                    <div v-if="dropdownOpen" class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                                        <div class="px-4 py-3 border-b border-gray-100">
                                            <p class="text-sm font-semibold text-gray-700">{{ user?.full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ user?.email || '—' }}</p>
                                        </div>

                                        <!-- Profile Link -->
                                        <router-link to="/teacher/profile" class="dropdown-item">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span>Profil</span>
                                        </router-link>

                                        <!-- Logout -->
                                        <button @click="handleLogout" class="dropdown-item text-red-600 hover:bg-red-50 w-full">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>Chiqish</span>
                                        </button>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="p-4 lg:p-8">
                <router-view />
            </main>
        </div>

        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const user = computed(() => authStore.user);
const sidebarOpen = ref(false);
const dropdownOpen = ref(false);

const userInitials = computed(() => {
    if (!user.value?.full_name) return 'OQ';
    const names = user.value.full_name.split(' ');
    return names.length >= 2
        ? `${names[0][0]}${names[1][0]}`.toUpperCase()
        : names[0].substring(0, 2).toUpperCase();
});

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};

const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    },
};
</script>

<style scoped>
.sidebar-link {
    @apply flex items-center space-x-3 px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200;
}

.sidebar-link-active {
    @apply text-white bg-white/20 shadow-lg;
}

.dropdown-item {
    @apply flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer;
}
</style>
