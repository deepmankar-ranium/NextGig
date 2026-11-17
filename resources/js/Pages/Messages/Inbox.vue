<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, Trash2, MessageSquare } from 'lucide-vue-next';
import AppLayout from '@/Layout/AppLayout.vue';
import ConversationList from '@/Components/Chat/ConversationList.vue';
import MessageThread from '@/Components/Chat/MessageThread.vue';
import Composer from '@/Components/Chat/Composer.vue';
import Avatar from '@/Components/Chat/Avatar.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import { useNotificationStore } from '@/store/notifications';

const props = defineProps({
  users: { type: Array, default: () => [] },
  messages: { type: Array, default: () => [] },
  selectedUser: { type: Object, default: null },
});

const page = usePage();
const store = useNotificationStore();
const me = computed(() => page.props.auth.user);
const other = computed(() => props.selectedUser);

// Local copies so sends/receives update instantly; reset whenever the server sends new props.
const conversations = ref([...props.users]);
const thread = ref([...props.messages]);
watch(() => props.users, (v) => { conversations.value = [...v]; });
watch(() => props.messages, (v) => { thread.value = [...v]; });

const touchConversation = (partnerId, text, fromMe, at, unreadDelta = 0) => {
  const i = conversations.value.findIndex((c) => c.id === partnerId);
  if (i === -1) return false;
  const c = { ...conversations.value[i], last_message: text.slice(0, 80), last_from_me: fromMe, last_at: at, unread: Math.max(0, (conversations.value[i].unread || 0) + unreadDelta) };
  conversations.value.splice(i, 1);
  conversations.value.unshift(c);
  return true;
};

// ---- Sending ----
const draft = ref('');
const sending = ref(false);
const composer = ref(null);

const send = () => {
  const text = draft.value.trim();
  if (!text || !other.value) return;

  const temp = { id: `temp_${Date.now()}`, message: text, sender_id: me.value.id, receiver_id: other.value.id, created_at: new Date().toISOString(), temp: true };
  thread.value.push(temp);
  touchConversation(other.value.id, text, true, temp.created_at);
  draft.value = '';
  sending.value = true;

  router.post(route('messages.store'), { receiver_id: other.value.id, message: text }, {
    preserveState: true,
    preserveScroll: true,
    only: ['messages', 'users'],
    onError: () => {
      thread.value = thread.value.filter((m) => m.id !== temp.id);
      draft.value = text;
    },
    onFinish: () => { sending.value = false; composer.value?.focus(); },
  });
};

// ---- Deleting ----
const confirmState = ref(null); // { type: 'conversation' } | { type: 'message', message }

const doConfirm = () => {
  const state = confirmState.value;
  confirmState.value = null;

  if (state.type === 'conversation') {
    router.post(route('messages.delete', { sender_id: me.value.id, receiver_id: other.value.id }), {}, {
      preserveScroll: true,
      onSuccess: () => router.get('/messages'),
    });
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
watch(() => other.value?.id, () => { joinChannel(); store.fetchUnreadCount(); draft.value = ''; });
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
            <Avatar :id="other.id" :name="other.name" />
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-gray-900">{{ other.name }}</p>
              <p class="text-xs text-gray-500">{{ other.role }}</p>
            </div>
            <button v-if="thread.length" type="button" @click="confirmState = { type: 'conversation' }"
              class="rounded-full p-2 text-gray-400 hover:bg-red-50 hover:text-red-600" aria-label="Delete conversation" title="Delete conversation">
              <Trash2 class="h-4 w-4" />
            </button>
          </header>

          <MessageThread :messages="thread" :me="me" :other="other" @delete="(m) => (confirmState = { type: 'message', message: m })" />

          <Composer ref="composer" v-model="draft" :busy="sending" :placeholder="`Message ${other.name.split(' ')[0]}…`" autofocus @send="send" />
        </template>

        <div v-else class="flex flex-1 flex-col items-center justify-center bg-gray-50 px-6 text-center">
          <div class="mb-3 rounded-full bg-indigo-50 p-4"><MessageSquare class="h-7 w-7 text-indigo-600" /></div>
          <p class="text-sm font-medium text-gray-900">Select a conversation</p>
          <p class="mt-1 max-w-xs text-xs text-gray-500">Or start one with "Message employer" on a job, or the message icon next to an applicant.</p>
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
  </AppLayout>
</template>
