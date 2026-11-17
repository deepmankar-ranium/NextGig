<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, Trash2, MessageSquare, MoreVertical, Ban, Flag, Briefcase, X } from 'lucide-vue-next';
import AppLayout from '@/Layout/AppLayout.vue';
import ConversationList from '@/Components/Chat/ConversationList.vue';
import MessageThread from '@/Components/Chat/MessageThread.vue';
import Composer from '@/Components/Chat/Composer.vue';
import Avatar from '@/Components/Chat/Avatar.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import StageBadge from '@/Components/UI/StageBadge.vue';
import ReportDialog from '@/Components/Chat/ReportDialog.vue';
import { useNotificationStore } from '@/store/notifications';

const props = defineProps({
  users: { type: Array, default: () => [] },
  messages: { type: Array, default: () => [] },
  selectedUser: { type: Object, default: null },
  conversation: { type: Object, default: null }, // { block, can_send, applications[], about }
  reportReasons: { type: Object, default: () => ({}) },
});

const page = usePage();
const store = useNotificationStore();
const me = computed(() => page.props.auth.user);
const other = computed(() => props.selectedUser);
const isEmployer = computed(() => me.value?.role_id === 2);
const status = computed(() => page.props.flash?.status);

// Local copies so sends/receives update instantly; reset whenever the server sends new props.
const conversations = ref([...props.users]);
const thread = ref([...props.messages]);
watch(() => props.users, (v) => { conversations.value = [...v]; });
watch(() => props.messages, (v) => {
  // The server's list replaces ours, but keep sends still in flight or failed (with Retry).
  // A successful send's temp copy is removed in onSuccess, once the real one has arrived.
  const pending = thread.value.filter((m) => (m.temp || m.failed) && m.receiver_id === other.value?.id);
  thread.value = [...v, ...pending];
});

const touchConversation = (partnerId, text, fromMe, at, unreadDelta = 0) => {
  const i = conversations.value.findIndex((c) => c.id === partnerId);
  if (i === -1) return false;
  const c = { ...conversations.value[i], last_message: text.slice(0, 80), last_from_me: fromMe, last_at: at, unread: Math.max(0, (conversations.value[i].unread || 0) + unreadDelta) };
  conversations.value.splice(i, 1);
  conversations.value.unshift(c);
  return true;
};

// ---- Which job new messages are about ----
const applications = computed(() => props.conversation?.applications ?? []);
const defaultAbout = () => props.conversation?.about
  ?? (applications.value.length === 1 ? { id: applications.value[0].job_id, title: applications.value[0].title } : null);
const about = ref(defaultAbout());
watch(() => other.value?.id, () => { about.value = defaultAbout(); });

// ---- Sending ----
const draft = ref('');
const sending = ref(false);
const composer = ref(null);

const post = (temp) => {
  sending.value = true;
  router.post(route('messages.store'), { receiver_id: temp.receiver_id, message: temp.message, job_listing_id: temp.job_listing_id }, {
    preserveState: true,
    preserveScroll: true,
    only: ['messages', 'users', 'conversation', 'flash'],
    onSuccess: () => { thread.value = thread.value.filter((x) => x.id !== temp.id); },
    onError: (errors) => {
      const m = thread.value.find((x) => x.id === temp.id);
      if (m) Object.assign(m, { temp: false, failed: true, error: errors.message || errors.job_listing_id || 'Not sent.' });
    },
    onFinish: () => { sending.value = false; composer.value?.focus(); },
  });
};

const send = () => {
  const text = draft.value.trim();
  if (!text || !other.value) return;

  const temp = {
    id: `temp_${Date.now()}`, message: text, sender_id: me.value.id, receiver_id: other.value.id,
    job_listing_id: about.value?.id ?? null, job_listing: about.value, created_at: new Date().toISOString(), temp: true,
  };
  thread.value.push(temp);
  touchConversation(other.value.id, text, true, temp.created_at);
  draft.value = '';
  post(temp);
};

const retry = (m) => {
  Object.assign(m, { failed: false, error: null, temp: true });
  post(m);
};
const discard = (m) => { thread.value = thread.value.filter((x) => x.id !== m.id); };

// ---- Menu: block / report / delete ----
const menuOpen = ref(false);
const confirmState = ref(null); // { type: 'conversation' | 'message' | 'block', message? }
const reporting = ref(false);

const closeMenu = (e) => { if (!e.target.closest?.('[data-thread-menu]')) menuOpen.value = false; };
onMounted(() => document.addEventListener('click', closeMenu));
onUnmounted(() => document.removeEventListener('click', closeMenu));

