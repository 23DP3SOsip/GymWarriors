<template>
    <div class="gym-page">

        <!-- =====================================================
             NAVBAR
        ====================================================== -->

        <header class="navbar">

            <!-- LOGO -->
            <router-link
                to="/"
                class="navbar-logo"
            >
                GYM<span>WARRIORS</span>
            </router-link>


            <!-- DESKTOP NAVIGATION -->
            <nav class="navbar-links">

                <a href="#features">
                    FEATURES
                </a>

                <a href="#pricing">
                    PRICING
                </a>

                <a href="#faq">
                    FAQ
                </a>

            </nav>


            <!-- DESKTOP ACTIONS + MOBILE BURGER -->
            <div class="navbar-actions">

                <!-- DESKTOP BUTTONS -->
                <div class="desktop-nav-actions">

                    <!-- LOGGED IN -->
                    <router-link
                        v-if="isLoggedIn"
                        to="/profile"
                        class="login-btn"
                    >
                        PROFILE
                    </router-link>

                    <!-- NOT LOGGED IN -->
                    <router-link
                        v-if="!isLoggedIn"
                        to="/login"
                        class="login-btn"
                    >
                        LOG IN
                    </router-link>


                    <router-link
                        v-if="isAdmin"
                        to="/admin"
                        class="login-btn"
                    >
                        ADMIN PANEL
                    </router-link>

                    <router-link
                        v-if="isLoggedIn"
                        to="/support"
                        class="login-btn"
                    >
                        SUPPORT
                    </router-link>

                    <!-- LOGGED IN -->
                    <router-link
                        v-if="isLoggedIn && !hasActiveMembership"
                        to="/plans"
                        class="start-btn"
                    >
                        MEMBERSHIP
                    </router-link>

                    <!-- NOT LOGGED IN -->
                    <router-link
                        v-if="!isLoggedIn"
                        to="/register"
                        class="start-btn"
                    >
                        START TRAINING
                    </router-link>

                </div>


                <!-- MOBILE BURGER -->
                <button
                    class="mobile-burger"
                    type="button"
                    aria-label="Open navigation menu"
                    :aria-expanded="isMenuOpen"
                    @click="isMenuOpen = !isMenuOpen"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

            </div>

        </header>


        <!-- =====================================================
             MOBILE NAVIGATION
        ====================================================== -->

        <div
            v-if="isMenuOpen"
            class="mobile-navigation"
        >

            <div class="mobile-navigation-content">

                <!-- FEATURES -->
                <a
                    href="#features"
                    @click="isMenuOpen = false"
                >
                    FEATURES
                </a>


                <!-- PRICING -->
                <a
                    href="#pricing"
                    @click="isMenuOpen = false"
                >
                    PRICING
                </a>


                <!-- FAQ -->
                <a
                    href="#faq"
                    @click="isMenuOpen = false"
                >
                    FAQ
                </a>


                <!-- LOGGED IN: PROFILE -->
                <router-link
                    v-if="isLoggedIn"
                    to="/profile"
                    @click="isMenuOpen = false"
                >
                    PROFILE
                </router-link>


                <!-- LOGGED IN: MEMBERSHIP -->
                <router-link
                    v-if="isLoggedIn && !hasActiveMembership"
                    to="/plans"
                    class="mobile-navigation-primary"
                    @click="isMenuOpen = false"
                >
                    MEMBERSHIP
                </router-link>

                <router-link
                    v-if="isAdmin"
                    to="/admin"
                    @click="isMenuOpen = false"
                >
                    ADMIN PANEL
                </router-link>

                <router-link
                    v-if="isLoggedIn"
                    to="/support"
                    @click="isMenuOpen = false"
                >
                    SUPPORT CHAT
                </router-link>


                <!-- NOT LOGGED IN: LOGIN -->
                <router-link
                    v-if="!isLoggedIn"
                    to="/login"
                    @click="isMenuOpen = false"
                >
                    LOG IN
                </router-link>


                <!-- NOT LOGGED IN: REGISTER -->
                <router-link
                    v-if="!isLoggedIn"
                    to="/register"
                    class="mobile-navigation-primary"
                    @click="isMenuOpen = false"
                >
                    START TRAINING
                </router-link>

            </div>

        </div>


        <!-- =====================================================
             HERO
        ====================================================== -->

        <section class="hero">

            <div class="hero-content">

                <p class="hero-small">
                    WELCOME TO GYM WARRIORS
                </p>


                <h1>
                    BUILD YOUR
                    <span>WARRIOR</span>
                </h1>


                <p class="hero-description">
                    Track your workouts, monitor your progress,
                    climb the ranks and become stronger every day.
                </p>


                <div class="hero-buttons">

                    <router-link
                        :to="isLoggedIn ? '/profile' : '/register'"
                        class="primary-btn"
                    >
                        {{ isLoggedIn ? 'GO TO PROFILE' : 'START TRAINING' }}
                    </router-link>


                    <a
                        href="#features"
                        class="secondary-btn"
                    >
                        EXPLORE FEATURES
                    </a>

                </div>

            </div>


            <!-- WARRIOR CARD -->

            <div class="warrior-card">

                <div class="warrior-card-header">

                    <span>
                        WARRIOR STATUS
                    </span>

                    <span class="status-online">
                        ● ONLINE
                    </span>

                </div>


                <div class="warrior-avatar">

                    <div class="avatar-placeholder">
                        GW
                    </div>

                </div>


                <h2>
                    YOUR JOURNEY
                </h2>


                <div class="rank-info">

                    <span>
                        BEGINNER
                    </span>

                    <span>
                        LEVEL 01
                    </span>

                </div>


                <div class="progress-bar">

                    <div class="progress-value"></div>

                </div>


                <p class="progress-text">
                    START YOUR JOURNEY TO UNLOCK NEW RANKS
                </p>

            </div>

        </section>

        <section v-if="isLoggedIn" class="leaderboard-section" aria-labelledby="leaderboard-title">
            <div class="leaderboard-heading">
                <p class="hero-small">GYM WARRIORS RANKING</p>
                <h2 id="leaderboard-title">CLIMB THE <span>RANKS</span></h2>
            </div>

            <p v-if="leaderboardLoading" class="leaderboard-message">Loading rankings...</p>
            <p v-else-if="leaderboardError" class="leaderboard-message leaderboard-error">{{ leaderboardError }}</p>
            <div v-else-if="leaderboard" class="leaderboard-content">
                <article class="leaderboard-user-rank">
                    <span class="leaderboard-label">YOUR RANK</span>
                    <strong>#{{ leaderboard.rank }}</strong>
                    <span>OF {{ leaderboard.total_users }} WARRIORS</span>
                    <p>{{ leaderboard.workouts_count }} {{ leaderboard.workouts_count === 1 ? 'WORKOUT' : 'WORKOUTS' }} COMPLETED</p>
                </article>

                <div class="leaderboard-top">
                    <h3>TOP 3 WARRIORS</h3>
                    <ol>
                        <li v-for="warrior in leaderboard.top_users" :key="warrior.rank">
                            <span class="leaderboard-place">#{{ warrior.rank }}</span>
                            <span class="leaderboard-name">{{ warrior.first_name }} {{ warrior.last_name }}</span>
                            <strong>{{ warrior.workouts_count }} {{ warrior.workouts_count === 1 ? 'WORKOUT' : 'WORKOUTS' }}</strong>
                        </li>
                    </ol>
                </div>
            </div>
        </section>


        <!-- =====================================================
             STATS
        ====================================================== -->

        <section class="stats-section">

            <div class="stat">

                <strong>
                    {{ homepageData?.stats.warriors ?? '—' }}
                </strong>

                <span>
                    ACTIVE WARRIORS
                </span>

            </div>


            <div class="stat">

                <strong>
                    {{ homepageData?.stats.workouts ?? '—' }}
                </strong>

                <span>
                    WORKOUTS LOGGED
                </span>

            </div>


            <div class="stat">

                <strong>
                    {{ homepageData?.stats.locations ?? '—' }}
                </strong>

                <span>
                    GYM LOCATIONS
                </span>

            </div>

        </section>


        <!-- =====================================================
             SYSTEM
        ====================================================== -->

        <section
            class="system-section"
            id="features"
        >

            <div class="section-label">
                THE SYSTEM
            </div>


            <h2>
                NO GUESSWORK.<br>
                <span>JUST PROGRESS.</span>
            </h2>


            <p>
                Gym Warriors gives you the tools to track your fitness
                journey, understand your progress and stay motivated.
            </p>

        </section>


        <!-- =====================================================
             FEATURES
        ====================================================== -->

        <section class="features-section">

            <!-- FEATURE 01 -->

            <article class="feature-card">

                <div class="feature-number">
                    01
                </div>


                <h3>
                    TRACK YOUR WORKOUTS
                </h3>


                <p>
                    Keep track of your training activity and build
                    a consistent workout history.
                </p>


                <div class="feature-line"></div>

            </article>


            <!-- FEATURE 02 -->

            <article class="feature-card">

                <div class="feature-number">
                    02
                </div>


                <h3>
                    CLIMB THE RANKS
                </h3>


                <p>
                    Improve your performance and progress through
                    the Gym Warriors ranking system.
                </p>


                <div class="feature-line"></div>

            </article>


            <!-- FEATURE 03 -->

            <article class="feature-card">

                <div class="feature-number">
                    03
                </div>


                <h3>
                    TRAIN TOGETHER
                </h3>


                <p>
                    Connect with other warriors, compare progress
                    and stay motivated.
                </p>


                <div class="feature-line"></div>

            </article>

        </section>


        <!-- =====================================================
             PRICING
        ====================================================== -->

        <section
            class="pricing-section"
            id="pricing"
        >

            <div class="pricing-header">

                <div class="section-label">
                    MEMBERSHIP
                </div>


                <h2>
                    CHOOSE YOUR
                    <span>LEVEL</span>
                </h2>

            </div>


            <p v-if="homepageError" class="homepage-data-error">
                {{ homepageError }}
            </p>
            <p v-else-if="!homepageData" class="homepage-data-message">
                Loading membership plans…
            </p>
            <p v-else-if="!homepageData.plans.length" class="homepage-data-message">
                No membership plans are available right now.
            </p>
            <div v-else class="pricing-grid">
                <article
                    v-for="(plan, index) in homepageData.plans"
                    :key="plan.id"
                    class="pricing-card"
                >

                    <div class="pricing-card-top">

                        <span class="plan-name">
                            {{ plan.name }}
                        </span>

                        <span class="plan-id">
                            #{{ String(index + 1).padStart(2, '0') }}
                        </span>

                    </div>


                    <div class="plan-price">
                        €{{ Number(plan.price).toFixed(2) }}
                        <span>/ 30 DAYS</span>
                    </div>

                    <div v-for="feature in plan.features" :key="feature" class="plan-feature">
                        <span>✓</span>
                        {{ feature }}
                    </div>

                    <router-link
                        to="/plans"
                        class="plan-button"
                    >
                        CHOOSE PLAN
                    </router-link>

                </article>

            </div>

        </section>


        <!-- =====================================================
             FAQ
        ====================================================== -->

        <section
            class="faq-section"
            id="faq"
        >

            <div class="section-label">
                FAQ
            </div>


            <h2>
                FREQUENTLY ASKED
                <span>QUESTIONS</span>
            </h2>


            <div class="faq-list">

                <!-- FAQ 01 -->

                <div class="faq-item" :class="{ 'faq-item-open': openFaq === 0 }">

                    <button
                        class="faq-question"
                        type="button"
                        :aria-expanded="openFaq === 0"
                        aria-controls="faq-answer-0"
                        @click="toggleFaq(0)"
                    >
                        <span>
                            What is Gym Warriors?
                        </span>

                        <span class="faq-icon" aria-hidden="true">
                            {{ openFaq === 0 ? '−' : '+' }}
                        </span>
                    </button>
                    <p v-if="openFaq === 0" id="faq-answer-0" class="faq-answer">
                        Gym Warriors is a fitness platform where you can track workouts, follow your progress, connect with friends and train at our gym locations.
                    </p>

                </div>


                <!-- FAQ 02 -->

                <div class="faq-item" :class="{ 'faq-item-open': openFaq === 1 }">

                    <button
                        class="faq-question"
                        type="button"
                        :aria-expanded="openFaq === 1"
                        aria-controls="faq-answer-1"
                        @click="toggleFaq(1)"
                    >
                        <span>
                            How does the ranking system work?
                        </span>

                        <span class="faq-icon" aria-hidden="true">
                            {{ openFaq === 1 ? '−' : '+' }}
                        </span>
                    </button>
                    <p v-if="openFaq === 1" id="faq-answer-1" class="faq-answer">
                        Your rank is based on the number of completed workouts recorded in your account. Keep training consistently to climb the leaderboard.
                    </p>

                </div>


                <!-- FAQ 03 -->

                <div class="faq-item" :class="{ 'faq-item-open': openFaq === 2 }">

                    <button
                        class="faq-question"
                        type="button"
                        :aria-expanded="openFaq === 2"
                        aria-controls="faq-answer-2"
                        @click="toggleFaq(2)"
                    >
                        <span>
                            Can I use multiple gym locations?
                        </span>

                        <span class="faq-icon" aria-hidden="true">
                            {{ openFaq === 2 ? '−' : '+' }}
                        </span>
                    </button>
                    <p v-if="openFaq === 2" id="faq-answer-2" class="faq-answer">
                        Yes. The Pro membership includes access to all active locations. The standard plan includes access to the locations listed for that plan.
                    </p>

                </div>


                <!-- FAQ 04 -->

                <div class="faq-item" :class="{ 'faq-item-open': openFaq === 3 }">

                    <button
                        class="faq-question"
                        type="button"
                        :aria-expanded="openFaq === 3"
                        aria-controls="faq-answer-3"
                        @click="toggleFaq(3)"
                    >
                        <span>
                            Is there a free plan?
                        </span>

                        <span class="faq-icon" aria-hidden="true">
                            {{ openFaq === 3 ? '−' : '+' }}
                        </span>
                    </button>
                    <p v-if="openFaq === 3" id="faq-answer-3" class="faq-answer">
                        Gym access requires a paid membership. You can compare current plans and prices in the Membership section above.
                    </p>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <footer class="footer">

            <div class="footer-logo">
                GYM<span>WARRIORS</span>
            </div>


            <p>
                BUILD YOUR WARRIOR. BUILD YOUR LEGACY.
            </p>


            <div class="footer-links">

                <a href="#features">
                    FEATURES
                </a>

                <a href="#pricing">
                    PRICING
                </a>

                <a href="#faq">
                    FAQ
                </a>


                <!-- NOT LOGGED IN -->

                <router-link
                    v-if="!isLoggedIn"
                    to="/login"
                >
                    LOGIN
                </router-link>


                <!-- LOGGED IN -->

                <router-link
                    v-if="isLoggedIn"
                    to="/profile"
                >
                    PROFILE
                </router-link>

                <router-link
                    v-if="isAdmin"
                    to="/admin"
                >
                    ADMIN PANEL
                </router-link>

                <router-link
                    v-if="isLoggedIn"
                    to="/support"
                >
                    SUPPORT CHAT
                </router-link>

            </div>


            <div class="footer-bottom">
                © 2026 Gym Warriors. All rights reserved.
            </div>

        </footer>

    </div>
