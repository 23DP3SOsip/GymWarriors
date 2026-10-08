<template>
    <div class="profile-page">
        <header class="profile-navbar">
            <router-link to="/" class="profile-logo">
                GYM<span>WARRIORS</span>
            </router-link>

            <nav class="profile-nav">
                <router-link to="/profile">PROFILE</router-link>
                <router-link to="/profile#stats">STATS</router-link>
                <router-link to="/profile#membership">MEMBERSHIP</router-link>
                <router-link to="/profile#friends">FRIENDS</router-link>
                <router-link to="/calories" class="nav-current">CALORIE TRACKING</router-link>
            </nav>

            <button class="logout-button" @click="logout">
                LOG OUT
            </button>
        </header>

        <main class="profile-container">
            <section class="calorie-header">
                <p class="profile-label">CALORIE TRACKING</p>
                <h1>YOUR DAILY TARGET</h1>
                <p class="calorie-intro">
                    Enter your details and choose a goal. We estimate how
                    many calories you burn per day and what to aim for.
                </p>
            </section>

            <section class="profile-section">
                <div class="section-title">
                    <span>01</span>
                    YOUR DETAILS
                </div>

                <form class="calorie-form" @submit.prevent="save">
                    <label>
                        HEIGHT (CM)
                        <input v-model.number="form.height_cm" type="number" min="100" max="250" placeholder="180" required />
                    </label>

                    <label>
                        WEIGHT (KG)
                        <input v-model.number="form.weight_kg" type="number" min="30" max="300" step="0.1" placeholder="75" required />
                    </label>

                    <label>
                        AGE
                        <input v-model.number="form.age" type="number" min="13" max="100" placeholder="25" required />
                    </label>

                    <label>
                        SEX
                        <select v-model="form.sex" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </label>

                    <label class="calorie-wide">
                        ACTIVITY LEVEL
                        <select v-model="form.activity_level" required>
                            <option v-for="level in activityLevels" :key="level.value" :value="level.value">
                                {{ level.label }}
                            </option>
                        </select>
                    </label>

                    <div class="calorie-wide">
                        <p class="calorie-field-label">YOUR GOAL</p>

                        <div class="goal-options">
                            <button
                                v-for="goal in goals"
                                :key="goal.value"
                                type="button"
                                class="goal-option"
                                :class="{ active: form.goal === goal.value }"
                                @click="form.goal = goal.value"
                            >
                                <strong>{{ goal.label }}</strong>
                                <span>{{ goal.hint }}</span>
                            </button>
                        </div>
                    </div>

                    <p v-if="errorMessage" class="calorie-message error">{{ errorMessage }}</p>
                    <p v-if="savedMessage" class="calorie-message success">{{ savedMessage }}</p>

                    <button type="submit" class="membership-button calorie-submit" :disabled="saving">
                        {{ saving ? 'SAVING...' : 'SAVE MY DATA' }}
                    </button>
                </form>
            </section>

            <section v-if="result" class="profile-section">
                <div class="section-title">
                    <span>02</span>
                    YOUR RESULTS
                </div>

                <div class="stats-grid calorie-results">
                    <div class="profile-stat">
                        <strong>{{ result.bmr }}</strong>
                        <span>BMR (KCAL)</span>
                    </div>
                    <div class="profile-stat">
                        <strong>{{ result.maintenance }}</strong>
                        <span>MAINTENANCE (KCAL)</span>
                    </div>
                    <div class="profile-stat">
                        <strong>{{ result.target }}</strong>
                        <span>DAILY TARGET (KCAL)</span>
                    </div>
                    <div class="profile-stat">
                        <strong>{{ result.bmi }}</strong>
                        <span>BMI</span>
                    </div>
                </div>

                <div class="macro-grid">
                    <div>
                        <strong>{{ result.protein }} g</strong>
                        <span>PROTEIN</span>
                    </div>
                    <div>
                        <strong>{{ result.carbs }} g</strong>
                        <span>CARBS</span>
                    </div>
                    <div>
                        <strong>{{ result.fat }} g</strong>
                        <span>FAT</span>
                    </div>
                </div>

                <p class="calorie-note">
                    {{ goalSummary }} These numbers are estimates
                    (Mifflin-St Jeor formula), not medical advice.
                </p>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const activityLevels = [
    { value: 'sedentary', label: 'Sedentary — little or no exercise', factor: 1.2 },
    { value: 'light', label: 'Light — exercise 1-3 days/week', factor: 1.375 },
    { value: 'moderate', label: 'Moderate — exercise 3-5 days/week', factor: 1.55 },
    { value: 'active', label: 'Active — exercise 6-7 days/week', factor: 1.725 },
    { value: 'very_active', label: 'Very active — hard training or physical job', factor: 1.9 },
];

