<template>
    <main class="support-page">
        <header class="support-header">
            <router-link to="/" class="support-logo">
                GYM<span>WARRIORS</span>
            </router-link>

            <nav>
                <router-link to="/profile">PROFILS</router-link>
                <router-link to="/">SĀKUMS</router-link>
            </nav>
        </header>

        <section class="support-panel">
            <div class="support-heading">
                <div>
                    <p class="support-eyebrow">GYMWARRIORS PALĪDZĪBA</p>
                    <h1>Atbalsta čats</h1>
                    <p>Uzdod jautājumu — mūsu atbalsta komanda tev atbildēs šeit.</p>
                </div>
                <span class="support-online"><i></i> SARUNA AR ATBALSTU</span>
            </div>

            <p v-if="error" class="support-error" role="alert">{{ error }}</p>

            <div class="support-chat-layout">
                <aside class="support-history">
                    <div class="support-history-heading">
                        <strong>MANAS SARUNAS</strong>
                        <button
                            v-if="!hasOpenConversation"
                            type="button"
                            @click="startConversation"
                        >
                            JAUNA SARUNA
                        </button>
                    </div>

                    <p v-if="!conversations.length && !loading" class="support-history-empty">
                        Sarunu vēsture ir tukša.
                    </p>

                    <button
                        v-for="conversation in conversations"
                        :key="conversation.id"
                        type="button"
                        class="support-history-item"
                        :class="{ active: selectedConversation?.id === conversation.id }"
                        @click="selectConversation(conversation)"
                    >
                        <span class="support-history-item-top">
                            <strong>{{ conversation.status === 'open' ? 'Atvērta saruna' : 'Slēgta saruna' }}</strong>
                            <span :class="`history-status-${conversation.status}`">
                                {{ conversation.status === 'open' ? 'AKTĪVA' : 'VĒSTURE' }}
                            </span>
                        </span>
                        <span class="support-history-preview">
                            {{ conversation.last_message?.body ?? 'Vēl nav ziņojumu' }}
                        </span>
                    </button>
                </aside>

                <div class="support-conversation">
                    <div
                        v-if="!selectedConversation && !loading"
                        class="support-empty"
                    >
                        <strong>Sāc sarunu ar atbalsta komandu</strong>
                        <span>Uzdod jautājumu, un mūsu komanda tev atbildēs šeit.</span>
                        <button type="button" @click="startConversation">SĀKT SARUNU</button>
                    </div>

                    <template v-else-if="selectedConversation">
                        <div
                            ref="messageList"
                            class="support-messages"
                            aria-live="polite"
                            aria-label="Sarunas ziņojumi"
                        >
                            <div v-if="!messages.length && !loading" class="support-empty">
                                <strong>{{ selectedConversation.status === 'open' ? 'Sāc sarunu ar atbalsta komandu' : 'Šī saruna tika aizvērta bez ziņojumiem' }}</strong>
                                <span>Ziņojumu vēsture tiks saglabāta arī pēc sarunas aizvēršanas.</span>
                            </div>

                            <article
                                v-for="message in messages"
                                :key="message.id"
                                class="support-message"
                                :class="{ 'support-message-own': !message.is_support }"
                            >
                                <div class="support-message-meta">
                                    <strong>{{ message.is_support ? 'ATBALSTA KOMANDA' : 'TU' }}</strong>
                                    <time :datetime="message.created_at">{{ formatTime(message.created_at) }}</time>
                                </div>
                                <p>{{ message.body }}</p>
                            </article>
                        </div>

                        <form
                            v-if="selectedConversation.status === 'open'"
                            class="support-compose"
                            @submit.prevent="sendMessage"
                        >
                            <label class="sr-only" for="support-message">Tavs ziņojums</label>
                            <textarea
                                id="support-message"
                                v-model="draft"
                                maxlength="5000"
                                rows="3"
                                placeholder="Ieraksti savu jautājumu..."
                                :disabled="sending"
                                required
                            ></textarea>
                            <div class="support-compose-footer">
                                <span>{{ draft.length }}/5000</span>
                                <button type="submit" :disabled="sending || !draft.trim()">
                                    {{ sending ? 'SŪTA...' : 'NOSŪTĪT' }}
                                </button>
                            </div>
                        </form>
                        <p v-else class="support-closed-note">
                            Šī saruna ir slēgta. Vēsturi vari apskatīt jebkurā laikā.
                            Lai sazinātos ar atbalstu vēlreiz, sāc jaunu sarunu.
                        </p>
                    </template>
                </div>
            </div>
        </section>
    </main>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import '../../css/support.css';