const reloadThread = { preserveScroll: true, preserveState: true, only: ['conversation', 'flash'] };

const unblock = () => router.delete(route('users.unblock', other.value.id), reloadThread);

const doConfirm = () => {
  const state = confirmState.value;
  confirmState.value = null;

  if (state.type === 'conversation') {
    router.post(route('messages.delete', { sender_id: me.value.id, receiver_id: other.value.id }), {}, {
      preserveScroll: true,
      onSuccess: () => router.get('/messages'),
    });
  } else if (state.type === 'block') {
    router.post(route('users.block', other.value.id), {}, reloadThread);
  } else {
    const id = state.message.id;
    thread.value = thread.value.filter((m) => m.id !== id);
    router.delete(route('message.destroy', { message_id: id }), { preserveScroll: true, preserveState: true, only: ['messages', 'users'] });
  }
};

// ---- Real time: the open conversation's channel ----
let channel = null;
const leaveChannel = () => {
  if (!channel || !window.Echo) return;
  window.Echo.leave(channel);
  channel = null;
};
const joinChannel = () => {
  leaveChannel();
  if (!other.value || !window.Echo) return;
  const [a, b] = [me.value.id, other.value.id].sort((x, y) => x - y);
  channel = `chat.${a}.${b}`;
  window.Echo.private(channel)
    .listen('ChatMessageSent', (e) => {
      if (e.message.sender_id === me.value.id) return;
      thread.value.push(e.message);
      touchConversation(e.message.sender_id, e.message.message, false, e.message.created_at);
      axios.post(`/api/direct-messages/${e.message.id}/read`).then(() => store.fetchUnreadCount()).catch(() => {});
    })
    .listen('InBetweenMessageDeleted', (e) => {
      thread.value = thread.value.filter((m) => m.id !== e.message_id);
    });
};

// Messages from people whose conversation isn't open: update the list preview + unread count.
watch(() => store.lastIncomingMessage, (e) => {
  if (!e || e.message.sender_id === other.value?.id) return;
  if (!touchConversation(e.message.sender_id, e.message.message, false, e.message.created_at, 1)) {
    router.reload({ only: ['users'] });
  }
});

onMounted(() => { joinChannel(); store.fetchUnreadCount(); });
onUnmounted(leaveChannel);
watch(() => other.value?.id, () => { joinChannel(); store.fetchUnreadCount(); draft.value = ''; menuOpen.value = false; });
</script>

