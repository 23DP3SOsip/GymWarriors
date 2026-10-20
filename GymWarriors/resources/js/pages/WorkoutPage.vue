<template>
    <div class="profile-page">
        <header class="profile-navbar">
            <router-link to="/" class="profile-logo">GYM<span>WARRIORS</span></router-link>
            <nav class="profile-nav">
                <router-link to="/profile">PROFILE</router-link>
                <router-link to="/profile#stats">STATS</router-link>
                <router-link to="/profile#membership">MEMBERSHIP</router-link>
                <router-link to="/profile#friends">FRIENDS</router-link>
                <router-link to="/calories">CALORIE TRACKING</router-link>
                <router-link to="/workout" class="nav-current">WORKOUTS AND NUTRITION</router-link>
                <router-link to="/reports">REPORTS</router-link>
            </nav>
            <button type="button" class="page-mobile-burger" @click="isMenuOpen = !isMenuOpen" :aria-expanded="isMenuOpen" aria-label="Open navigation menu">
                <span></span><span></span><span></span>
            </button>
            <button class="logout-button" @click="logout">LOG OUT</button>
        </header>

        <div v-if="isMenuOpen" class="page-mobile-navigation">
            <router-link to="/" @click="isMenuOpen = false">HOME</router-link>
            <router-link to="/profile" @click="isMenuOpen = false">PROFILE</router-link>
            <router-link to="/profile#stats" @click="isMenuOpen = false">STATS</router-link>
            <router-link to="/profile#membership" @click="isMenuOpen = false">MEMBERSHIP</router-link>
            <router-link to="/profile#friends" @click="isMenuOpen = false">FRIENDS</router-link>
            <router-link to="/calories" @click="isMenuOpen = false">CALORIE TRACKING</router-link>
            <router-link to="/workout" @click="isMenuOpen = false">WORKOUTS AND NUTRITION</router-link>
            <router-link to="/reports" @click="isMenuOpen = false">REPORTS</router-link>
            <button type="button" @click="logout">LOG OUT</button>
        </div>

        <main class="profile-container">
            <section class="calorie-header">
                <p class="profile-label">WORKOUTS AND NUTRITION</p>
                <h1>TRAIN SMARTER</h1>
                <p class="calorie-intro">Choose your workout type and get personalized nutrition, hydration, and training advice.</p>
            </section>

            <section class="profile-section">
                <div class="section-title"><span>01</span> YOUR DETAILS</div>
                <form class="calorie-form" @submit.prevent="loadRecommendation">
                    <label>HEIGHT (CM)<input v-model.number="form.height_cm" type="number" min="100" max="250" required /></label>
                    <label>WEIGHT (KG)<input v-model.number="form.weight_kg" type="number" min="30" max="300" step="0.1" required /></label>
                    <label>AGE<input v-model.number="form.age" type="number" min="13" max="100" required /></label>
                    <label>SEX<select v-model="form.sex"><option value="male">Male</option><option value="female">Female</option></select></label>
                    <label class="calorie-wide">ACTIVITY LEVEL<select v-model="form.activity_level"><option v-for="level in activityLevels" :key="level.value" :value="level.value">{{ level.label }}</option></select></label>
                    <label class="calorie-wide">WORKOUT TYPE<select v-model="workoutType"><option v-for="workout in workoutTypes" :key="workout.value" :value="workout.value">{{ workout.label }}</option></select></label>
                    <label class="calorie-wide">GOAL<select v-model="form.goal"><option value="lose">Lose weight</option><option value="maintain">Maintain weight</option><option value="gain">Gain weight</option></select></label>
                    <p v-if="errorMessage" class="calorie-message error">{{ errorMessage }}</p>
                    <button type="submit" class="membership-button calorie-submit" :disabled="loading">{{ loading ? 'CALCULATING...' : 'GET RECOMMENDATION' }}</button>
                </form>
            </section>

            <section v-if="recommendation" class="profile-section">
                <div class="section-title"><span>02</span> PERSONAL RECOMMENDATION</div>
                <div class="recommendation-heading"><h2>{{ recommendation.workout_name }}</h2><span>{{ recommendation.duration }}</span></div>
                <div class="recommendation-grid">
                    <article><strong>BEFORE WORKOUT</strong><p>{{ recommendation.before }}</p></article>
                    <article><strong>AFTER WORKOUT</strong><p>{{ recommendation.after }}</p></article>
                    <article><strong>HYDRATION</strong><p>{{ recommendation.hydration }}</p></article>
                    <article><strong>GUIDELINES</strong><p>{{ recommendation.calories.before }} kcal before · {{ recommendation.calories.after }} kcal after · {{ recommendation.protein_target }} g daily protein</p></article>
                </div>
                <ul class="recommendation-tips"><li v-for="tip in recommendation.tips" :key="tip">{{ tip }}</li></ul>
                <p class="calorie-note">{{ recommendation.notice }}</p>
            </section>

            <section class="profile-section">
                <div class="section-title"><span>03</span> WORKOUT LIBRARY</div>
                <div class="workout-library">
                    <article v-for="template in templates" :key="template.id" class="workout-card">
                        <h2>{{ template.name }}</h2>
                        <p>{{ template.description }}</p>
                        <ul>
                            <li v-for="exercise in template.exercises" :key="exercise.id">
                                {{ exercise.name }} · {{ exercise.pivot.sets }} sets
                                <span v-if="exercise.pivot.reps">× {{ exercise.pivot.reps }} reps</span>
                                <span v-else>· {{ exercise.pivot.duration_seconds }} sec</span>
                            </li>
                        </ul>
                        <button type="button" class="membership-button" @click="selectTemplate(template)">
                            LOG THIS WORKOUT
                        </button>
                    </article>
                </div>
            </section>

            <section v-if="selectedTemplate" class="profile-section">
                <div class="section-title"><span>04</span> SAVE WORKOUT</div>
                <form class="calorie-form" @submit.prevent="saveWorkout">
                    <label class="calorie-wide">NOTES
                        <input v-model="workoutNotes" type="text" maxlength="2000" placeholder="How did the workout feel?" />
                    </label>
                    <p v-if="workoutMessage" class="calorie-message success">{{ workoutMessage }}</p>
                    <button type="submit" class="membership-button calorie-submit" :disabled="savingWorkout">
                        {{ savingWorkout ? 'SAVING...' : 'SAVE COMPLETED WORKOUT' }}
                    </button>
                </form>
            </section>

            <section v-if="history.length" class="profile-section">
                <div class="section-title"><span>05</span> YOUR WORKOUT HISTORY</div>
                <div class="workout-history">
                    <article v-for="workout in history" :key="workout.id">
                        <strong>{{ workout.name }}</strong>
                        <span>{{ new Date(workout.started_at).toLocaleDateString() }}</span>
                    </article>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const isMenuOpen = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const recommendation = ref(null);
