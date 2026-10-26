<template>
    <div class="admin-page">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                GYM<span>WARRIORS</span>
                <small>ADMIN PANEL</small>
            </div>

            <nav class="admin-nav">
                <button
                    v-for="item in menuItems"
                    :key="item.id"
                    :class="{ active: activeSection === item.id }"
                    @click="activeSection = item.id"
                >
                    {{ item.label }}
                </button>
            </nav>

            <button class="admin-logout" @click="logout">
                LOG OUT
            </button>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <p class="admin-eyebrow">GYMWARRIORS MANAGEMENT</p>
                    <h1>{{ currentTitle }}</h1>
                </div>

                <div class="admin-account">
                    <span class="admin-status"></span>
                    Administrator
                </div>
            </header>

            <p v-if="error" class="admin-error">{{ error }}</p>

            <section v-if="activeSection === 'dashboard'">
                <div class="admin-welcome">
                    <h2>Welcome to the admin panel</h2>
                    <p>
                        Manage GymWarriors from one place.
                        Choose a section from the menu.
                    </p>
                </div>

                <div class="admin-cards">
                    <article class="admin-card">
                        <span>USERS</span>
                        <strong>{{ stats.users ?? '—' }}</strong>
                        <p>Registered accounts</p>
                    </article>

                    <article class="admin-card">
                        <span>PLANS</span>
                        <strong>{{ stats.plans ?? '—' }}</strong>
                        <p>Available membership plans</p>
                    </article>

                    <article class="admin-card">
                        <span>LOCATIONS</span>
                        <strong>{{ stats.locations ?? '—' }}</strong>
                        <p>Gym locations</p>
                    </article>
                </div>
            </section>

            <section v-else class="admin-placeholder">
                <h2>{{ currentTitle }}</h2>
                <p>
                    This section is ready for the next development step.
                </p>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import '../../css/admin.css';

const router = useRouter();

const activeSection = ref('dashboard');
const error = ref('');
const stats = ref({
    users: null,
    plans: null,
    locations: null,
});

const menuItems = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'users', label: 'Users' },
    { id: 'memberships', label: 'Memberships' },
    { id: 'locations', label: 'Locations' },
    { id: 'reports', label: 'Reports' },
];

const currentTitle = computed(() => {
    return menuItems.find(item => item.id === activeSection.value)?.label
        ?? 'Dashboard';
});

onMounted(async () => {
    try {
        const response = await fetch('/api/admin/dashboard', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.status === 401 || response.status === 403) {
            await router.replace('/login');
            return;
        }

        if (!response.ok) {
            throw new Error('Could not load administrator data.');
        }

        stats.value = await response.json();
    } catch (err) {
        error.value = 'Could not load dashboard data. Please refresh the page.';
    }
});

async function logout() {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
        });
    } finally {
        await router.replace('/login');
    }
}
</script>