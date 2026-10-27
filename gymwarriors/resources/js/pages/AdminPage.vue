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
                    @click="activeSection = item.id"
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
let supportRefreshTimer;

const menuItems = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'users', label: 'Users' },
    { id: 'memberships', label: 'Memberships' },
    { id: 'locations', label: 'Locations' },
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