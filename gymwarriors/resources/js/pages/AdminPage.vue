<template>
    <div class="admin-page">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                GYM<span>WARRIORS</span>
                <small>ADMIN PANEL</small>
            </div>

            <nav class="admin-nav">
                <button
                    v-for="item in menuItems"
                    :key="item.id"
                    :class="{ active: activeSection === item.id }"
                    @click="selectSection(item.id)"
                >
                    {{ item.label }}
                    <span
                        v-if="item.id === 'support' && supportUnreadCount"
                        class="admin-nav-badge"
                        aria-label="Unread support messages"
                    >
                        {{ supportUnreadCount }}
                    </span>
                </button>
            </nav>

            <button class="admin-logout" @click="logout">
                LOG OUT
            </button>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <p class="admin-eyebrow">GYMWARRIORS MANAGEMENT</p>
                    <h1>{{ currentTitle }}</h1>
                </div>

                <div class="admin-account">
                    <span class="admin-status"></span>
                    Administrator
                </div>
            </header>

            <p v-if="error" class="admin-error">{{ error }}</p>

            <section v-if="activeSection === 'dashboard'">
                <div class="admin-welcome">
                    <h2>Welcome to the admin panel</h2>
                    <p>
                        Manage GymWarriors from one place.
                        Choose a section from the menu.
                    </p>
                </div>

                <div class="admin-cards">
                    <article class="admin-card">
                        <span>USERS</span>
                        <strong>{{ stats.users ?? '—' }}</strong>
                        <p>Registered accounts</p>
                    </article>

                    <article class="admin-card">
                        <span>PLANS</span>
                        <strong>{{ stats.plans ?? '—' }}</strong>
                        <p>Available membership plans</p>
                    </article>

                    <article class="admin-card">
                        <span>LOCATIONS</span>
                        <strong>{{ stats.locations ?? '—' }}</strong>
                        <p>Gym locations</p>
                    </article>
                </div>
            </section>

            <section v-else-if="activeSection === 'users'" class="admin-users">
                <div class="admin-users-toolbar">
                    <div>
                        <h2>User management</h2>
                        <p>View account details and manage account access.</p>
                    </div>
                    <form class="admin-users-search" @submit.prevent="loadUsers(1)">
                        <input
                            v-model="userSearch"
                            type="search"
                            placeholder="Search name, email or phone"
                            aria-label="Search users"
                        >
                        <select v-model="userStatus" aria-label="Filter users by status">
                            <option value="all">All accounts</option>
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                        </select>
                        <button type="submit" :disabled="usersLoading">SEARCH</button>
                    </form>
                </div>

                <p v-if="usersError" class="admin-error">{{ usersError }}</p>
                <div class="admin-users-layout">
                    <div class="admin-users-list">
                        <p v-if="usersLoading" class="admin-support-empty">Loading users…</p>
                        <p v-else-if="!users.length" class="admin-support-empty">No users found.</p>
                        <div v-else class="admin-users-table-wrap">
                            <table class="admin-users-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Activity</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users" :key="user.id">
                                        <td>
                                            <strong>{{ user.first_name }} {{ user.last_name }}</strong>
                                            <span>{{ user.email }}</span>
                                            <span>{{ user.phone || 'No phone number' }}</span>
                                        </td>
                                        <td>{{ user.role }}</td>
                                        <td>
                                            <span class="admin-user-status" :class="{ banned: user.is_banned }">
                                                {{ user.is_banned ? 'BANNED' : 'ACTIVE' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ user.plan_purchases_count }} plans ·
                                            {{ user.workouts_count }} workouts ·
                                            {{ user.body_metrics_count }} metrics
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="admin-user-view"
                                                @click="selectUser(user)"
                                            >
                                                VIEW
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="admin-users-pagination">
                                <span>
                                    Page {{ usersPagination.current_page }} of {{ usersPagination.last_page }}
                                    · {{ usersPagination.total }} users
                                </span>
                                <div>
                                    <button
                                        type="button"
                                        :disabled="usersPagination.current_page <= 1 || usersLoading"
                                        @click="loadUsers(usersPagination.current_page - 1)"
                                    >
                                        PREVIOUS
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="usersPagination.current_page >= usersPagination.last_page || usersLoading"
                                        @click="loadUsers(usersPagination.current_page + 1)"
                                    >
                                        NEXT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside v-if="selectedUser" class="admin-user-detail">
                        <header>
                            <div>
                                <p class="admin-eyebrow">ACCOUNT DETAILS</p>
                                <h2>{{ selectedUser.first_name }} {{ selectedUser.last_name }}</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-user-detail-close"
                                aria-label="Close user details"
                                @click="selectedUser = null"
                            >
                                ×
                            </button>
                        </header>

                        <p v-if="userActionError" class="admin-error">{{ userActionError }}</p>
                        <dl class="admin-user-fields">
                            <div><dt>Email</dt><dd>{{ selectedUser.email }}</dd></div>
                            <div><dt>Phone</dt><dd>{{ selectedUser.phone || 'Not provided' }}</dd></div>
                            <div><dt>Role</dt><dd>{{ selectedUser.role }}</dd></div>
                            <div><dt>Joined</dt><dd>{{ formatAdminDate(selectedUser.created_at) }}</dd></div>
                            <div v-if="selectedUser.is_banned"><dt>Banned</dt><dd>{{ formatAdminDate(selectedUser.banned_at) }}</dd></div>
                        </dl>

                        <button
                            v-if="selectedUser.role !== 'admin'"
                            type="button"
                            class="admin-user-ban"
                            :class="{ 'is-banned': selectedUser.is_banned }"
                            :disabled="banUpdating"
                            @click="toggleUserBan"
                        >
                            {{ banUpdating ? 'SAVING…' : selectedUser.is_banned ? 'UNBAN USER' : 'BAN USER' }}
                        </button>
                        <p v-else class="admin-user-protected">Administrator accounts are protected.</p>

                        <section class="admin-user-data">
                            <h3>Calorie profile</h3>
                            <p v-if="!userDetails?.calorie_profile">No calorie profile.</p>
                            <p v-else>
                                {{ userDetails.calorie_profile.height_cm }} cm ·
                                {{ userDetails.calorie_profile.weight_kg }} kg ·
                                age {{ userDetails.calorie_profile.age }} ·
                                goal {{ userDetails.calorie_profile.goal }}
                            </p>
                        </section>

                        <section class="admin-user-data">
                            <h3>Recent body metrics</h3>
                            <p v-if="!userDetails?.body_metrics?.length">No body metrics.</p>
                            <div v-for="metric in userDetails?.body_metrics || []" :key="metric.id">
                                {{ metric.entry_date }} · {{ metric.weight_kg ?? '—' }} kg ·
                                waist {{ metric.waist_cm ?? '—' }} cm
                            </div>
                        </section>

                        <section class="admin-user-data">
                            <h3>Membership history</h3>
                            <p v-if="!userDetails?.plan_purchases?.length">No memberships.</p>
                            <div v-for="purchase in userDetails?.plan_purchases || []" :key="purchase.id">
                                {{ purchase.plan?.name || 'Plan' }} · {{ purchase.status }} ·
                                {{ purchase.start_date }} – {{ purchase.end_date }} ·
                                {{ purchase.checkins_count }} check-ins
                            </div>
                        </section>

                        <section class="admin-user-data">
                            <h3>Recent workouts</h3>
                            <p v-if="!userDetails?.workouts?.length">No workouts.</p>
                            <div v-for="workout in userDetails?.workouts || []" :key="workout.id">
                                {{ workout.name }} · {{ formatAdminDate(workout.started_at) }}
                            </div>
                        </section>

                        <section class="admin-user-data">
                            <h3>Recent payments</h3>
                            <p v-if="!userDetails?.payments?.length">No payments.</p>
                            <div v-for="payment in userDetails?.payments || []" :key="payment.id">
                                {{ payment.amount }} · {{ payment.status }} ·
                                {{ formatAdminDate(payment.payment_date) }}
                            </div>
                        </section>
                    </aside>
                </div>
            </section>

            <section v-else-if="activeSection === 'memberships'" class="admin-plans">
                <div class="admin-plans-heading">
                    <div>
                        <h2>Subscription plans</h2>
                        <p>Edit the public plan details and review membership purchase share.</p>
                    </div>
                    <span>{{ totalPlanPurchases }} total purchases</span>
                </div>

                <p v-if="plansError" class="admin-error">{{ plansError }}</p>
                <p v-else-if="plansLoading" class="admin-support-empty">Loading plans…</p>

                <div v-else class="admin-plans-layout">
                    <div class="admin-plan-list">
                        <article v-for="plan in plans" :key="plan.id" class="admin-plan-card">
                            <header>
                                <div>
                                    <p class="admin-eyebrow">MEMBERSHIP PLAN</p>
                                    <h3>{{ plan.name }}</h3>
                                </div>
                                <strong>€{{ Number(plan.price).toFixed(2) }}</strong>
                            </header>
                            <p class="admin-plan-description">
                                {{ plan.description || 'No description provided.' }}
                            </p>
                            <p class="admin-plan-locations">
                                {{ plan.max_locations === null
                                    ? 'Unlimited gym locations'
                                    : `Up to ${plan.max_locations} gym locations` }}
                            </p>

                            <div class="admin-plan-stat">
                                <div>
                                    <span>Share of all purchases</span>
                                    <strong>{{ plan.purchase_percentage }}%</strong>
                                </div>
                                <div
                                    class="admin-plan-bar"
                                    role="progressbar"
                                    :aria-valuenow="plan.purchase_percentage"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    :aria-label="`${plan.name} purchase share`"
                                >
                                    <span :style="{ width: `${plan.purchase_percentage}%` }"></span>
                                </div>
                                <small>{{ plan.purchases_count }} purchases</small>
                            </div>

                            <button
                                type="button"
                                class="admin-plan-edit-button"
                                @click="editPlan(plan)"
                            >
                                EDIT PLAN
                            </button>
                        </article>
                        <p v-if="!plans.length" class="admin-support-empty">No plans found.</p>
                    </div>

                    <form
                        v-if="planDraft"
                        class="admin-plan-editor"
                        @submit.prevent="savePlan"
                    >
                        <p class="admin-eyebrow">PLAN SETTINGS</p>
                        <h3>Edit {{ planDraft.name }}</h3>
                        <p v-if="planSaveError" class="admin-error">{{ planSaveError }}</p>

                        <label>
                            Plan name
                            <input v-model.trim="planDraft.name" maxlength="50" required>
                        </label>

                        <label>
                            Description
                            <textarea
                                v-model="planDraft.description"
                                maxlength="1000"
                                rows="4"
                                placeholder="Describe what this plan includes"
                            ></textarea>
                        </label>

                        <label>
                            Price (€)
                            <input
                                v-model="planDraft.price"
                                type="number"
                                min="0.01"
                                max="999999.99"
                                step="0.01"
                                required
                            >
                        </label>

                        <label>
                            Maximum gym locations
                            <input
                                v-model="planDraft.max_locations"
                                type="number"
                                min="1"
                                max="100"
                                placeholder="Leave empty for unlimited"
                            >
                        </label>

                        <fieldset class="admin-plan-features">
                            <legend>Tick-marked plan features</legend>
                            <p>These are display labels. Set the actual location limit separately above.</p>
                            <div
                                v-for="(feature, index) in planDraft.features"
                                :key="index"
                                class="admin-plan-feature-row"
                            >
                                <span aria-hidden="true">✓</span>
                                <input
                                    v-model.trim="planDraft.features[index]"
                                    :aria-label="`Plan feature ${index + 1}`"
                                    maxlength="150"
                                    required
                                >
                                <button
                                    type="button"
                                    :aria-label="`Remove plan feature ${index + 1}`"
                                    :disabled="planDraft.features.length <= 1"
                                    @click="removePlanFeature(index)"
                                >
                                    ×
                                </button>
                            </div>
                            <button
                                type="button"
                                class="admin-plan-add-feature"
                                :disabled="planDraft.features.length >= 10"
                                @click="addPlanFeature"
                            >
                                + ADD FEATURE
                            </button>
                        </fieldset>

                        <p class="admin-plan-editor-hint">
                            Leave location limit empty to allow access to all locations.
                        </p>
                        <button type="submit" :disabled="planSaving">
                            {{ planSaving ? 'SAVING…' : 'SAVE CHANGES' }}
                        </button>
                    </form>
                    <div v-else class="admin-plan-editor admin-plan-editor-empty">
                        <h3>Select a plan to edit</h3>
                        <p>Purchase percentages are based on all recorded membership purchases.</p>
                    </div>
                </div>
            </section>

            <section v-else-if="activeSection === 'locations'" class="admin-locations">
                <div class="admin-locations-heading">
                    <div>
                        <h2>Gym locations</h2>
                        <p>Add, update, activate or remove locations shown to members.</p>
                    </div>
                    <button
                        type="button"
                        class="admin-location-add"
                        @click="startNewLocation"
                    >
                        + ADD LOCATION
                    </button>
                </div>

                <p v-if="locationsError" class="admin-error">{{ locationsError }}</p>
                <p v-else-if="locationsLoading" class="admin-support-empty">Loading locations…</p>

                <div v-else class="admin-locations-layout">
                    <div class="admin-location-list">
                        <p v-if="!locations.length" class="admin-support-empty">No locations yet.</p>
                        <article
                            v-for="location in locations"
                            :key="location.id"
                            class="admin-location-card"
                        >
                            <div class="admin-location-card-main">
                                <span class="admin-location-status" :class="{ inactive: !location.active }">
                                    {{ location.active ? 'ACTIVE' : 'INACTIVE' }}
                                </span>
                                <h3>{{ location.name }}</h3>
                                <p>{{ location.address }}, {{ location.city }}</p>
                                <small>
                                    {{ location.purchase_locations_count }} plan assignments ·
                                    {{ location.checkins_count }} check-ins
                                </small>
                            </div>
                            <div class="admin-location-card-actions">
                                <button
                                    type="button"
                                    class="admin-location-edit"
                                    @click="editLocation(location)"
                                >
                                    EDIT
                                </button>
                                <button
                                    type="button"
                                    class="admin-location-delete"
                                    :disabled="location.purchase_locations_count > 0 || location.checkins_count > 0"
                                    :title="location.purchase_locations_count > 0 || location.checkins_count > 0
                                        ? 'Locations used in purchases or check-ins cannot be deleted. Deactivate them instead.'
                                        : 'Delete location'"
                                    @click="deleteLocation(location)"
                                >
                                    DELETE
                                </button>
                            </div>
                        </article>
                    </div>

                    <form
                        class="admin-location-editor"
                        @submit.prevent="saveLocation"
                    >
                        <p class="admin-eyebrow">{{ locationDraft.id ? 'EDIT LOCATION' : 'NEW LOCATION' }}</p>
                        <h3>{{ locationDraft.id ? 'Update location' : 'Add a location' }}</h3>
                        <p v-if="locationFormError" class="admin-error">{{ locationFormError }}</p>

                        <label>
                            Location name
                            <input v-model.trim="locationDraft.name" maxlength="150" required>
                        </label>

                        <label>
                            Address
                            <input v-model.trim="locationDraft.address" maxlength="255" required>
                        </label>

                        <label>
                            City
                            <input v-model.trim="locationDraft.city" maxlength="100" required>
                        </label>

                        <label class="admin-location-active">
                            <input v-model="locationDraft.active" type="checkbox">
                            Active — visible for new membership purchases
                        </label>

                        <p class="admin-location-editor-note">
                            Existing purchase and check-in history is preserved. Used locations can be deactivated but not deleted.
                        </p>

                        <div class="admin-location-editor-actions">
                            <button type="submit" :disabled="locationSaving">
                                {{ locationSaving
                                    ? 'SAVING…'
                                    : locationDraft.id ? 'SAVE CHANGES' : 'CREATE LOCATION' }}
                            </button>
                            <button
                                v-if="locationDraft.id"
                                type="button"
                                class="admin-location-cancel"
                                :disabled="locationSaving"
                                @click="startNewLocation"
                            >
                                CANCEL
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section v-else-if="activeSection === 'trainers'" class="admin-locations">
                <div class="admin-locations-heading">
                    <div>
                        <h2>Trainer management</h2>
                        <p>Add, edit or remove the trainers shown on the workouts page.</p>
                    </div>
                    <button
                        type="button"
                        class="admin-location-add"
                        @click="startNewTrainer"
                    >
                        + ADD TRAINER
                    </button>
                </div>

                <p v-if="trainersError" class="admin-error">{{ trainersError }}</p>
                <p v-else-if="trainersLoading" class="admin-support-empty">Loading trainers…</p>

                <div v-else class="admin-locations-layout">
                    <div class="admin-location-list">
                        <p v-if="!trainers.length" class="admin-support-empty">No trainers yet.</p>
                        <article
                            v-for="trainer in trainers"
                            :key="trainer.id"
                            class="admin-trainer-card"
                        >
                            <img :src="trainer.image" :alt="`${trainer.first_name} ${trainer.last_name}`">
                            <div class="admin-location-card-main">
                                <span class="admin-location-status">{{ trainer.specialty }}</span>
                                <h3>{{ trainer.first_name }} {{ trainer.last_name }}</h3>
                                <p>{{ trainer.email }} · {{ trainer.phone }}</p>
                                <small>{{ trainer.experience }}</small>
                            </div>
                            <div class="admin-location-card-actions">
                                <button
                                    type="button"
                                    class="admin-location-edit"
                                    @click="editTrainer(trainer)"
                                >
                                    EDIT
                                </button>
                                <button
                                    type="button"
                                    class="admin-location-delete"
                                    @click="deleteTrainer(trainer)"
                                >
                                    DELETE
                                </button>
                            </div>
                        </article>
                    </div>

                    <form class="admin-location-editor" @submit.prevent="saveTrainer">
                        <p class="admin-eyebrow">{{ trainerDraft.id ? 'EDIT TRAINER' : 'NEW TRAINER' }}</p>
                        <h3>{{ trainerDraft.id ? 'Update trainer' : 'Add a trainer' }}</h3>
                        <p v-if="trainerFormError" class="admin-error">{{ trainerFormError }}</p>

                        <label>
                            First name
                            <input v-model.trim="trainerDraft.first_name" maxlength="100" required>
                        </label>

                        <label>
                            Last name
                            <input v-model.trim="trainerDraft.last_name" maxlength="100" required>
                        </label>

                        <label>
                            Specialty
                            <input v-model.trim="trainerDraft.specialty" maxlength="150" required>
                        </label>

                        <label>
                            Experience
                            <input v-model.trim="trainerDraft.experience" maxlength="150" required>
                        </label>

                        <label>
                            Description
                            <textarea v-model.trim="trainerDraft.description" maxlength="2000" rows="5" required></textarea>
                        </label>

                        <label>
                            Email
                            <input v-model.trim="trainerDraft.email" type="email" maxlength="255" required>
                        </label>

                        <label>
                            Phone
                            <input v-model.trim="trainerDraft.phone" type="tel" maxlength="50" required>
                        </label>

                        <label>
                            Photo URL or public path
                            <input
                                v-model.trim="trainerDraft.image"
                                type="text"
                                maxlength="2048"
                                placeholder="/images/trainers/trainer-1.jpg"
                                required
                            >
                        </label>
                        <p class="admin-location-editor-note">
                            Use an HTTPS image URL or a path inside the public /images folder.
                        </p>

                        <div class="admin-location-editor-actions">
                            <button type="submit" :disabled="trainerSaving">
                                {{ trainerSaving
                                    ? 'SAVING…'
                                    : trainerDraft.id ? 'SAVE CHANGES' : 'CREATE TRAINER' }}
                            </button>
                            <button
                                v-if="trainerDraft.id"
                                type="button"
                                class="admin-location-cancel"
                                :disabled="trainerSaving"
                                @click="startNewTrainer"
                            >
                                CANCEL
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section v-else-if="activeSection === 'reports'" class="admin-reports">
                <div class="admin-reports-heading">
                    <div>
                        <p class="admin-eyebrow">BUSINESS OVERVIEW</p>
                        <h2>Payments & visits</h2>
                        <p>Review membership revenue and gym attendance across all members.</p>
                    </div>
                </div>

                <form class="admin-report-filters" @submit.prevent="loadAdminReport">
                    <label>
                        Period
                        <select v-model="reportPeriod" @change="changeReportPeriod">
                            <option value="all_time">All time</option>
                            <option value="last_30_days">Last 30 days</option>
                            <option value="last_12_months">Last 12 months</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </label>
                    <label>
                        From
                        <input v-model="reportFilters.from" type="date" :disabled="reportPeriod === 'all_time'">
                    </label>
                    <label>
                        To
                        <input v-model="reportFilters.to" type="date" :disabled="reportPeriod === 'all_time'">
                    </label>
                    <label>
                        Location
                        <select v-model="reportFilters.location_id">
                            <option value="">All locations</option>
                            <option v-for="location in reportLocations" :key="location.id" :value="location.id">
                                {{ location.name }}
                            </option>
                        </select>
                    </label>
                    <label>
                        User ID
                        <input v-model="reportFilters.user_id" type="number" min="1" placeholder="All users">
                    </label>
                    <button type="submit" :disabled="reportLoading">
                        {{ reportLoading ? 'LOADING…' : 'APPLY FILTERS' }}
                    </button>
                </form>

                <p v-if="reportError" class="admin-error">{{ reportError }}</p>
                <p v-else-if="reportLoading && !adminReport" class="admin-support-empty">Loading reports…</p>

                <template v-if="adminReport">
                    <div class="admin-report-summary">
                        <article>
                            <span>MEMBERSHIP SALES</span>
                            <strong>{{ adminReport.summary.payments_count }}</strong>
                        </article>
                        <article>
                            <span>MEMBERSHIP REVENUE</span>
                            <strong>{{ formatReportMoney(adminReport.summary.payments_total) }}</strong>
                        </article>
                        <article>
                            <span>VISITS</span>
                            <strong>{{ adminReport.summary.visits_count }}</strong>
                        </article>
                        <article>
                            <span>TOKENS USED</span>
                            <strong>{{ adminReport.summary.tokens_used }}</strong>
                        </article>
                    </div>

                    <div class="admin-report-charts">
                        <article class="admin-report-panel">
                            <h3>MEMBERSHIP REVENUE BY DAY</h3>
                            <div v-if="adminReport.payment_trend.length" class="admin-report-bars">
                                <div v-for="item in adminReport.payment_trend" :key="item.date" class="admin-report-bar-column">
                                    <div
                                        class="admin-report-bar payment"
                                        :style="{ height: `${reportBarHeight(item.value, maxReportPayment)}%` }"
                                        :title="formatReportMoney(item.value)"
                                    ></div>
                                    <small>{{ formatReportShortDate(item.date) }}</small>
                                </div>
                            </div>
                            <p v-else class="admin-report-empty">No membership sales in this period.</p>
                        </article>
                        <article class="admin-report-panel">
                            <h3>VISITS BY DAY</h3>
                            <div v-if="adminReport.visit_trend.length" class="admin-report-bars">
                                <div v-for="item in adminReport.visit_trend" :key="item.date" class="admin-report-bar-column">
                                    <div
                                        class="admin-report-bar visits"
                                        :style="{ height: `${reportBarHeight(item.value, maxReportVisits)}%` }"
                                        :title="`${item.value} visits`"
                                    ></div>
                                    <small>{{ formatReportShortDate(item.date) }}</small>
                                </div>
                            </div>
                            <p v-else class="admin-report-empty">No visits in this period.</p>
                        </article>
                    </div>

                    <div class="admin-report-tables">
                        <article class="admin-report-panel">
                            <h3>MEMBERSHIP SALES HISTORY</h3>
                            <div class="admin-report-table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Date</th><th>Member</th><th>Plan</th><th>Amount</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="payment in adminReport.payments" :key="payment.id">
                                            <td>{{ formatReportDate(payment.payment_date) }}</td>
                                            <td>{{ payment.user_name }}</td>
                                            <td>{{ payment.plan_name }}</td>
                                            <td>{{ formatReportMoney(payment.amount) }}</td>
                                            <td>{{ payment.status }}</td>
                                        </tr>
                                        <tr v-if="!adminReport.payments.length">
                                            <td colspan="5" class="admin-report-empty">No membership sales found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                        <article class="admin-report-panel">
                            <h3>VISIT HISTORY</h3>
                            <div class="admin-report-table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Date</th><th>Member</th><th>Location</th><th>Token</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="visit in adminReport.visits" :key="visit.id">
                                            <td>{{ formatReportDate(visit.time) }}</td>
                                            <td>{{ visit.user_name }}</td>
                                            <td>{{ visit.location_name }}</td>
                                            <td>{{ visit.token_removed ? 'Used' : 'Not used' }}</td>
                                        </tr>
                                        <tr v-if="!adminReport.visits.length">
                                            <td colspan="4" class="admin-report-empty">No visits found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </template>
            </section>

            <section v-else-if="activeSection === 'support'" class="admin-support">
                <div class="admin-support-inbox">
                    <div class="admin-support-inbox-heading">
                        <h2>Support inbox</h2>
                        <span>{{ supportConversations.length }} conversations</span>
                    </div>

                    <div class="admin-support-filters">
                        <button
                            type="button"
                            :class="{ active: supportFilter === 'open' }"
                            @click="supportFilter = 'open'"
                        >
                            Open ({{ openSupportConversations.length }})
                        </button>
                        <button
                            type="button"
                            :class="{ active: supportFilter === 'closed' }"
                            @click="supportFilter = 'closed'"
                        >
                            History ({{ closedSupportConversations.length }})
                        </button>
                    </div>

                    <p v-if="supportError" class="admin-error">{{ supportError }}</p>
                    <p v-else-if="!filteredSupportConversations.length" class="admin-support-empty">
                        {{ supportFilter === 'open' ? 'No open conversations.' : 'No closed conversations yet.' }}
                    </p>

                    <button
                        v-for="conversation in filteredSupportConversations"
                        :key="conversation.id"
                        type="button"
                        class="admin-support-thread"
                        :class="{ active: selectedSupportConversation?.id === conversation.id }"
                        @click="selectSupportConversation(conversation)"
                    >
                        <span class="admin-support-thread-top">
                            <strong>{{ conversation.user.name }}</strong>
                            <span v-if="conversation.unread_count" class="admin-support-unread">
                                {{ conversation.unread_count }}
                            </span>
                        </span>
                        <span class="admin-support-thread-email">{{ conversation.user.email }}</span>
                        <span class="admin-support-thread-preview">
                            {{ conversation.last_message?.body ?? 'No messages' }}
                        </span>
                        <span class="admin-support-status" :class="`status-${conversation.status}`">
                            {{ conversation.status === 'open' ? 'OPEN' : 'CLOSED' }}
                        </span>
                    </button>
                </div>

                <div class="admin-support-chat">
                    <template v-if="selectedSupportConversation">
                        <header class="admin-support-chat-header">
                            <div>
                                <h2>{{ selectedSupportConversation.user.name }}</h2>
                                <p>{{ selectedSupportConversation.user.email }}</p>
                            </div>
                            <div class="admin-support-chat-actions">
                                <span class="admin-support-status" :class="`status-${selectedSupportConversation.status}`">
                                    {{ selectedSupportConversation.status === 'open' ? 'OPEN' : 'CLOSED' }}
                                </span>
                                <button
                                    v-if="selectedSupportConversation.status === 'open'"
                                    type="button"
                                    class="admin-support-close"
                                    :disabled="closingConversation"
                                    @click="closeSupportConversation"
                                >
                                    {{ closingConversation ? 'CLOSING…' : 'CLOSE CHAT' }}
                                </button>
                                <span v-if="supportLoading">Refreshing…</span>
                            </div>
                        </header>

                        <div class="admin-support-messages" aria-live="polite">
                            <article
                                v-for="message in supportMessages"
                                :key="message.id"
                                class="admin-support-message"
                                :class="{ 'from-support': message.is_support }"
                            >
                                <div>
                                    <strong>{{ message.is_support ? 'SUPPORT' : 'USER' }}</strong>
                                    <time :datetime="message.created_at">
                                        {{ formatSupportTime(message.created_at) }}
                                    </time>
                                </div>
                                <p>{{ message.body }}</p>
                            </article>
                        </div>

                        <form
                            v-if="selectedSupportConversation.status === 'open'"
                            class="admin-support-compose"
                            @submit.prevent="sendSupportReply"
                        >
                            <textarea
                                v-model="supportDraft"
                                maxlength="5000"
                                rows="3"
                                placeholder="Write a reply..."
                                aria-label="Support reply"
                                required
                            ></textarea>
                            <button type="submit" :disabled="replySending || !supportDraft.trim()">
                                {{ replySending ? 'SENDING…' : 'SEND REPLY' }}
                            </button>
                        </form>
                        <p v-else class="admin-support-closed-note">
                            This conversation is closed and saved in history.
                        </p>
                    </template>
                    <div v-else class="admin-support-no-selection">
                        <h2>Select a conversation</h2>
                        <p>Incoming user messages will appear in this inbox.</p>
                    </div>
                </div>
            </section>

            <section v-else class="admin-placeholder">
                <h2>{{ currentTitle }}</h2>
                <p>
                    This section is ready for the next development step.
                </p>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import '../../css/admin.css';

