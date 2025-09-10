<template>
  <div class="flex h-screen bg-slate-50">
    <!-- Sidebar -->
    <ChatSidebar :users="users" @user-selected="handleUserSelected" />

    <!-- Main Chat Area -->
    <div class="flex flex-1 flex-col">
      <ChatHeader :user="selectedUser" :has-messages="messages.length > 0" @delete-messages="confirmDeleteMessages" />

      <!-- Messages Area -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-5 bg-slate-50">
        <template v-if="selectedUser && messages && messages.length > 0">
          <MessageBubble
            v-for="message in messages"
            :key="message.id || Math.random()"
            :message="message"
            :user="selectedUser"
            :current-user="currentUser"
            @mouseenter="handleMouseOver"
            @mouseleave="handleMouseLeave"
          />
        </template>
      </div>

      <MessageInput v-model="newMessage" :disabled="isLoading || !selectedUser" :is-loading="isLoading" :can-send-message="canSendMessage" @send="sendMessage" />
    </div>

    <!-- Right Sidebar -->
    <UserProfileSidebar :user="selectedUser" />
  </div>

  <ConfirmationModal
    :show="isConfirmDeleteModalOpen"
    title="Delete Conversation"
    message="Are you sure you want to delete this entire conversation? All messages will be permanently removed. This action cannot be undone."
    @confirm="handleDeleteConfirm"
    @cancel="handleDeleteCancel"
  />

  <ActionModal
    :show="showHoverModal"
    :message="hoveredMessage"
    :position="hoverModalPosition"
    @mouseenter="cancelHideModal"
    @mouseleave="handleMouseLeave"
    @reply="replyToMessage"
    @copy="copyMessage"
    @delete="deleteMessage"
  />
</template>


