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
                <a href="#friends">FRIENDS</a>
                <router-link to="/calories">CALORIE TRACKING</router-link>
                <router-link to="/workout">WORKOUTS AND NUTRITION</router-link>
            </nav>
                
<button
    type="button"
    class="page-mobile-burger"
    @click="isMenuOpen = !isMenuOpen"
    :aria-expanded="isMenuOpen"
    aria-label="Open navigation menu"
>
    <span></span>
    <span></span>
    <span></span>
</button>

            

            <button class="logout-button" @click="logout">
                LOG OUT
            </button>
        </header>

        
<div v-if="isMenuOpen" class="page-mobile-navigation">
    <router-link to="/" @click="isMenuOpen = false">
        HOME
    </router-link>

    <router-link to="/profile" @click="isMenuOpen = false">
        PROFILE
    </router-link>

    <a href="#stats" @click="isMenuOpen = false">
        STATS
    </a>

    <a href="#membership" @click="isMenuOpen = false">
        MEMBERSHIP
    </a>

    <a href="#friends" @click="isMenuOpen = false">
        FRIENDS
    </a>

    <router-link to="/calories" @click="isMenuOpen = false">
        CALORIE TRACKING
    </router-link>
    <router-link to="/workout" @click="isMenuOpen = false">
        WORKOUTS AND NUTRITION
    </router-link>

    <button type="button" @click="logout">
        LOG OUT
    </button>
</div>


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

            <section v-if="membership" class="profile-section" id="qr">
                <div class="section-title">
                    <span>04</span>
                    ENTRY QR CODE
                </div>

                <div class="qr-panel">
                    <div class="qr-tokens">
                        <strong>{{ membership.remaining_tokens }}</strong>
                        <span>TOKENS LEFT</span>
                    </div>

                    <div class="qr-content">
                        <template v-if="qrImage">
                            <img
                                :src="qrImage"
                                alt="Your entry QR code"
                                class="qr-image"
                            />

                            <p class="qr-timer">
                                VALID FOR {{ qrCountdown }}
                            </p>

                            <p class="qr-hint">
                                Show this code at the gym entrance. You can
                                generate a new one when this code expires.
                            </p>
                        </template>

                        <template v-else>
                            <p class="qr-hint">
                                Generating a QR code uses 1 token. The code
                                is valid for 24 hours.
                            </p>

                            <button
                                type="button"
                                class="membership-button qr-button"
                                :disabled="qrLoading || membership.remaining_tokens < 1"
                                @click="generateQr"
                            >
                                {{ qrLoading ? 'GENERATING...' : 'GET QR CODE' }}
                            </button>

                            <p
                                v-if="membership.remaining_tokens < 1"
                                class="qr-error"
                            >
                                You have no tokens left. Buy a new plan to
                                continue.
                            </p>
                        </template>

                        <p v-if="qrError" class="qr-error">
                            {{ qrError }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="profile-section" id="friends">
                <div class="section-title">
                    <span>05</span>
                    FRIENDS
                </div>

                <div class="friends-empty">
                    <h2>NO FRIENDS YET</h2>

                    <p>
                        Train together, stay motivated. Your Gym Warriors
                        friends will appear here.
                    </p>
                </div>
            </section>

            <section class="profile-section">
                <div class="section-title">
                    <span>06</span>
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
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import QRCode from 'qrcode';
import { useRouter } from 'vue-router';

const router = useRouter();
const isMenuOpen = ref(false);

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

const qrImage = ref('');
const qrExpiresAt = ref(0);
const qrLoading = ref(false);
const qrError = ref('');
const now = ref(Date.now());

let timer = null;

const qrSecondsLeft = computed(() => {
    if (!qrImage.value) {
        return 0;
    }

    return Math.max(Math.floor((qrExpiresAt.value - now.value) / 1000), 0);
});

const qrCountdown = computed(() => {
    const total = qrSecondsLeft.value;

    const hours = String(Math.floor(total / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
    const seconds = String(total % 60).padStart(2, '0');

    return `${hours}:${minutes}:${seconds}`;
});

// When the 24 hours run out the QR code disappears
// and the "GET QR CODE" button shows up again.
watch(qrSecondsLeft, (seconds) => {
    if (seconds === 0 && qrImage.value) {
        qrImage.value = '';
        qrExpiresAt.value = 0;
    }
});

const csrfToken = () => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

const showQr = async (qrCode) => {
    now.value = Date.now();
    qrExpiresAt.value = Date.now() + qrCode.expires_in * 1000;

    qrImage.value = await QRCode.toDataURL(qrCode.code, {
        width: 480,
        margin: 2,
    });
};

const loadQr = async () => {
    try {
        const response = await fetch('/api/qr-code', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (data.qr_code) {
            await showQr(data.qr_code);
        }
    } catch (error) {
        console.error('QR LOAD ERROR:', error);
    }
};

const generateQr = async () => {
    qrLoading.value = true;
    qrError.value = '';

    try {
        const response = await fetch('/api/qr-code', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': csrfToken(),
            },
        });

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        const data = await response.json();

        if (!response.ok) {
            qrError.value = data.message || 'Could not generate a QR code.';
            return;
        }

        membership.value.remaining_tokens = data.remaining_tokens;

        await showQr(data.qr_code);
    } catch (error) {
        qrError.value = 'Could not generate a QR code.';
        console.error('QR GENERATE ERROR:', error);
    } finally {
        qrLoading.value = false;
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
    loadQr();

    timer = setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>