const router = useRouter();

const activeSection = ref('dashboard');
const error = ref('');
const stats = ref({
    users: null,
    plans: null,
    locations: null,
});
const supportConversations = ref([]);
const selectedSupportConversation = ref(null);
const supportMessages = ref([]);
const supportDraft = ref('');
const supportError = ref('');
const supportLoading = ref(false);
const replySending = ref(false);
const closingConversation = ref(false);
const supportFilter = ref('open');
const users = ref([]);
const userSearch = ref('');
const userStatus = ref('all');
const usersLoading = ref(false);
const usersError = ref('');
const userActionError = ref('');
const usersPagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
});
const selectedUser = ref(null);
const userDetails = ref(null);
const banUpdating = ref(false);
const plans = ref([]);
const totalPlanPurchases = ref(0);
const plansLoading = ref(false);
const plansError = ref('');
const planSaveError = ref('');
const planDraft = ref(null);
const planSaving = ref(false);
const locations = ref([]);
const locationsLoading = ref(false);
const locationsError = ref('');
const locationFormError = ref('');
const locationSaving = ref(false);
const locationDraft = ref(createEmptyLocation());
const trainers = ref([]);
const trainersLoading = ref(false);
const trainersError = ref('');
const trainerFormError = ref('');
const trainerSaving = ref(false);
const trainerDraft = ref(createEmptyTrainer());
const reportLocations = ref([]);
const reportLoading = ref(false);
const reportError = ref('');
const adminReport = ref(null);
const reportFilters = ref({
    from: '',
    to: '',
    location_id: '',
    user_id: '',
});
const reportPeriod = ref('all_time');
let supportRefreshTimer;