<template>
  <AppLayout full-height>
    <div class="mx-auto flex h-[calc(100dvh-8rem)] max-w-7xl overflow-hidden bg-white md:h-[calc(100dvh-4rem)] md:border-x md:border-gray-200">
      <!-- Conversation list: full width on phones until a conversation is open -->
      <div class="w-full shrink-0 md:block md:w-80" :class="other ? 'hidden' : 'block'">
        <ConversationList :conversations="conversations" :selected-id="other?.id ?? null" />
      </div>

      <!-- Conversation -->
      <section class="min-w-0 flex-1 flex-col md:flex" :class="other ? 'flex' : 'hidden'">
        <template v-if="other">
          <header class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-3 sm:px-5">
            <Link href="/messages" class="-ml-1 rounded-full p-1.5 text-gray-500 hover:bg-gray-100 md:hidden" aria-label="All conversations">
              <ChevronLeft class="h-5 w-5" />
            </Link>
            <Avatar :id="other.id" :name="other.name" :src="other.photo_url" />
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-gray-900">{{ other.name }}</p>
              <p class="text-xs text-gray-500">{{ other.role }}</p>
            </div>

            <div class="relative" data-thread-menu>
              <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-haspopup="menu"
                class="rounded-full p-2 text-gray-500 hover:bg-gray-100" aria-label="Conversation options">
                <MoreVertical class="h-4 w-4" />
              </button>
              <div v-if="menuOpen" role="menu" class="absolute right-0 z-20 mt-1 w-52 rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                <button v-if="conversation?.block === 'you_blocked'" role="menuitem" type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50" @click="menuOpen = false; unblock()">
                  <Ban class="h-4 w-4 text-gray-400" /> Unblock {{ other.name.split(' ')[0] }}
                </button>
                <button v-else role="menuitem" type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50" @click="menuOpen = false; confirmState = { type: 'block' }">
                  <Ban class="h-4 w-4 text-gray-400" /> Block {{ other.name.split(' ')[0] }}
                </button>
                <button role="menuitem" type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50" @click="menuOpen = false; reporting = true">
                  <Flag class="h-4 w-4 text-gray-400" /> Report
                </button>
                <button v-if="thread.length" role="menuitem" type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50" @click="menuOpen = false; confirmState = { type: 'conversation' }">
                  <Trash2 class="h-4 w-4" /> Delete conversation
                </button>
              </div>
            </div>
          </header>

          <!-- What links the two of you -->
          <div v-if="applications.length" class="flex shrink-0 items-center gap-2 overflow-x-auto border-b border-gray-200 bg-white px-3 py-2 sm:px-5">
            <span class="shrink-0 text-xs text-gray-500">{{ isEmployer ? 'Applied to' : 'Your applications' }}</span>
            <button v-for="a in applications" :key="a.id" type="button" @click="about = { id: a.job_id, title: a.title }"
              class="flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs transition-colors"
              :class="about?.id === a.job_id ? 'border-indigo-300 bg-indigo-50 text-indigo-800' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
              :title="`Write about ${a.title}`">
              {{ a.title }} <StageBadge :status="a.status" />
            </button>
          </div>

          <div v-if="status" role="status" class="shrink-0 bg-indigo-50 px-4 py-2 text-center text-xs text-indigo-800">{{ status }}</div>

          <MessageThread :messages="thread" :me="me" :other="other"
            @delete="(m) => (confirmState = { type: 'message', message: m })" @retry="retry" @discard="discard" />

          <!-- Blocked: no composer -->
          <div v-if="conversation?.block === 'you_blocked'" class="shrink-0 border-t border-gray-200 bg-gray-50 px-4 py-4 text-center text-sm text-gray-600">
            You blocked {{ other.name }}. Neither of you can send messages.
            <button type="button" class="ml-1 font-medium text-indigo-600 hover:underline" @click="unblock">Unblock</button>
          </div>
          <div v-else-if="conversation?.block === 'blocked_you'" class="shrink-0 border-t border-gray-200 bg-gray-50 px-4 py-4 text-center text-sm text-gray-600">
            You can't reply to this conversation.
          </div>
          <template v-else>
            <div v-if="about" class="flex shrink-0 items-center gap-2 border-t border-gray-200 bg-white px-4 pt-2 text-xs text-gray-600">
              <Briefcase class="h-3.5 w-3.5 text-gray-400" />
              <span class="truncate">About <span class="font-medium text-gray-900">{{ about.title }}</span></span>
              <button type="button" class="rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Don't link a job" @click="about = null"><X class="h-3.5 w-3.5" /></button>
            </div>
            <Composer ref="composer" v-model="draft" :busy="sending" :class="about ? '!border-t-0' : ''"
              :placeholder="`Message ${other.name.split(' ')[0]}…`" autofocus @send="send" />
          </template>
        </template>

        <div v-else class="flex flex-1 flex-col items-center justify-center bg-gray-50 px-6 text-center">
          <div class="mb-3 rounded-full bg-indigo-50 p-4"><MessageSquare class="h-7 w-7 text-indigo-600" /></div>
          <p class="text-sm font-medium text-gray-900">Select a conversation</p>
          <p v-if="status" class="mt-2 max-w-xs rounded-lg bg-indigo-50 px-3 py-2 text-xs text-indigo-800">{{ status }}</p>
          <p class="mt-1 max-w-xs text-xs text-gray-500">
            {{ isEmployer ? 'Message an applicant from the Applicants page.' : 'Start one with "Message employer" on any job.' }}
          </p>
        </div>
      </section>
    </div>

    <ConfirmDialog v-if="confirmState?.type === 'conversation'" danger confirm-label="Delete"
      title="Delete this conversation?"
      :message="`It's removed from your inbox only. ${other?.name} still has their copy, and it comes back if either of you sends a new message.`"
      @confirm="doConfirm" @cancel="confirmState = null" />
    <ConfirmDialog v-if="confirmState?.type === 'message'" danger confirm-label="Delete"
      title="Delete this message?" message="It's removed for both of you."
      @confirm="doConfirm" @cancel="confirmState = null" />
    <ConfirmDialog v-if="confirmState?.type === 'block'" danger confirm-label="Block"
      :title="`Block ${other?.name}?`"
      message="Neither of you will be able to send messages in this conversation. They aren't told. You can unblock any time."
      @confirm="doConfirm" @cancel="confirmState = null" />
    <ReportDialog v-if="reporting && other" :user="other" :reasons="reportReasons" @close="reporting = false" />
  </AppLayout>
</template>
