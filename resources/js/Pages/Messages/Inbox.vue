<template>

  <div class="flex h-screen bg-slate-50">
    <!-- Sidebar -->
    <ChatSidebar :users="users" @user-selected="handleUserSelected" />

    <!-- Main Chat Area -->
    <div class="flex flex-1 flex-col">
      <!-- Chat Header -->
      <div class="h-[60px] bg-white border-b border-slate-200 px-6 flex items-center justify-between shadow-sm">
        <div v-if="selectedUser" class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-base bg-gradient-to-br from-violet-600 to-violet-500"
          >
            {{ getUserInitials(selectedUser) }}
          </div>
          <div>
            <h1 class="text-base font-semibold text-slate-800 m-0">
              {{ getUserDisplayName(selectedUser) }}
            </h1>
            <p class="text-xs text-emerald-500 flex items-center gap-1 m-0">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              Online
            </p>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center gap-2">
          <button
            class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center transition hover:bg-slate-200"
          >
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
              />
            </svg>
          </button>
          <button
            class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center transition hover:bg-slate-200"
          >
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
          </button>
          <button
            class="w-9 h-9 rounded-full bg-violet-600 text-white flex items-center justify-center transition hover:bg-violet-700"
          >
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-5 bg-slate-50">
        <template v-if="selectedUser && messages && messages.length > 0">
          <div
            v-for="message in messages"
            :key="message.id || Math.random()"
            class="flex gap-3 mb-5"
            :class="isMyMessage(message) ? 'flex-row-reverse' : ''"
          >
            <!-- Received messages -->
            <div v-if="!isMyMessage(message)" class="flex gap-3 max-w-[70%]">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 bg-gradient-to-br from-violet-600 to-violet-500"
              >
                {{ getUserInitials(selectedUser) }}
              </div>
              <div
                class="bg-white rounded-2xl rounded-tl-md p-3.5 shadow-sm border border-slate-200"
              >
                <p class="text-slate-800 text-sm leading-relaxed m-0">{{ getMessageText(message) }}</p>
                <p class="text-[11px] text-slate-500 mt-1">{{ formatTime(message.created_at) }}</p>
              </div>
            </div>

            <!-- Sent messages -->
            <div v-else class="flex gap-3 max-w-[70%]">
              <div class="bg-violet-600 rounded-2xl rounded-tr-md p-3.5 shadow-sm">
                <p class="text-white text-sm leading-relaxed m-0">{{ getMessageText(message) }}</p>
                <div class="flex items-center justify-end gap-1 mt-1">
                  <p class="text-[11px] text-white/80 m-0">{{ formatTime(message.created_at) }}</p>
                  <svg class="w-[14px] h-[14px] text-white/80" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    />
                  </svg>
                </div>
              </div>
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 bg-gradient-to-br from-emerald-500 to-emerald-600"
              >
                {{ getUserInitials(currentUser) }}
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Input -->
      <div class="p-5 bg-white border-t border-slate-200">
        <div class="flex items-end gap-3">
          <div
            class="fleam able to build chat feature in this project *:x flex-1 items-center gap-3 bg-slate-100 rounded-full px-5 py-3 border-2 border-transparent transition focus-within:border-violet-500"
          >
            <button
              class="w-6 h-6 text-slate-500 flex items-center justify-center hover:text-slate-700"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                />
              </svg>
            </button>

            <input
              type="text"
              v-model="newMessage"
              @keyup.enter="sendMessage"
              placeholder="Type your message here..."
              :disabled="isLoading || !selectedUser"
              class="flex-1 bg-transparent border-none outline-none text-slate-800 text-sm placeholder-slate-400 focus:ring-0"
            />

            <button
              class="w-6 h-6 text-slate-500 flex items-center justify-center hover:text-slate-700"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </button>
          </div>

          <button
            @click="sendMessage"
            :disabled="!canSendMessage"
            class="w-11 h-11 bg-violet-600 rounded-full text-white flex items-center justify-center transition shadow-lg hover:bg-violet-700 disabled:opacity-50"
          >
            <svg
              v-if="!isLoading"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div v-if="selectedUser" class="w-[300px] bg-white border-l border-slate-200 p-6 overflow-y-auto">
      <!-- Profile Header -->
      <div class="text-center mb-6">
        <div
          class="w-20 h-20 rounded-full flex items-center justify-center text-white font-semibold text-3xl mx-auto mb-3 shadow-lg bg-gradient-to-br from-violet-600 to-violet-500"
        >
          {{ getUserInitials(selectedUser) }}
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-1">
          {{ getUserDisplayName(selectedUser) }}
        </h2>
        <p class="text-sm text-emerald-500 flex items-center justify-center gap-1">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          UX Designer
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2 mb-6">
        <button
          class="flex-1 p-2 border border-slate-200 rounded-lg bg-white text-slate-500 text-xs flex items-center justify-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
        </button>
        <!-- <button
          class="flex-1 p-2 border border-slate-200 rounded-lg bg-white text-slate-500 text-xs flex items-center justify-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
            />
          </svg>
        </button>
        <button
          class="flex-1 p-2 border border-slate-200 rounded-lg bg-white text-slate-500 text-xs flex items-center justify-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
            />
          </svg>
        </button> -->
      </div>

      <!-- Contact Info -->
      <!-- <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-slate-800">EDIT PROFILE</h3>
          <button class="text-xs text-slate-500">See all</button>
        </div>
        <div class="mb-4">
          <label class="text-xs font-medium text-slate-500 block mb-1">Mobile</label>
          <p class="text-sm text-slate-800">+1(0) 332 4567</p>
        </div>
        <div class="mb-4">
          <label class="text-xs font-medium text-slate-500 block mb-1">Email</label>
          <p class="text-sm text-slate-800">michael@gmail.com</p>
        </div>
        <div class="mb-4">
          <label class="text-xs font-medium text-slate-500 block mb-1">Date of Birth</label>
          <p class="text-sm text-slate-800">02/12/1990</p>
        </div>
        <div class="mb-4">
          <label class="text-xs font-medium text-slate-500 block mb-1">Gender</label>
          <p class="text-sm text-slate-800">Male</p>
        </div>
      </div> -->

      <!-- Shared Media -->
      <!-- <div>
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-slate-800">Shared Media</h3>
          <button class="text-xs text-slate-500">See all</button>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div
            class="aspect-square bg-slate-100 rounded-lg flex items-center justify-center"
          >
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div
            class="aspect-square bg-slate-100 rounded-lg flex items-center justify-center"
          >
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div
            class="aspect-square bg-slate-100 rounded-lg flex items-center justify-center"
          >
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div
            class="aspect-square bg-slate-100 rounded-lg flex items-center justify-center"
          >
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
          </div>
        </div>
      </div> -->
    </div>
  </div>