const menuItems = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'users', label: 'Users' },
    { id: 'memberships', label: 'Memberships' },
    { id: 'locations', label: 'Locations' },
    { id: 'trainers', label: 'Trainers' },
    { id: 'reports', label: 'Reports' },
    { id: 'support', label: 'Support' },
];

const supportUnreadCount = computed(() => supportConversations.value
    .reduce((total, conversation) => total + conversation.unread_count, 0));
const openSupportConversations = computed(() => supportConversations.value
    .filter(conversation => conversation.status === 'open'));
const closedSupportConversations = computed(() => supportConversations.value
    .filter(conversation => conversation.status === 'closed'));
const filteredSupportConversations = computed(() => supportFilter.value === 'open'
    ? openSupportConversations.value
    : closedSupportConversations.value);
const maxReportPayment = computed(() => Math.max(
    ...(adminReport.value?.payment_trend ?? []).map(item => item.value),
    1,
));
const maxReportVisits = computed(() => Math.max(
    ...(adminReport.value?.visit_trend ?? []).map(item => item.value),
    1,
));

const currentTitle = computed(() => {
    return menuItems.find(item => item.id === activeSection.value)?.label
        ?? 'Dashboard';
});

onMounted(async () => {
    try {
        const response = await fetch('/api/admin/dashboard', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.status === 401 || response.status === 403) {
            await router.replace('/login');
            return;
        }

        if (!response.ok) {
            throw new Error('Could not load administrator data.');
        }

        stats.value = await response.json();
    } catch (err) {
        error.value = 'Could not load dashboard data. Please refresh the page.';
    }

    await loadSupportConversations();
    supportRefreshTimer = window.setInterval(refreshSupport, 10000);
});