const templates = ref([]);
const history = ref([]);
const selectedTemplate = ref(null);
const workoutNotes = ref('');
const savingWorkout = ref(false);
const workoutMessage = ref('');
const workoutType = ref('strength');
const activityLevels = [
    { value: 'sedentary', label: 'Sedentary — little or no exercise' },
    { value: 'light', label: 'Light — exercise 1-3 days/week' },
    { value: 'moderate', label: 'Moderate — exercise 3-5 days/week' },
    { value: 'active', label: 'Active — exercise 6-7 days/week' },
    { value: 'very_active', label: 'Very active — hard training or physical job' },
];
const workoutTypes = [
    { value: 'strength', label: 'Strength training' },
    { value: 'cardio', label: 'Cardio' },
    { value: 'hiit', label: 'HIIT / interval training' },
    { value: 'flexibility', label: 'Mobility / stretching' },
];
const form = reactive({
    height_cm: null,
    weight_kg: null,
    age: null,
    sex: 'male',
    activity_level: 'moderate',
    goal: 'maintain',
});

const csrfToken = () => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
};

const loadProfile = async () => {
    const response = await fetch('/api/calorie-profile', { headers: { Accept: 'application/json' } });
    if (response.status === 401) {
        router.push('/login');
        return;
    }
    if (!response.ok) return;
    const saved = (await response.json()).calorie_profile;
    if (saved) Object.assign(form, { ...saved, weight_kg: Number(saved.weight_kg) });
};

const loadRecommendation = async () => {
    loading.value = true;
    errorMessage.value = '';
    try {
        const response = await fetch('/api/workout-recommendations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ ...form, workout_type: workoutType.value }),
        });
        if (response.status === 401) {
            router.push('/login');
            return;
        }
        const data = await response.json();
        if (!response.ok) {
            errorMessage.value = data.message || 'Could not load recommendations.';
            return;
        }
        recommendation.value = data;
    } catch (error) {
        errorMessage.value = 'Could not load recommendations.';
        console.error('WORKOUT RECOMMENDATION ERROR:', error);
    } finally {
        loading.value = false;
    }
};

const loadWorkoutData = async () => {
    const [templatesResponse, historyResponse] = await Promise.all([
        fetch('/api/workout-templates', { headers: { Accept: 'application/json' } }),
        fetch('/api/workouts', { headers: { Accept: 'application/json' } }),
    ]);

    if (templatesResponse.ok) templates.value = (await templatesResponse.json()).workouts;
    if (historyResponse.ok) history.value = (await historyResponse.json()).workouts;
};

const selectTemplate = (template) => {
    selectedTemplate.value = template;
    workoutMessage.value = '';
};

const saveWorkout = async () => {
    savingWorkout.value = true;
    workoutMessage.value = '';
    try {
        const response = await fetch('/api/workouts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                workout_template_id: selectedTemplate.value.id,
                name: selectedTemplate.value.name,
                type: selectedTemplate.value.type,
                started_at: new Date().toISOString(),
                completed_at: new Date().toISOString(),
                notes: workoutNotes.value,
                exercises: selectedTemplate.value.exercises.map((exercise) => ({
                    exercise_id: exercise.id,
                    sets_completed: exercise.pivot.sets,
                    reps_completed: exercise.pivot.reps,
                    duration_seconds: exercise.pivot.duration_seconds,
                })),
            }),
        });
        if (response.status === 401) {
            router.push('/login');
            return;
        }
        if (!response.ok) throw new Error('Could not save workout');
        workoutMessage.value = 'Workout saved successfully.';
        workoutNotes.value = '';
        await loadWorkoutData();
    } catch (error) {
        workoutMessage.value = 'Could not save workout.';
        console.error('WORKOUT SAVE ERROR:', error);
    } finally {
        savingWorkout.value = false;
    }
};

const logout = async () => {
    await fetch('/logout', { method: 'POST', headers: { Accept: 'application/json', 'X-XSRF-TOKEN': csrfToken() } });
    router.push('/');
};

onMounted(async () => {
    await loadProfile();
    await loadWorkoutData();
});
</script>
