<template>
    <div class="gym-page">

        <!-- NAVBAR -->
        <header class="navbar">
            <router-link to="/" class="navbar-logo">
            GYM<span>WARRIORS</span>
            </router-link>

            <nav class="navbar-links">
                <a href="#features">FEATURES</a>
                <a href="#pricing">PRICING</a>
                <a href="#faq">FAQ</a>
            </nav>

            <div class="navbar-actions">

    <router-link
        v-if="isLoggedIn"
        to="/profile"
        class="login-btn"
    >
        PROFILE
    </router-link>

    <router-link
        v-else
        to="/login"
        class="login-btn"
    >
        LOG IN
    </router-link>

    <router-link
        v-if="isLoggedIn"
        to="/plans"
        class="start-btn"
    >
        MEMBERSHIP
    </router-link>

    <router-link
        v-else
        to="/register"
        class="start-btn"
    >
        START TRAINING
    </router-link>

</div>
        </header>


        <!-- HERO -->
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
                    <router-link to="/register" class="primary-btn">
                        START TRAINING
                    </router-link>

                    <a href="#features" class="secondary-btn">
                        EXPLORE FEATURES
                    </a>
                </div>

            </div>


            <!-- WARRIOR CARD -->
            <div class="warrior-card">

                <div class="warrior-card-header">
                    <span>WARRIOR STATUS</span>
                    <span class="status-online">● ONLINE</span>
                </div>

                <div class="warrior-avatar">
                    <div class="avatar-placeholder">
                        GW
                    </div>
                </div>

                <h2>YOUR JOURNEY</h2>

                <div class="rank-info">
                    <span>BEGINNER</span>
                    <span>LEVEL 01</span>
                </div>

                <div class="progress-bar">
                    <div class="progress-value"></div>
                </div>

                <p class="progress-text">
                    START YOUR JOURNEY TO UNLOCK NEW RANKS
                </p>

            </div>

        </section>


        <!-- STATS -->
        <section class="stats-section">

            <div class="stat">
                <strong>0</strong>
                <span>ACTIVE WARRIORS</span>
            </div>

            <div class="stat">
                <strong>0</strong>
                <span>WORKOUTS LOGGED</span>
            </div>

            <div class="stat">
                <strong>3</strong>
                <span>GYM LOCATIONS</span>
            </div>

        </section>


        <!-- SYSTEM -->
        <section class="system-section" id="features">

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


        <!-- FEATURES -->
        <section class="features-section">

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


        <!-- PRICING -->
        <section class="pricing-section" id="pricing">

            <div class="pricing-header">

                <div class="section-label">
                    MEMBERSHIP
                </div>

                <h2>
                    CHOOSE YOUR
                    <span>LEVEL</span>
                </h2>

            </div>


            <div class="pricing-grid">

                <article class="pricing-card">

                    <div class="pricing-card-top">
                        <span class="plan-name">
                            PARASTAIS
                        </span>

                        <span class="plan-id">
                            #01
                        </span>
                    </div>

                    <div class="plan-price">
                        €19.99
                        <span>/ MONTH</span>
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Gym access
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Workout tracking
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Progress tracking
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Up to 2 locations
                    </div>

                    <router-link to="/register" class="plan-button">
                        CHOOSE PLAN
                    </router-link>

                </article>


                <article class="pricing-card">

                    <div class="pricing-card-top">
                        <span class="plan-name">
                            PRO
                        </span>

                        <span class="plan-id">
                            #02
                        </span>
                    </div>

                    <div class="plan-price">
                        €29.99
                        <span>/ MONTH</span>
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Gym access
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Workout tracking
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Progress tracking
                    </div>

                    <div class="plan-feature">
                        <span>✓</span>
                        Unlimited locations
                    </div>

                    <router-link to="/register" class="plan-button">
                        CHOOSE PLAN
                    </router-link>

                </article>

            </div>

        </section>


        <!-- FAQ -->
        <section class="faq-section" id="faq">

            <div class="section-label">
                FAQ
            </div>

            <h2>
                FREQUENTLY ASKED
                <span>QUESTIONS</span>
            </h2>


            <div class="faq-list">

                <div class="faq-item">

                    <button class="faq-question">
                        <span>What is Gym Warriors?</span>
                        <span class="faq-icon">+</span>
                    </button>

                </div>


                <div class="faq-item">

                    <button class="faq-question">
                        <span>How does the ranking system work?</span>
                        <span class="faq-icon">+</span>
                    </button>

                </div>


                <div class="faq-item">

                    <button class="faq-question">
                        <span>Can I use multiple gym locations?</span>
                        <span class="faq-icon">+</span>
                    </button>

                </div>


                <div class="faq-item">

                    <button class="faq-question">
                        <span>Is there a free plan?</span>
                        <span class="faq-icon">+</span>
                    </button>

                </div>

            </div>

        </section>


        <!-- FOOTER -->
        <footer class="footer">

            <div class="footer-logo">
                GYM<span>WARRIORS</span>
            </div>

            <p>
                BUILD YOUR WARRIOR. BUILD YOUR LEGACY.
            </p>

            <div class="footer-links">
                <a href="#features">FEATURES</a>
                <a href="#pricing">PRICING</a>
                <a href="#faq">FAQ</a>
                <router-link to="/login">LOGIN</router-link>
            </div>

            <div class="footer-bottom">
                © 2026 Gym Warriors. All rights reserved.
            </div>

        </footer>

    </div>
</template>
<script setup>
import { onMounted, ref } from 'vue';

const isLoggedIn = ref(false);

const checkAuthentication = async () => {
    try {
        const response = await fetch('/api/user', {
            headers: {
                Accept: 'application/json',
            },
        });

        isLoggedIn.value = response.ok;
    } catch (error) {
        isLoggedIn.value = false;
    }
};

onMounted(() => {
    checkAuthentication();
});
</script>