onUnmounted(() => {
    window.clearInterval(supportRefreshTimer);
});

async function loadSupportConversations() {
    try {
        const response = await fetch('/api/admin/support-conversations', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Could not load support conversations.');
        }

        const data = await response.json();
        supportConversations.value = data.conversations;
        if (selectedSupportConversation.value) {
            selectedSupportConversation.value = data.conversations.find(
                conversation => conversation.id === selectedSupportConversation.value.id,
            ) ?? selectedSupportConversation.value;
        }
        supportError.value = '';
    } catch (err) {
        supportError.value = 'Could not load support inbox. Please refresh the page.';
    }
}

async function refreshSupport() {
    await loadSupportConversations();

    if (selectedSupportConversation.value) {
        await loadSupportMessages(selectedSupportConversation.value.id);
    }
}

async function selectSupportConversation(conversation) {
    selectedSupportConversation.value = conversation;
    supportMessages.value = [];
    supportDraft.value = '';
    await loadSupportMessages(conversation.id);
    await loadSupportConversations();
}

async function loadSupportMessages(conversationId) {
    supportLoading.value = true;

    try {
        const response = await fetch(
            `/api/admin/support-conversations/${conversationId}/messages`,
            {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            throw new Error('Could not load support messages.');
        }

        const data = await response.json();
        if (selectedSupportConversation.value?.id === conversationId) {
            supportMessages.value = data.messages;
        }
        supportError.value = '';
    } catch (err) {
        supportError.value = 'Could not load this conversation. Please try again.';
    } finally {
        supportLoading.value = false;
    }
}

async function closeSupportConversation() {
    const conversation = selectedSupportConversation.value;
    if (!conversation || conversation.status !== 'open' || closingConversation.value) {
        return;
    }

    closingConversation.value = true;

    try {
        const response = await fetch(
            `/api/admin/support-conversations/${conversation.id}/close`,
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            throw new Error('Could not close support conversation.');
        }

        const data = await response.json();
        selectedSupportConversation.value = {
            ...conversation,
            ...data.conversation,
        };
        supportFilter.value = 'closed';
        supportError.value = '';
        await loadSupportConversations();
    } catch (err) {
        supportError.value = 'Could not close this conversation. Please try again.';
    } finally {
        closingConversation.value = false;
    }
}

async function sendSupportReply() {
    const conversation = selectedSupportConversation.value;
    const body = supportDraft.value.trim();

    if (!conversation || !body || replySending.value) {
        return;
    }

    replySending.value = true;

    try {
        const response = await fetch(
            `/api/admin/support-conversations/${conversation.id}/messages`,
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ body }),
            },
        );

        if (!response.ok) {
            throw new Error('Could not send support reply.');
        }

        const data = await response.json();
        supportMessages.value.push(data.message);
        supportDraft.value = '';
        supportError.value = '';
        await loadSupportConversations();
    } catch (err) {
        supportError.value = 'Could not send your reply. Please try again.';
    } finally {
        replySending.value = false;
    }
}