</template>


<script setup>

import {
    onMounted,
    ref
} from 'vue';


// =====================================================
// AUTHENTICATION
// =====================================================

const isLoggedIn = ref(false);
const isAdmin = ref(false);
const hasActiveMembership = ref(false);
const homepageData = ref(null);
const homepageError = ref('');
const leaderboard = ref(null);
const leaderboardLoading = ref(false);
const leaderboardError = ref('');


// =====================================================
// MOBILE MENU
// =====================================================

const isMenuOpen = ref(false);
const openFaq = ref(null);

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index;
};


// =====================================================
// CHECK USER AUTHENTICATION
// =====================================================

const checkAuthentication = async () => {
    try {
        const response = await fetch('/api/user', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.status === 401 || response.status === 403) {
            isLoggedIn.value = false;
            isAdmin.value = false;
            hasActiveMembership.value = false;
            return;
        }

        if (!response.ok) {
            isLoggedIn.value = false;
            isAdmin.value = false;
            hasActiveMembership.value = false;
            console.error('Neizdevās pārbaudīt lietotāja autentifikāciju.');
            return;
        }

        const user = await response.json();
        isLoggedIn.value = true;
        isAdmin.value = user.role === 'admin';
        hasActiveMembership.value = user.has_active_membership;
        await loadLeaderboard();
    } catch (error) {
        isLoggedIn.value = false;
        isAdmin.value = false;
        hasActiveMembership.value = false;
        console.error(
            'Neizdevās izveidot savienojumu ar autentifikācijas API.',
            error
        );
    }
};

const loadHomepageData = async () => {
    homepageError.value = '';
    try {
        const response = await fetch('/api/homepage', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Could not load homepage data.');
        }

        homepageData.value = await response.json();
    } catch (error) {
        homepageError.value = 'Could not load membership plans and gym statistics. Please refresh the page.';
        console.error('HOMEPAGE DATA ERROR:', error);
    }
};

const loadLeaderboard = async () => {
    leaderboardLoading.value = true;
    leaderboardError.value = '';
    try {
        const response = await fetch('/api/leaderboard', {
            headers: { Accept: 'application/json' },
        });

        if (response.status === 401) {
            isLoggedIn.value = false;
            return;
        }

        if (!response.ok) {
            throw new Error('Could not load the leaderboard.');
        }

        leaderboard.value = await response.json();
    } catch (error) {
        leaderboardError.value = error.message || 'Could not load the leaderboard.';
        console.error('LEADERBOARD ERROR:', error);
    } finally {
        leaderboardLoading.value = false;
    }
};


// =====================================================
// PAGE LOAD
// =====================================================

onMounted(() => {
    checkAuthentication();
    loadHomepageData();
});

</script>