<template>
    <div class="profile-page">
        <header class="profile-navbar">
            <router-link to="/" class="profile-logo">
                GYM<span>WARRIORS</span>
            </router-link>

            <nav class="profile-nav">
                <router-link to="/profile">PROFILE</router-link>
                <a href="#stats">STATS</a>
                <a href="#membership">MEMBERSHIP</a>
            </nav>

            <button class="logout-button" @click="logout">
                LOG OUT
            </button>
        </header>

        <main class="profile-container">
            <section class="profile-header">
                <div class="profile-avatar">
                    {{ initials }}
                </div>

                <div class="profile-info">
                    <p class="profile-label">GYM WARRIOR</p>

                    <h1>
                        {{ user.first_name }} {{ user.last_name }}
                    </h1>

                    <p class="profile-email">
                        {{ user.email }}
                    </p>
                </div>
            </section>

            <section class="profile-section">
                <div class="section-title">
                    <span>01</span>
                    PERSONAL INFORMATION
                </div>

                <div class="profile-grid">
                    <div class="profile-field">
                        <label>FIRST NAME</label>
                        <p>{{ user.first_name }}</p>
                    </div>

                    <div class="profile-field">
                        <label>LAST NAME</label>
                        <p>{{ user.last_name }}</p>
                    </div>

                    <div class="profile-field">
                        <label>EMAIL</label>
                        <p>{{ user.email }}</p>
                    </div>

                    <div class="profile-field">
                        <label>PHONE</label>
                        <p>{{ user.phone || 'Not provided' }}</p>
                    </div>
                </div>
            </section>

            <section class="profile-section" id="stats">
                <div class="section-title">
                    <span>02</span>
                    WARRIOR STATS
                </div>

                <div class="stats-grid">
                    <div class="profile-stat">
                        <strong>01</strong>
                        <span>RANK</span>
                    </div>

                    <div class="profile-stat">
                        <strong>0</strong>
                        <span>WORKOUTS</span>
                    </div>

                    <div class="profile-stat">
                        <strong>0</strong>
                        <span>CHECK-INS</span>
                    </div>

                    <div class="profile-stat">
                        <strong>0</strong>
                        <span>FRIENDS</span>
                    </div>
                </div>
            </section>

            <section class="profile-section" id="membership">
                <div class="section-title">
                    <span>03</span>
                    MEMBERSHIP
                </div>

                <div v-if="loadingMembership" class="membership-card">
                    <div>
                        <p class="membership-label">MEMBERSHIP</p>
                        <h2>LOADING...</h2>
                    </div>
                </div>

                <div v-else-if="membership" class="membership-card">
                    <div>
                        <p class="membership-label">
                            CURRENT PLAN
                        </p>

                        <h2>
                            {{ membership.plan }}
                        </h2>

                        <p>
                            €{{ Number(membership.price).toFixed(2) }}
                            / 30 DAYS
                        </p>

                        <p>
                            {{ formatDate(membership.start_date) }}
                            —
                            {{ formatDate(membership.end_date) }}
                        </p>

                        <p>
                            TOKENS:
                            {{ membership.remaining_tokens }}
                            /
                            {{ membership.allocated_tokens }}
                        </p>

                        <p class="membership-status">
                            {{ membership.status.toUpperCase() }}
                        </p>
                    </div>
                    <div
    v-if="membership.locations && membership.locations.length"
    class="membership-locations"
>
    <p class="membership-locations-title">
        YOUR LOCATIONS
    </p>

    <div class="membership-location-list">
        <div
            v-for="location in membership.locations"
            :key="location.id"
            class="membership-location"
        >
            <div class="membership-location-number">
                {{ String(location.id).padStart(2, '0') }}
            </div>

            <div class="membership-location-info">
                <strong>{{ location.name }}</strong>
                <span>
                    {{ location.address }}, {{ location.city }}
                </span>
            </div>
        </div>
    </div>
</div>

<div
    v-else-if="membership.plan.toLowerCase() === 'pro'"
    class="membership-all-locations"
>
    <p class="membership-locations-title">
        LOCATION ACCESS
    </p>

    <strong>ALL GYM WARRIORS LOCATIONS</strong>

    <span>
        Your Pro membership gives you access to all active locations.
    </span>
</div>

                    <router-link
                        to="/plans"
                        class="membership-button"
                    >
                        CHANGE PLAN
                    </router-link>
                </div>

                <div v-else class="membership-card">
                    <div>
                        <p class="membership-label">
                            CURRENT PLAN
                        </p>

                        <h2>NO ACTIVE PLAN</h2>

                        <p>
                            Choose a membership plan to start your
                            Gym Warriors journey.
                        </p>
                    </div>

                    <router-link
                        to="/plans"
                        class="membership-button"
                    >
                        VIEW PLANS
                    </router-link>
                </div>
            </section>

            <section class="profile-section">
                <div class="section-title">
                    <span>04</span>
                    ACCOUNT
                </div>

                <div class="account-actions">
                    <button
                        class="account-button"
                        @click="logout"
                    >
                        LOG OUT
                    </button>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const user = reactive({
    id: null,
    first_name: '',
    last_name: '',
    email: '',
    phone: null,
});

const membership = ref(null);
const loadingMembership = ref(true);

const initials = computed(() => {
    const first = user.first_name?.charAt(0) || '';
    const last = user.last_name?.charAt(0) || '';

    return (first + last).toUpperCase();
});

const formatDate = (date) => {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString('lv-LV');
};

const loadProfile = async () => {
    try {
        const response = await fetch('/api/profile', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        if (!response.ok) {
            throw new Error('Failed to load profile.');
        }

        const data = await response.json();

        user.id = data.user.id;
        user.first_name = data.user.first_name;
        user.last_name = data.user.last_name;
        user.email = data.user.email;
        user.phone = data.user.phone;

        membership.value = data.membership;

        console.log('PROFILE DATA:', data);
        console.log('MEMBERSHIP:', data.membership);
        console.log('LOCATIONS:', data.membership?.locations);
    } catch (error) {
        console.error('PROFILE ERROR:', error);
    } finally {
        loadingMembership.value = false;
    }
};

const logout = async () => {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
        });
    } finally {
        router.push('/');
    }
};

onMounted(() => {
    loadProfile();
});
</script>