function formatSupportTime(value) {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function selectSection(section) {
    activeSection.value = section;

    if (section === 'users' && !users.value.length) {
        loadUsers();
    }
    if (section === 'memberships' && !plans.value.length) {
        loadPlans();
    }
    if (section === 'locations' && !locations.value.length) {
        loadLocations();
    }
    if (section === 'trainers' && !trainers.value.length) {
        loadTrainers();
    }
    if (section === 'reports' && !adminReport.value) {
        loadAdminReportsData();
    }
}

async function loadAdminReportsData() {
    const [locationsResult, reportResult] = await Promise.allSettled([
        fetch('/api/admin/locations', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        }),
        loadAdminReport(),
    ]);

    if (locationsResult.status === 'fulfilled' && locationsResult.value.ok) {
        reportLocations.value = (await locationsResult.value.json()).locations;
    } else {
        reportError.value = 'Could not load report locations. Please try again.';
    }

    if (reportResult.status === 'rejected') {
        reportError.value = 'Could not load reports. Please try again.';
    }
}

function changeReportPeriod() {
    if (reportPeriod.value === 'all_time') {
        reportFilters.value.from = '';
        reportFilters.value.to = '';
        loadAdminReport();
        return;
    }

    if (reportPeriod.value === 'custom') {
        return;
    }

    const today = new Date();
    const toDate = formatReportInputDate(today);
    const fromDate = new Date(today);
    if (reportPeriod.value === 'last_30_days') {
        fromDate.setDate(fromDate.getDate() - 29);
    } else {
        fromDate.setFullYear(fromDate.getFullYear() - 1);
        fromDate.setDate(fromDate.getDate() + 1);
    }

    reportFilters.value.from = formatReportInputDate(fromDate);
    reportFilters.value.to = toDate;
    loadAdminReport();
}

