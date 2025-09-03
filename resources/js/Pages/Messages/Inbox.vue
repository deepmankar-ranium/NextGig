<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center p-4">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 flex flex-row w-full max-w-7xl h-[95vh] overflow-hidden">
                <!-- Chat Sidebar -->
                <ChatSidebar
                    :users="users"
                    @user-selected="handleUserSelected"
                    class="w-80 border-r border-slate-200/50 flex flex-col bg-white/50 backdrop-blur-sm"
                />

                <!-- Main Chat Area -->
                <div class="flex flex-col flex-1 relative">
                    <div v-if="selectedUser" class="flex flex-col flex-1 h-full">
                        <!-- Chat Header with enhanced design -->
                        <div class="bg-white/70 backdrop-blur-md p-6 border-b border-slate-200/50 flex items-center justify-between sticky top-0 z-10">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-lg font-bold shadow-lg ring-4 ring-white/20">
                                        {{ getUserInitials(selectedUser) }}
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full"></div>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold text-slate-800">{{ getUserDisplayName(selectedUser) }}</h1>
                                    <p class="text-sm text-slate-500">Active now</p>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex items-center space-x-2">
                                <button class="p-2 rounded-full hover:bg-slate-100 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </button>
                                <button class="p-2 rounded-full hover:bg-slate-100 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <button class="p-2 rounded-full hover:bg-slate-100 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Message Display Area with enhanced styling -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gradient-to-b from-slate-50/30 to-white/30" ref="messagesContainer" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                            <template v-if="messages && messages.length > 0">
                                <div v-for="message in messages" :key="message.id || Math.random()"
                                     :class="{'flex justify-end': isMyMessage(message), 'flex justify-start': !isMyMessage(message)}"
                                     class="animate-fade-in">

                                    <!-- Received messages -->
                                    <div v-if="!isMyMessage(message)" class="flex items-end space-x-3 max-w-md">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-r from-slate-400 to-slate-500 flex items-center justify-center text-white text-sm font-medium">
                                            {{ getUserInitials(selectedUser) }}
                                        </div>
                                        <div class="bg-white border border-slate-200 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                                            <p class="text-slate-800 leading-relaxed">{{ getMessageText(message) }}</p>
                                            <p class="text-xs text-slate-500 mt-1">{{ formatTime(message.created_at) }}</p>
                                        </div>
                                    </div>

                                    <!-- Sent messages -->
                                    <div v-else class="flex items-end space-x-3 max-w-md">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl rounded-br-md px-4 py-3 shadow-lg">
                                            <p class="text-white leading-relaxed">{{ getMessageText(message) }}</p>
                                            <div class="flex items-center justify-end space-x-1 mt-1">
                                                <p class="text-xs text-indigo-100">{{ formatTime(message.created_at) }}</p>
                                                <svg class="w-4 h-4 text-indigo-200" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-medium">
                                            {{ getUserInitials(currentUser) }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="flex items-center justify-center h-32 text-slate-500">
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        </div>

                        <!-- Enhanced Message Input -->
                        <div class="bg-white/80 backdrop-blur-md p-6 border-t border-slate-200/50">
                            <div class="flex items-end space-x-4">
                                <!-- Attachment button -->
                                <button class="flex-shrink-0 p-3 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                </button>

                                <!-- Message input container -->
                                <div class="flex-1 relative">
                                    <input
                                        type="text"
                                        v-model="newMessage"
                                        @keyup.enter="sendMessage"
                                        @input="handleTyping"
                                        placeholder="Type your message..."
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 pr-12 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 resize-none"
                                        :disabled="isLoading"
                                    />

                                    <!-- Emoji button -->
                                    <button class="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Send button with enhanced styling -->
                                <button
                                    @click="sendMessage"
                                    :disabled="!canSendMessage"
                                    class="flex-shrink-0 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed text-white rounded-2xl p-4 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95"
                                >
                                    <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Typing indicator -->
                            <div v-if="isTyping" class="flex items-center space-x-2 mt-3 text-sm text-slate-500">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                                <span>{{ getUserDisplayName(selectedUser) }} is typing...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced empty state -->
                    <div v-else class="flex items-center justify-center h-full">
                        <div class="text-center text-slate-500 max-w-md mx-auto p-8">
                            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-3xl flex items-center justify-center">
                                <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-700 mb-3">Start a conversation</h3>
                            <p class="text-slate-500 leading-relaxed">Select someone from the sidebar to begin chatting. Your messages are private and secure.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import ChatSidebar from '@/Layout/ChatSidebar.vue';
import { ref, watch, nextTick, computed } from 'vue';
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
const isTyping = ref(false);
const typingTimeout = ref(null);

// Computed properties for safe access
const selectedUser = computed(() => props.selectedUser || null);
const currentUser = computed(() => props.auth?.user || null);
const messages = computed(() => props.messages || []);

const canSendMessage = computed(() => {
    return !isLoading.value && newMessage.value?.trim() && selectedUser.value;
});

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

// Handle typing indicator
const handleTyping = () => {
    isTyping.value = true;
    clearTimeout(typingTimeout.value);
    typingTimeout.value = setTimeout(() => {
        isTyping.value = false;
    }, 1000);
};

// Send message with comprehensive error handling
const sendMessage = () => {
    if (!canSendMessage.value) return;

    const messageText = newMessage.value.trim();
    const receiverId = selectedUser.value?.id;

    if (!messageText || !receiverId) {
        console.error('Invalid message data:', { messageText, receiverId });
        return;
    }

    isLoading.value = true;

    try {
        router.post(route('messages.store'), {
            receiver_id: receiverId,
            message: messageText,
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                newMessage.value = '';
            },
            onError: (errors) => {
                console.error('Message send error:', errors);
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    } catch (error) {
        console.error('Error sending message:', error);
        isLoading.value = false;
    }
};
</script>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

/* Custom scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
