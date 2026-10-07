<template>
    <div class="plans-page">
        <header class="plans-navbar">
            <router-link to="/profile" class="plans-logo">
                GYM<span>WARRIORS</span>
            </router-link>

            <div class="plans-nav-actions">
                <router-link to="/profile" class="plans-profile-link">
                    PROFILE
                </router-link>

                <button class="plans-logout-button" @click="logout">
                    LOG OUT
                </button>
            </div>
        </header>

        <main class="plans-container">
            <section class="plans-hero">
                <p class="plans-label">GYM WARRIORS MEMBERSHIP</p>

                <h1>
                    CHOOSE YOUR<br>
                    <span>PLAN.</span>
                </h1>

                <p class="plans-description">
                    Choose the membership that fits your training journey.
                    Get access to Gym Warriors locations and start your progress.
                </p>
            </section>

            <p v-if="loading" class="plans-status">
                LOADING PLANS...
            </p>

            <p v-if="error" class="plans-error">
                {{ error }}
            </p>

            <section v-if="!loading && !error" class="plans-grid">
                <article
                    v-for="(plan, index) in plans"
                    :key="plan.id"
                    class="plan-card"
                    :class="{
                        'plan-card-pro': plan.name.toLowerCase() === 'pro'
                    }"
                >
                    <div
                        v-if="plan.name.toLowerCase() === 'pro'"
                        class="plan-badge"
                    >
                        RECOMMENDED
                    </div>

                    <div class="plan-number">
                        {{ String(index + 1).padStart(2, '0') }}
                    </div>

                    <p class="plan-type">
                        {{
                            plan.name.toLowerCase() === 'pro'
                                ? 'PREMIUM MEMBERSHIP'
                                : 'STANDARD MEMBERSHIP'
                        }}
                    </p>

                    <h2>{{ plan.name }}</h2>

                    <div class="plan-price">
                        <strong>
                            €{{ Number(plan.price).toFixed(2) }}
                        </strong>

                        <span>/ 30 DAYS</span>
                    </div>

                    <div class="plan-divider"></div>

                    <ul class="plan-features">
                        <li>30 DAYS MEMBERSHIP</li>
                        <li>30 TOKENS</li>

                        <li v-if="plan.max_locations">
                            ACCESS TO {{ plan.max_locations }} LOCATIONS
                        </li>

                        <li v-else>
                            UNLIMITED LOCATIONS
                        </li>

                        <li>GYM CHECK-IN</li>
                    </ul>

                    <button
                        class="plan-button"
                        @click="choosePlan(plan)"
                    >
                        CHOOSE PLAN
                    </button>
                </article>
            </section>

            <!-- LOCATION MODAL -->
<div
    v-if="showLocationModal"
    class="location-modal-overlay"
    @click.self="closeLocationModal"
>
    <div class="location-modal">

        <button
            class="location-modal-close"
            @click="closeLocationModal"
        >
            ×
        </button>

        <div class="location-modal-header">
            <p class="location-modal-label">
                LOCATION ACCESS
            </p>

            <h2>
                CHOOSE YOUR<br>
                <span>LOCATIONS.</span>
            </h2>

            <p class="location-modal-description">
                Choose 1 or 2 Gym Warriors locations for your
                {{ selectedPlan?.name }} membership.
            </p>

            <div class="location-selected">
                <span>SELECTED</span>

                <strong>
                    {{ selectedLocationIds.length }}
                </strong>

                <span>
                    / {{ selectedPlan?.max_locations }}
                </span>
            </div>
        </div>


        <div class="locations-list">

            <button
                v-for="location in locations"
                :key="location.id"
                type="button"
                class="location-option"
                :class="{
                    selected: selectedLocationIds.includes(location.id)
                }"
                @click="toggleLocation(location.id)"
            >

                <div class="location-image">
                    <div class="location-image-overlay"></div>

                    <div class="location-number">
                        {{ String(location.id).padStart(2, '0') }}
                    </div>

                    <div class="location-checkbox">
                        <span
                            v-if="selectedLocationIds.includes(location.id)"
                        >
                            ✓
                        </span>
                    </div>
                </div>


                <div class="location-content">

                    <div class="location-pin">
                        ●
                    </div>

                    <div class="location-info">
                        <strong>
                            {{ location.name }}
                        </strong>

                        <span>
                            {{ location.address }}, {{ location.city }}
                        </span>
                    </div>

                </div>

            </button>

        </div>


        <p
            v-if="locationError"
            class="plans-error location-modal-error"
        >
            {{ locationError }}
        </p>


        <button
            class="location-confirm-button"
            :class="{
                active: selectedLocationIds.length > 0
            }"
            :disabled="selectedLocationIds.length < 1"
            @click="confirmPurchase"
        >
            CONTINUE
        </button>

    </div>