</template>


<script setup>
// Replace your current script setup with this improved version

import AppLayout from '@/Layout/AppLayout.vue';
import ChatSidebar from '@/Layout/ChatSidebar.vue';
import { ref, watch, nextTick, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    },
    messages: {
        type: Array,
        default: () => []
    },
    selectedUser: {
        type: Object,
        default: null
    },
    auth: {
        type: Object,
        default: () => ({})
    }
});

const newMessage = ref('');
const messagesContainer = ref(null);
const isLoading = ref(false);

// ✅ Create reactive local messages array
const localMessages = ref([...props.messages]);

// ✅ Watch for prop changes and update local messages
watch(() => props.messages, (newMessages) => {
    localMessages.value = [...newMessages];
}, { immediate: true });

// Computed properties for safe access
const selectedUser = computed(() => props.selectedUser || null);
const currentUser = computed(() => props.auth?.user || null);
const messages = computed(() => localMessages.value || []);

const canSendMessage = computed(() => {
    return !isLoading.value && newMessage.value?.trim() && selectedUser.value;
});

// ✅ Store current Echo channel reference
let currentChannel = null;

// Auto-scroll to bottom when new messages arrive
watch(() => messages.value, () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTo({
                top: messagesContainer.value.scrollHeight,
                behavior: 'smooth'
            });
        }
    });
}, { deep: true });

// ✅ Improved Echo setup
const setupEchoChannel = (user) => {
    if (!user || !currentUser.value || !window.Echo) return;

    const participants = [currentUser.value.id, user.id].sort();
    const channelName = `chat.${participants[0]}.${participants[1]}`;

    try {
        currentChannel = window.Echo.private(channelName)
            .listen('ChatMessageSent', (e) => {
                console.log('New message received:', e.message);
                // ✅ Add to local messages array
                // Only add if the message is not from the current user (to prevent duplication from optimistic updates)
                if (e.message.sender_id !== currentUser.value.id) {
                    localMessages.value.push(e.message);
                }
            })
            .error((error) => {
                console.error('Echo channel error:', error);
            });

        console.log('Echo channel setup:', channelName);
    } catch (error) {
        console.error('Failed to setup Echo channel:', error);
    }
};

