<template>
    <div class="auth-page">

        <div class="auth-card">

            <div class="auth-logo">
                GYM<span>WARRIORS</span>
            </div>

            <p class="auth-label">JOIN THE WARRIORS</p>

            <h1>SIGN UP</h1>

            <p class="auth-description">
                Create your account and start your warrior journey.
            </p>

            <form @submit.prevent="register">

                <div class="form-row">

                    <div class="form-group">
                        <label for="first_name">FIRST NAME</label>

                        <input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            placeholder="First name"
                            maxlength="30"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">LAST NAME</label>

                        <input
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            placeholder="Last name"
                            maxlength="30"
                            required
                        >
                    </div>

                </div>

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
                    <label for="phone">PHONE</label>

                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="Phone number"
                    >
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>

                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Create a password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        CONFIRM PASSWORD
                    </label>

                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Repeat your password"
                        required
                    >
                </div>

                <p v-if="error" class="auth-error">
                    {{ error }}
                </p>

                <button
                    type="submit"
                    class="auth-button"
                    :disabled="loading"
                >
                    {{ loading ? 'CREATING ACCOUNT...' : 'CREATE ACCOUNT' }}
                </button>

            </form>

            <div class="auth-switch">
                Already have an account?

                <router-link to="/login">
                    LOG IN
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
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const error = ref('');
const loading = ref(false);

const register = async () => {
    error.value = '';

    if (form.password !== form.password_confirmation) {
        error.value = 'Passwords do not match.';
        return;
    }

    loading.value = true;

    try {
        const response = await fetch('/register', {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },

            body: JSON.stringify(form),
        });

        const data = await response.json();

        if (!response.ok) {
            error.value = data.message || 'Registration failed.';
            return;
        }

        router.push('/dashboard');

    } catch (err) {
        error.value = 'Something went wrong. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>