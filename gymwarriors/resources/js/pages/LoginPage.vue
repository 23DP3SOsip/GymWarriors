<template>
    <div class="auth-page">

        <div class="auth-card">

            <div class="auth-logo">
                GYM<span>WARRIORS</span>
            </div>

            <p class="auth-label">WELCOME BACK</p>

            <h1>LOG IN</h1>

            <p class="auth-description">
                Log in to continue your warrior journey.
            </p>

            <form @submit.prevent="login">

                <div class="form-group">
                    <label for="email">EMAIL</label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Enter your email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>

                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <p v-if="error" class="auth-error">
                    {{ error }}
                </p>

                <button type="submit" class="auth-button">
                    LOG IN
                </button>

            </form>

            <div class="auth-switch">
                Don't have an account?

                <router-link to="/register">
                    SIGN UP
                </router-link>
            </div>

            <router-link to="/" class="back-home">
                ← BACK TO HOME
            </router-link>

        </div>

    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = reactive({
    email: '',
    password: '',
});

const error = ref('');

const login = async () => {
    error.value = '';

    try {
        const response = await fetch('/login', {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },

            body: JSON.stringify(form),
        });

        const data = await response.json();

        if (!response.ok) {
            error.value = data.message || 'Incorrect email or password.';
            return;
        }

        router.push('/dashboard');

    } catch (err) {
        error.value = 'Something went wrong. Please try again.';
    }
};
</script>