async function loadAdminReport() {
    reportLoading.value = true;
    reportError.value = '';
    const params = new URLSearchParams();
    Object.entries(reportFilters.value).forEach(([key, value]) => {
        if (value) {
            params.set(key, value);
        }
    });

    try {
        const response = await fetch(`/api/admin/reports?${params}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.status === 401 || response.status === 403) {
            await router.replace('/login');
            return;
        }

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Could not load reports.');
        }

        adminReport.value = data;
    } catch (loadError) {
        reportError.value = loadError instanceof Error
            ? loadError.message
            : 'Could not load reports. Please try again.';
    } finally {
        reportLoading.value = false;
    }
}

function formatReportMoney(value) {
    return new Intl.NumberFormat('en-IE', {
        style: 'currency',
        currency: 'EUR',
    }).format(value || 0);
}

function formatReportDate(value) {
    return new Date(value).toLocaleString();
}

function formatReportInputDate(value) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function formatReportShortDate(value) {
    return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
}

function reportBarHeight(value, max) {
    return Math.max((value / max) * 100, 4);
}

function createEmptyTrainer() {
    return {
        id: null,
        first_name: '',
        last_name: '',
        specialty: '',
        experience: '',
        description: '',
        email: '',
        phone: '',
        image: '',
    };
}

async function loadTrainers() {
    trainersLoading.value = true;
    trainersError.value = '';

    try {
        const response = await fetch('/api/admin/trainers', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Could not load trainers.');
        }

        trainers.value = (await response.json()).trainers;
    } catch (loadError) {
        trainersError.value = 'Could not load trainers. Please try again.';
    } finally {
        trainersLoading.value = false;
    }
}

function startNewTrainer() {
    trainerDraft.value = createEmptyTrainer();
    trainerFormError.value = '';
}

function editTrainer(trainer) {
    trainerDraft.value = { ...trainer };
    trainerFormError.value = '';
}

async function saveTrainer() {
    if (trainerSaving.value) {
        return;
    }

    trainerSaving.value = true;
    trainerFormError.value = '';
    const isEditing = Boolean(trainerDraft.value.id);

    try {
        const response = await fetch(
            isEditing
                ? `/api/admin/trainers/${trainerDraft.value.id}`
                : '/api/admin/trainers',
            {
                method: isEditing ? 'PUT' : 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    first_name: trainerDraft.value.first_name,
                    last_name: trainerDraft.value.last_name,
                    specialty: trainerDraft.value.specialty,
                    experience: trainerDraft.value.experience,
                    description: trainerDraft.value.description,
                    email: trainerDraft.value.email,
                    phone: trainerDraft.value.phone,
                    image: trainerDraft.value.image,
                }),
            },
        );

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.errors
                ? Object.values(data.errors).flat().join(' ')
                : data?.message || 'Could not save trainer.');
        }

        await loadTrainers();
        startNewTrainer();
    } catch (saveError) {
        trainerFormError.value = saveError instanceof Error
            ? saveError.message
            : 'Could not save trainer. Please try again.';
    } finally {
        trainerSaving.value = false;
    }
}

async function deleteTrainer(trainer) {
    if (!window.confirm(`Delete ${trainer.first_name} ${trainer.last_name}? This cannot be undone.`)) {
        return;
    }

    trainersError.value = '';

    try {
        const response = await fetch(`/api/admin/trainers/${trainer.id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.message || 'Could not delete trainer.');
        }

        if (trainerDraft.value.id === trainer.id) {
            startNewTrainer();
        }
        await loadTrainers();
    } catch (deleteError) {
        trainersError.value = deleteError instanceof Error
            ? deleteError.message
            : 'Could not delete trainer. Please try again.';
    }
}