const router = useRouter();
const messages = ref([]);
const conversations = ref([]);
const selectedConversation = ref(null);
const draft = ref('');
const error = ref('');
const loading = ref(true);
const sending = ref(false);
const messageList = ref(null);
let refreshTimer;

onMounted(() => {
    loadConversations();
    refreshTimer = window.setInterval(refreshChat, 5000);
});

onUnmounted(() => {
    window.clearInterval(refreshTimer);
});

async function loadConversations() {
    if (sending.value) {
        return;
    }

    try {
        const response = await fetch('/api/support/conversations', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.status === 401) {
            await router.replace('/login');
            return;
        }

        if (!response.ok) {
            throw new Error(await responseError(response, 'Neizdevās ielādēt sarunu vēsturi.'));
        }

        const data = await response.json();
        conversations.value = data.conversations;
        const selectedId = selectedConversation.value?.id;
        selectedConversation.value = conversations.value.find(item => item.id === selectedId)
            ?? conversations.value.find(item => item.status === 'open')
            ?? conversations.value[0]
            ?? null;
        if (selectedConversation.value) {
            await loadMessages(selectedConversation.value.id);
        } else {
            messages.value = [];
        }
        error.value = '';
    } catch (loadError) {
        error.value = loadError instanceof Error
            ? loadError.message
            : 'Neizdevās ielādēt sarunu vēsturi. Lūdzu, mēģini vēlreiz.';
    } finally {
        loading.value = false;
    }
}

const hasOpenConversation = computed(() => conversations.value
    .some(conversation => conversation.status === 'open'));

async function refreshChat() {
    await loadConversations();
}

async function selectConversation(conversation) {
    selectedConversation.value = conversation;
    draft.value = '';
    try {
        await loadMessages(conversation.id);
        error.value = '';
    } catch (loadError) {
        error.value = loadError instanceof Error
            ? loadError.message
            : 'Neizdevās ielādēt sarunu.';
    }
}

async function loadMessages(conversationId) {
    const response = await fetch(`/api/support/conversations/${conversationId}/messages`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (response.status === 401) {
        await router.replace('/login');
        return;
    }

    if (!response.ok) {
        throw new Error(await responseError(response, 'Neizdevās ielādēt sarunu.'));
    }

    const data = await response.json();
    if (selectedConversation.value?.id === conversationId) {
        selectedConversation.value = data.conversation;
        messages.value = data.messages;
        scrollToLatest();
    }
}

async function startConversation() {
    error.value = '';

    try {
        const response = await fetch('/api/support/conversations', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
        });

        if (response.status === 401) {
            await router.replace('/login');
            return;
        }

        if (!response.ok) {
            throw new Error(await responseError(response, 'Neizdevās sākt jaunu sarunu.'));
        }

        const data = await response.json();
        selectedConversation.value = data.conversation;
        messages.value = [];
        await loadConversations();
    } catch (startError) {
        error.value = startError instanceof Error
            ? startError.message
            : 'Neizdevās sākt jaunu sarunu. Lūdzu, mēģini vēlreiz.';
    }
}

async function sendMessage() {
    const body = draft.value.trim();
    if (!body || sending.value) {
        return;
    }

    sending.value = true;
    error.value = '';

    try {
        const response = await fetch(
            `/api/support/conversations/${selectedConversation.value.id}/messages`,
            {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ body }),
            },
        );

        if (response.status === 401) {
            await router.replace('/login');
            return;
        }

        if (!response.ok) {
            throw new Error(await responseError(response, 'Neizdevās nosūtīt ziņojumu.'));
        }

        const data = await response.json();
        messages.value.push(data.message);
        selectedConversation.value = data.conversation;
        draft.value = '';
        await loadConversations();
        scrollToLatest();
    } catch (sendError) {
        error.value = sendError instanceof Error
            ? sendError.message
            : 'Ziņojumu neizdevās nosūtīt. Lūdzu, mēģini vēlreiz.';
    } finally {
        sending.value = false;
    }
}

async function responseError(response, fallback) {
    if (response.status === 419) {
        return 'Sesija ir beigusies. Atjauno lapu un pieslēdzies vēlreiz.';
    }

    const data = await response.json().catch(() => null);

    return data?.errors?.body?.[0] ?? data?.message ?? fallback;
}

function scrollToLatest() {
    requestAnimationFrame(() => {
        if (messageList.value) {
            messageList.value.scrollTop = messageList.value.scrollHeight;
        }
    });
}

function formatTime(value) {
    return new Date(value).toLocaleString('lv-LV', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}
</script>