<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import ChatSidebar from '@/Layout/ChatSidebar.vue';
import MessageBubble from '@/Components/Chat/MessageBubble.vue';
import ActionModal from '@/Components/Chat/ActionModal.vue';
import ChatHeader from '@/Components/Chat/ChatHeader.vue';
import MessageInput from '@/Components/Chat/MessageInput.vue';
import UserProfileSidebar from '@/Components/Chat/UserProfileSidebar.vue';
import ConfirmationModal from '@/Components/Chat/ConfirmationModal.vue';
import { ref, watch, nextTick, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { getMessageText } from '@/utils/helpers';

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
const hoveredMessage = ref(null);
const showHoverModal = ref(false);
const hoverModalPosition = ref({ top: 0, left: 0 });
let leaveTimeout = null;

const localMessages = ref([...props.messages]);

watch(() => props.messages, (newMessages) => {
    localMessages.value = [...newMessages];
}, { immediate: true });

const selectedUser = computed(() => props.selectedUser || null);
const currentUser = computed(() => props.auth?.user || null);
const messages = computed(() => localMessages.value || []);

const canSendMessage = computed(() => {
    return !isLoading.value && newMessage.value?.trim() && selectedUser.value;
});

let currentChannel = null;

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

const setupEchoChannel = (user) => {
    if (!user || !currentUser.value || !window.Echo) return;

    const participants = [currentUser.value.id, user.id].sort();
    const channelName = `chat.${participants[0]}.${participants[1]}`;

    try {
        currentChannel = window.Echo.private(channelName)
            .listen('ChatMessageSent', (e) => {
                console.log('New message received:', e.message);
                if (e.message.sender_id !== currentUser.value.id) {
                    localMessages.value.push(e.message);
                }
            })
            .listen('MessagesDeleted', (e) => {
                console.log('Messages deleted event received:', e);
                // Check if the deletion event is for the current conversation
                const userIds = [e.sender_id, e.receiver_id].sort();
                const currentUserIds = [currentUser.value.id, selectedUser.value.id].sort();

                if (JSON.stringify(userIds) === JSON.stringify(currentUserIds)) {
                    localMessages.value = [];
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

const cleanupEchoChannel = () => {
    if (currentChannel) {
        try {
            // Stop listening to all events on the channel before leaving
            currentChannel.stopListening('ChatMessageSent');
            currentChannel.stopListening('MessagesDeleted');
            window.Echo.leave(currentChannel.name);
            currentChannel = null;
            console.log('Echo channel cleaned up');
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

watch(selectedUser, (newUser, oldUser) => {
    cleanupEchoChannel();
    if (newUser) {
        setupEchoChannel(newUser);
    }
});

const handleUserSelected = (user) => {
    if (!user || typeof user !== 'object' || !user.id) {
        console.error('Selected user is invalid:', user);
        return;
    }

    try {
        router.get(route('messages.show', { user: user.id }), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Error selecting user:', error);
    }
};

const sendMessage = () => {
    if (!canSendMessage.value) return;

    const messageText = newMessage.value.trim();
    const receiverId = selectedUser.value?.id;

    if (!messageText || !receiverId) {
        console.error('Invalid message data:', { messageText, receiverId });
        return;
    }

    isLoading.value = true;

    const tempMessage = {
        id: 'temp_' + Date.now(),
        message: messageText,
        sender_id: currentUser.value.id,
        receiver_id: receiverId,
        created_at: new Date().toISOString(),
        temp: true
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
                const tempIndex = localMessages.value.findIndex(msg => msg.id === tempMessage.id);
                if (tempIndex !== -1) {
                    localMessages.value.splice(tempIndex, 1);
                }
            },
            onError: (errors) => {
                console.error('Message send error:', errors);
                const tempIndex = localMessages.value.findIndex(msg => msg.id === tempMessage.id);
                if (tempIndex !== -1) {
                    localMessages.value.splice(tempIndex, 1);
                }
                newMessage.value = originalMessageText;
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    } catch (error) {
        console.error('Error sending message:', error);
        const tempIndex = localMessages.value.findIndex(msg => msg.id === tempMessage.id);
        if (tempIndex !== -1) {
            localMessages.value.splice(tempIndex, 1);
        }
        newMessage.value = originalMessageText;
        isLoading.value = false;
    }
};

const isConfirmDeleteModalOpen = ref(false);

const confirmDeleteMessages = () => {
    if (selectedUser.value) {
        isConfirmDeleteModalOpen.value = true;
    }
};

const deleteMessages = () => {
    if (!selectedUser.value || !currentUser.value) {
        console.error('Cannot delete messages: missing user information.');
        return;
    }

    try {
        router.post(route('messages.delete', { sender_id: currentUser.value.id, receiver_id: selectedUser.value.id }), {
            onSuccess: () => {
                localMessages.value = [];

            },
            preserveState: true,
            preserveScroll: true,
            onError: (errors) => {
                console.error('Error deleting messages:', errors);
            }
        });
    } catch (error) {
        console.error('Exception during message deletion:', error);
    }
};

const handleDeleteConfirm = () => {
    deleteMessages();
    isConfirmDeleteModalOpen.value = false;
};

const handleDeleteCancel = () => {
    isConfirmDeleteModalOpen.value = false;
};

const handleMouseOver = (message, event) => {
    cancelHideModal();
    const rect = event.currentTarget.getBoundingClientRect();
    hoveredMessage.value = message;
    showHoverModal.value = true;

    const modalWidth = 110;
    const modalHeight = 40;
    const gap = 10;

    const top = rect.top + window.scrollY + rect.height / 2 - modalHeight / 2;

    if (message.sender_id === currentUser.value.id) {
        hoverModalPosition.value = {
            top: top,
            left: rect.left - modalWidth - gap,
        };
    } else {
        hoverModalPosition.value = {
            top: top,
            left: rect.right + gap,
        };
    }
};

const handleMouseLeave = () => {
    leaveTimeout = setTimeout(() => {
        showHoverModal.value = false;
        hoveredMessage.value = null;
    }, 300);
};

const cancelHideModal = () => {
    clearTimeout(leaveTimeout);
};

const replyToMessage = (messageId) => {
    console.log('Replying to message:', messageId);
    // Placeholder for reply logic
};

const copyMessage = (messageId) => {
    const message = messages.value.find(m => m.id === messageId);
    if (message) {
        try {
            navigator.clipboard.writeText(getMessageText(message));
            console.log('Message copied to clipboard');
        } catch (err) {
            console.error('Failed to copy message: ', err);
        }
    }
};

const deleteMessage = (messageId) => {
    console.log('Deleting message:', messageId);
    // Placeholder for delete logic
};
</script>