function createEmptyLocation() {
    return {
        id: null,
        name: '',
        address: '',
        city: '',
        active: true,
    };
}

async function loadLocations() {
    locationsLoading.value = true;
    locationsError.value = '';

    try {
        const response = await fetch('/api/admin/locations', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Could not load locations.');
        }

        const data = await response.json();
        locations.value = data.locations;
        stats.value.locations = data.locations.length;
    } catch (loadError) {
        locationsError.value = 'Could not load locations. Please try again.';
    } finally {
        locationsLoading.value = false;
    }
}

function startNewLocation() {
    locationDraft.value = createEmptyLocation();
    locationFormError.value = '';
}

function editLocation(location) {
    locationDraft.value = { ...location };
    locationFormError.value = '';
}

async function saveLocation() {
    if (locationSaving.value) {
        return;
    }

    locationSaving.value = true;
    locationFormError.value = '';
    const isEditing = Boolean(locationDraft.value.id);

    try {
        const response = await fetch(
            isEditing
                ? `/api/admin/locations/${locationDraft.value.id}`
                : '/api/admin/locations',
            {
                method: isEditing ? 'PUT' : 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name: locationDraft.value.name,
                    address: locationDraft.value.address,
                    city: locationDraft.value.city,
                    active: locationDraft.value.active,
                }),
            },
        );

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.errors
                ? Object.values(data.errors).flat().join(' ')
                : data?.message || 'Could not save location.');
        }

        await loadLocations();
        startNewLocation();
    } catch (saveError) {
        locationFormError.value = saveError instanceof Error
            ? saveError.message
            : 'Could not save location. Please try again.';
    } finally {
        locationSaving.value = false;
    }
}