const goals = [
    { value: 'lose', label: 'LOSE WEIGHT', hint: 'Calorie deficit' },
    { value: 'maintain', label: 'MAINTAIN', hint: 'Stay at your weight' },
    { value: 'gain', label: 'GAIN WEIGHT', hint: 'Calorie surplus' },
];

const form = reactive({
    height_cm: null,
    weight_kg: null,
    age: null,
    sex: 'male',
    activity_level: 'moderate',
    goal: 'lose',
});

const saving = ref(false);
const errorMessage = ref('');
const savedMessage = ref('');

const isComplete = computed(() =>
    form.height_cm > 0 && form.weight_kg > 0 && form.age > 0
);

const result = computed(() => {
    if (!isComplete.value) {
        return null;
    }

    const bmr =
        10 * form.weight_kg +
        6.25 * form.height_cm -
        5 * form.age +
        (form.sex === 'male' ? 5 : -161);

    const factor = activityLevels.find(
        (level) => level.value === form.activity_level
    )?.factor ?? 1.2;

    const maintenance = bmr * factor;

    // Safe lower limit so the deficit never gets extreme.
    const minimum = form.sex === 'male' ? 1500 : 1200;

    let target = maintenance;

    if (form.goal === 'lose') {
        target = Math.max(maintenance - 500, minimum);
    } else if (form.goal === 'gain') {
        target = maintenance + 300;
    }

    const proteinPerKg = form.goal === 'lose' ? 2.0 : 1.8;
    const protein = proteinPerKg * form.weight_kg;
    const fat = (target * 0.25) / 9;
    const carbs = (target - protein * 4 - fat * 9) / 4;

    const heightM = form.height_cm / 100;

    return {
        bmr: Math.round(bmr),
        maintenance: Math.round(maintenance),
        target: Math.round(target),
        protein: Math.round(protein),
        fat: Math.round(fat),
        carbs: Math.max(Math.round(carbs), 0),
        bmi: (form.weight_kg / (heightM * heightM)).toFixed(1),
    };
});

const goalSummary = computed(() => {
    if (form.goal === 'lose') {
        return 'To lose weight steadily, eat about 500 kcal below maintenance.';
    }

    if (form.goal === 'gain') {
        return 'To gain weight, eat about 300 kcal above maintenance.';
    }

    return 'To maintain your weight, eat around your maintenance calories.';
});

const csrfToken = () => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

const logout = async () => {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': csrfToken(),
            },
        });
    } finally {
        router.push('/');
    }
};

const loadProfile = async () => {
    try {
        const response = await fetch('/api/calorie-profile', {
            headers: { Accept: 'application/json' },
        });

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const saved = data.calorie_profile;

        if (saved) {
            form.height_cm = saved.height_cm;
            form.weight_kg = Number(saved.weight_kg);
            form.age = saved.age;
            form.sex = saved.sex;
            form.activity_level = saved.activity_level;
            form.goal = saved.goal;
        }
    } catch (error) {
        console.error('CALORIE PROFILE ERROR:', error);
    }
};

const save = async () => {
    saving.value = true;
    errorMessage.value = '';
    savedMessage.value = '';

    try {
        const response = await fetch('/api/calorie-profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(form),
        });

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        const data = await response.json();

        if (!response.ok) {
            const firstError = data.errors
                ? Object.values(data.errors)[0][0]
                : data.message;

            errorMessage.value = firstError || 'Could not save your data.';
            return;
        }

        savedMessage.value = 'Your data has been saved.';
    } catch (error) {
        errorMessage.value = 'Could not save your data.';
        console.error('CALORIE SAVE ERROR:', error);
    } finally {
        saving.value = false;
    }
};

onMounted(loadProfile);
</script>