<template>
    <div class="reports-page">
        <header class="profile-navbar">
            <router-link to="/" class="profile-logo">GYM<span>WARRIORS</span></router-link>
            <nav class="profile-nav">
                <router-link to="/profile">PROFILE</router-link>
                <router-link to="/profile#stats">STATS</router-link>
                <router-link to="/profile#membership">MEMBERSHIP</router-link>
                <router-link to="/profile#friends">FRIENDS</router-link>
                <router-link to="/calories">CALORIE TRACKING</router-link>
                <router-link to="/workout">WORKOUTS AND NUTRITION</router-link>
                <router-link to="/reports" class="nav-current">REPORTS</router-link>
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

        <main class="reports-content">
            <div class="reports-title">
                <p class="eyebrow">{{ report?.is_admin ? 'ADMIN DASHBOARD' : 'PERSONAL DASHBOARD' }}</p>
                <h1>PAYMENTS & VISITS</h1>
                <p>Track membership revenue and gym attendance in one place.</p>
            </div>

            <form class="filters" @submit.prevent="loadReport">
                <label>FROM <input v-model="filters.from" type="date"></label>
                <label>TO <input v-model="filters.to" type="date"></label>
                <label>LOCATION
                    <select v-model="filters.location_id">
                        <option value="">All locations</option>
                        <option v-for="location in locations" :key="location.id" :value="location.id">
                            {{ location.name }}
                        </option>
                    </select>
                </label>
                <label v-if="report?.is_admin">USER ID <input v-model="filters.user_id" type="number" min="1" placeholder="All users"></label>
                <button type="submit" :disabled="loading">{{ loading ? 'LOADING...' : 'APPLY FILTERS' }}</button>
            </form>

            <p v-if="error" class="error">{{ error }}</p>

            <section v-if="report" class="summary-grid">
                <article><span>PAYMENTS</span><strong>{{ report.summary.payments_count }}</strong></article>
                <article><span>REVENUE</span><strong>{{ money(report.summary.payments_total) }}</strong></article>
                <article><span>VISITS</span><strong>{{ report.summary.visits_count }}</strong></article>
                <article><span>TOKENS USED</span><strong>{{ report.summary.tokens_used }}</strong></article>
            </section>

            <section v-if="report" class="charts-grid">
                <article class="panel">
                    <h2>PAYMENTS BY DAY</h2>
                    <div v-if="report.payment_trend.length" class="bars">
                        <div v-for="item in report.payment_trend" :key="item.date" class="bar-column">
                            <div class="bar payment-bar" :style="{ height: `${barHeight(item.value, maxPayment)}%` }"></div>
                            <small>{{ shortDate(item.date) }}</small>
                        </div>
                    </div>
                    <p v-else class="empty">No payments in this period.</p>
                </article>
                <article class="panel">
                    <h2>VISITS BY DAY</h2>
                    <div v-if="report.visit_trend.length" class="bars">
                        <div v-for="item in report.visit_trend" :key="item.date" class="bar-column">
                            <div class="bar visit-bar" :style="{ height: `${barHeight(item.value, maxVisits)}%` }"></div>
                            <small>{{ shortDate(item.date) }}</small>
                        </div>
                    </div>
                    <p v-else class="empty">No visits in this period.</p>
                </article>
            </section>

            <section v-if="report" class="tables-grid">
                <article class="panel">
                    <h2>PAYMENT HISTORY</h2>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Date</th><th v-if="report.is_admin">Member</th><th>Plan</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <tr v-for="payment in report.payments" :key="payment.id">
                                    <td>{{ formatDate(payment.payment_date) }}</td>
                                    <td v-if="report.is_admin">{{ payment.user_name }}</td>
                                    <td>{{ payment.plan_name }}</td>
                                    <td>{{ money(payment.amount) }}</td>
                                    <td>{{ payment.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
                <article class="panel">
                    <h2>VISIT HISTORY</h2>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Date</th><th v-if="report.is_admin">Member</th><th>Location</th><th>Token</th></tr></thead>
                            <tbody>
                                <tr v-for="visit in report.visits" :key="visit.id">
                                    <td>{{ formatDate(visit.time) }}</td>
                                    <td v-if="report.is_admin">{{ visit.user_name }}</td>
                                    <td>{{ visit.location_name }}</td>
                                    <td>{{ visit.token_removed ? 'Used' : 'Not used' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const report = ref(null);
const locations = ref([]);
const loading = ref(false);
const error = ref('');
const isMenuOpen = ref(false);
const filters = reactive({ from: '', to: '', location_id: '', user_id: '' });

const maxPayment = computed(() => Math.max(...(report.value?.payment_trend || []).map((item) => item.value), 1));
const maxVisits = computed(() => Math.max(...(report.value?.visit_trend || []).map((item) => item.value), 1));

const loadReport = async () => {
    loading.value = true;
    error.value = '';
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => { if (value) params.set(key, value); });

    try {
        const response = await fetch(`/api/reports?${params}`, { headers: { Accept: 'application/json' } });
        if (response.status === 401) return router.push('/login');
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Could not load reports.');
        report.value = data;
    } catch (requestError) {
        error.value = requestError.message;
    } finally {
        loading.value = false;
    }
};

const loadLocations = async () => {
    const response = await fetch('/api/locations', { headers: { Accept: 'application/json' } });
    if (response.ok) locations.value = await response.json();
};

const money = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value || 0);
const formatDate = (value) => new Date(value).toLocaleString();
const shortDate = (value) => new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
const barHeight = (value, max) => Math.max((value / max) * 100, 4);
const logout = async () => { await fetch('/logout', { method: 'POST', headers: { Accept: 'application/json' } }); router.push('/'); };

onMounted(() => { loadLocations(); loadReport(); });
</script>

<style scoped>
.reports-page { min-height: 100vh; background: #080808; color: #fff; font-family: inherit; }
.eyebrow { color: #e50914; }
.filters button { border: 0; background: #e50914; color: #fff; padding: 12px 18px; font-weight: 800; cursor: pointer; }
.reports-content { width: min(1100px, 90%); margin: 0 auto; padding: 80px 0 120px; }
.reports-title h1 { margin: 8px 0 15px; font-size: clamp(45px, 6vw, 80px); line-height: 0.95; font-weight: 900; }
.reports-title p:last-child { color: #aaa; }
.eyebrow { font-weight: 800; letter-spacing: 3px; font-size: 13px; }
.filters { margin: 42px 0 28px; display: flex; flex-wrap: wrap; gap: 14px; align-items: end; }
.filters label { display: flex; flex-direction: column; gap: 7px; color: #aaa; font-size: 11px; font-weight: 700; letter-spacing: 1px; }
.filters input, .filters select { min-width: 145px; padding: 12px; border: 1px solid #333; background: #111; color: #fff; font-family: inherit; }
.summary-grid, .charts-grid, .tables-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px; }
.summary-grid article, .panel { border: 1px solid #292929; background: #101010; padding: 22px; }
.summary-grid span { color: #999; font-size: 11px; font-weight: 700; letter-spacing: 1px; }
.summary-grid strong { display: block; margin-top: 12px; font-size: 28px; }
.charts-grid, .tables-grid { grid-template-columns: repeat(2, 1fr); }
.panel h2 { margin-bottom: 24px; font-size: 15px; font-weight: 800; letter-spacing: 1px; }
.bars { height: 210px; display: flex; align-items: end; gap: 8px; overflow-x: auto; border-bottom: 1px solid #333; }
.bar-column { min-width: 34px; height: 100%; display: flex; flex-direction: column; justify-content: end; align-items: center; gap: 8px; }
.bar { width: 22px; min-height: 4px; }
.payment-bar { background: #e50914; }
.visit-bar { background: #efefef; }
.bar-column small { color: #888; font-size: 9px; white-space: nowrap; }
.empty { color: #777; }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12px; font-family: inherit; }
th, td { padding: 12px 8px; border-bottom: 1px solid #292929; text-align: left; white-space: nowrap; }
th { color: #999; font-size: 10px; letter-spacing: 1px; }
.error { color: #ff6b6b; margin-bottom: 20px; }
@media (max-width: 800px) { .summary-grid, .charts-grid, .tables-grid { grid-template-columns: 1fr; } .reports-content { width: 90%; padding: 40px 0 80px; } }
</style>