async function deleteLocation(location) {
    if (location.purchase_locations_count > 0 || location.checkins_count > 0) {
        return;
    }

    if (!window.confirm(`Delete ${location.name}? This cannot be undone.`)) {
        return;
    }

    locationsError.value = '';

    try {
        const response = await fetch(`/api/admin/locations/${location.id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.message || 'Could not delete location.');
        }

        if (locationDraft.value.id === location.id) {
            startNewLocation();
        }
        await loadLocations();
    } catch (deleteError) {
        locationsError.value = deleteError instanceof Error
            ? deleteError.message
            : 'Could not delete location. Please try again.';
    }
}

async function loadPlans() {
    plansLoading.value = true;
    plansError.value = '';

    try {
        const response = await fetch('/api/admin/plans', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Could not load subscription plans.');
        }

        const data = await response.json();
        plans.value = data.plans;
        totalPlanPurchases.value = data.total_purchases;
        if (planDraft.value) {
            planDraft.value = plans.value.some(plan => plan.id === planDraft.value.id)
                ? planDraft.value
                : null;
        }
    } catch (loadError) {
        plansError.value = 'Could not load plans and purchase statistics. Please try again.';
    } finally {
        plansLoading.value = false;
    }
}

function editPlan(plan) {
    planDraft.value = {
        ...plan,
        max_locations: plan.max_locations ?? '',
        features: plan.features?.length
            ? [...plan.features]
            : [
                '30 DAYS MEMBERSHIP',
                '30 TOKENS',
                plan.max_locations === null
                    ? 'UNLIMITED LOCATIONS'
                    : `ACCESS TO ${plan.max_locations} LOCATIONS`,
                'GYM CHECK-IN',
            ],
    };
    planSaveError.value = '';
}

function addPlanFeature() {
    if (planDraft.value && planDraft.value.features.length < 10) {
        planDraft.value.features.push('');
    }
}

function removePlanFeature(index) {
    if (planDraft.value && planDraft.value.features.length > 1) {
        planDraft.value.features.splice(index, 1);
    }
}

async function savePlan() {
    if (!planDraft.value || planSaving.value) {
        return;
    }

    planSaving.value = true;
    planSaveError.value = '';

    try {
        const response = await fetch(`/api/admin/plans/${planDraft.value.id}`, {
            method: 'PUT',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                name: planDraft.value.name,
                description: planDraft.value.description || null,
                price: planDraft.value.price,
                max_locations: planDraft.value.max_locations === ''
                    ? null
                    : Number(planDraft.value.max_locations),
                features: planDraft.value.features.map(feature => feature.trim()),
            }),
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.errors
                ? Object.values(data.errors).flat().join(' ')
                : data?.message || 'Could not save subscription plan.');
        }

        await loadPlans();
        const savedPlan = plans.value.find(plan => plan.id === planDraft.value?.id);
        if (savedPlan) {
            editPlan(savedPlan);
        }
    } catch (saveError) {
        planSaveError.value = saveError instanceof Error
            ? saveError.message
            : 'Could not save plan. Please try again.';
    } finally {
        planSaving.value = false;
    }
}

async function loadUsers(page = 1) {
    usersLoading.value = true;
    usersError.value = '';

    const params = new URLSearchParams({
        page: String(page),
        status: userStatus.value,
    });
    if (userSearch.value.trim()) {
        params.set('search', userSearch.value.trim());
    }

    try {
        const response = await fetch(`/api/admin/users?${params}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Could not load users.');
        }

        const data = await response.json();
        users.value = data.data;
        usersPagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        };
        if (selectedUser.value) {
            selectedUser.value = users.value.find(user => user.id === selectedUser.value.id)
                ?? selectedUser.value;
        }
    } catch (loadError) {
        usersError.value = 'Could not load user accounts. Please try again.';
    } finally {
        usersLoading.value = false;
    }
}

async function selectUser(user) {
    selectedUser.value = user;
    userDetails.value = null;
    userActionError.value = '';

    try {
        const response = await fetch(`/api/admin/users/${user.id}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Could not load user details.');
        }

        userDetails.value = await response.json();
        selectedUser.value = {
            ...user,
            ...userDetails.value.user,
        };
    } catch (loadError) {
        userActionError.value = 'Could not load user details. Please try again.';
    }
}

async function toggleUserBan() {
    if (!selectedUser.value || selectedUser.value.role === 'admin' || banUpdating.value) {
        return;
    }

    banUpdating.value = true;
    userActionError.value = '';

    try {
        const response = await fetch(`/api/admin/users/${selectedUser.value.id}/ban`, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ is_banned: !selectedUser.value.is_banned }),
        });

        if (!response.ok) {
            throw new Error('Could not update user access.');
        }

        const data = await response.json();
        selectedUser.value = {
            ...selectedUser.value,
            ...data.user,
        };
        if (userDetails.value) {
            userDetails.value.user = selectedUser.value;
        }
        users.value = users.value.map(user => user.id === selectedUser.value.id
            ? { ...user, ...data.user }
            : user);
    } catch (updateError) {
        userActionError.value = 'Could not update account status. Please try again.';
    } finally {
        banUpdating.value = false;
    }
}

function formatAdminDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

async function logout() {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
        });
    } finally {
        await router.replace('/login');
    }
}
</script>