// ✅ Cleanup Echo channel
const cleanupEchoChannel = () => {
    if (currentChannel) {
        try {
            currentChannel.stopListening('ChatMessageSent');
            currentChannel = null;
        } catch (error) {
            console.error('Error cleaning up Echo channel:', error);
        }
    }
};

onMounted(() => {
    if (selectedUser.value) {
        setupEchoChannel(selectedUser.value);
    }
});

onUnmounted(() => {
    cleanupEchoChannel();
});

// ✅ Better user selection handling
watch(selectedUser, (newUser, oldUser) => {
    // Cleanup old channel
    cleanupEchoChannel();

    // Setup new channel
    if (newUser) {
        setupEchoChannel(newUser);
    }
});

// Utility functions with comprehensive error handling
const getUserDisplayName = (user) => {
    if (!user || typeof user !== 'object') return 'Unknown User';
    const name = user.full_name || user.name || user.first_name || user.username || '';
    return name.trim() || 'Unknown User';
};

const getUserInitials = (user) => {
    if (!user || typeof user !== 'object') return '?';
    const name = getUserDisplayName(user);
    if (name === 'Unknown User') return '?';

    try {
        const words = name.trim().split(/\s+/);
        if (words.length === 1) {
            return words[0].charAt(0).toUpperCase();
        }
        return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
    } catch (error) {
        return '?';
    }
};

const getMessageText = (message) => {
    if (!message || typeof message !== 'object') return '';
    return message.message || message.text || message.content || '';
};

const isMyMessage = (message) => {
    if (!message || !currentUser.value) return false;
    return message.sender_id === currentUser.value.id;
};

const formatTime = (timestamp) => {
    if (!timestamp) return '';
    try {
        const date = new Date(timestamp);
        if (isNaN(date.getTime())) return '';
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        console.warn('Error formatting time:', error);
        return '';
    }
};

// Handle user selection
const handleUserSelected = (user) => {
    if (!user || typeof user !== 'object' || !user.id) {
        console.error('Selected user is invalid:', user);
        return;
    }

    try {
        router.get(route('messages.fetch', { user: user.id }), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Error selecting user:', error);
    }
};

// ✅ Improved send message with optimistic updates
const sendMessage = () => {
    if (!canSendMessage.value) return;

    const messageText = newMessage.value.trim();
    const receiverId = selectedUser.value?.id;

    if (!messageText || !receiverId) {
        console.error('Invalid message data:', { messageText, receiverId });
        return;
    }

    isLoading.value = true;

    // ✅ Optimistic update - add message immediately
    const tempMessage = {
        id: 'temp_' + Date.now(),
        message: messageText,
        sender_id: currentUser.value.id,
        receiver_id: receiverId,
        created_at: new Date().toISOString(),
        temp: true // Mark as temporary
    };

    localMessages.value.push(tempMessage);
    const originalMessageText = newMessage.value;
    newMessage.value = '';

    try {
        router.post(route('messages.store'), {
            receiver_id: receiverId,
            message: messageText,
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (response) => {
                // ✅ Remove temp message and replace with real one if needed
                const tempIndex = localMessages.value.findIndex(msg => msg.id === tempMessage.id);
                if (tempIndex !== -1) {
                    localMessages.value.splice(tempIndex, 1);
                }
            },
            onError: (errors) => {
                console.error('Message send error:', errors);
                // ✅ Remove temp message on error
                const tempIndex = localMessages.value.findIndex(msg => msg.id === tempMessage.id);
                if (tempIndex !== -1) {
                    localMessages.value.splice(tempIndex, 1);
                }
                // ✅ Restore message text
                newMessage.value = originalMessageText;
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    } catch (error) {
        console.error('Error sending message:', error);
        // ✅ Remove temp message and restore text on error
        const tempIndex = localMessages.value.findIndex(msg => msg.id === tempMessage.id);
        if (tempIndex !== -1) {
            localMessages.value.splice(tempIndex, 1);
        }
        newMessage.value = originalMessageText;
        isLoading.value = false;
    }
};

const handleTyping = () => {
    // Add typing indicator logic here if needed
};
</script>