</div>

            <router-link to="/profile" class="plans-back">
                ← BACK TO PROFILE
            </router-link>
        </main>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const plans = ref([]);
const locations = ref([]);

const loading = ref(true);
const error = ref('');

const showLocationModal = ref(false);
const selectedPlan = ref(null);
const selectedLocationIds = ref([]);
const locationError = ref('');

const loadPlans = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/plans', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        if (!response.ok) {
            throw new Error('Failed to load plans.');
        }

        plans.value = await response.json();
    } catch (err) {
        error.value = 'Could not load membership plans.';
    } finally {
        loading.value = false;
    }
};

const loadLocations = async () => {
    try {
        const response = await fetch('/api/locations', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        if (!response.ok) {
            throw new Error('Failed to load locations.');
        }

        locations.value = await response.json();
    } catch (err) {
        locationError.value = 'Could not load gym locations.';
    }
};

const choosePlan = async (plan) => {
    error.value = '';

    // Pro doesn't need location selection.
    if (plan.max_locations === null) {
        await purchasePlan(plan.id, []);
        return;
    }

    selectedPlan.value = plan;
    selectedLocationIds.value = [];
    locationError.value = '';

    await loadLocations();

    if (locationError.value) {
        return;
    }

    showLocationModal.value = true;
};

const toggleLocation = (locationId) => {
    const maxLocations = selectedPlan.value?.max_locations ?? 0;

    if (selectedLocationIds.value.includes(locationId)) {
        selectedLocationIds.value =
            selectedLocationIds.value.filter(
                id => id !== locationId
            );

        return;
    }

    if (selectedLocationIds.value.length >= maxLocations) {
        locationError.value =
            `Vari izvēlēties ne vairāk kā ${maxLocations} lokācijas.`;

        return;
    }

    locationError.value = '';

    selectedLocationIds.value.push(locationId);
};

const closeLocationModal = () => {
    showLocationModal.value = false;
    selectedPlan.value = null;
    selectedLocationIds.value = [];
    locationError.value = '';
};

const confirmPurchase = async () => {
    if (!selectedPlan.value) {
        return;
    }

    if (selectedLocationIds.value.length < 1) {
        locationError.value =
            'Izvēlies vismaz vienu lokāciju.';

        return;
    }

    if (
        selectedLocationIds.value.length >
        selectedPlan.value.max_locations
    ) {
        locationError.value =
            `Vari izvēlēties ne vairāk kā ${selectedPlan.value.max_locations} lokācijas.`;

        return;
    }

    await purchasePlan(
        selectedPlan.value.id,
        selectedLocationIds.value
    );
};

const purchasePlan = async (planId, locationIds) => {
    error.value = '';

    try {
        const response = await fetch('/api/plans/purchase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                plan_id: planId,
                location_ids: locationIds,
            }),
        });

        const data = await response.json();

        if (response.status === 401) {
            router.push('/login');
            return;
        }

        if (!response.ok) {
            error.value =
                data.message || 'Plan purchase failed.';

            return;
        }

        showLocationModal.value = false;

        alert('Plan successfully purchased!');

        router.push('/profile');
    } catch (err) {
        error.value =
            'Could not purchase the plan.';
    }
};

const logout = async () => {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
        });
    } finally {
        router.push('/');
    }
};

onMounted(() => {
    loadPlans();
});
</script>