<template>
  <AppLayout>
    <div class="bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto h-[calc(100vh-10rem)] flex flex-col bg-white rounded-2xl shadow-lg border border-gray-200"
           :class="{ 'blur-sm pointer-events-none': isModalOpen }">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/70 backdrop-blur-lg rounded-t-2xl border-b border-gray-200">
          <div class="flex justify-between items-center px-6 py-4">
            <div class="flex items-center gap-4">
              <div class="p-2 bg-indigo-100 rounded-full">
                <MessageCircle class="w-6 h-6 text-indigo-600" />
              </div>
              <h2 class="text-xl font-bold text-gray-800">Chat Assistant</h2>
            </div>
            <button @click="showClearChatModal"
                    class="flex items-center gap-2 px-4 py-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors">
              <Trash2 class="w-4 h-4" />
              <span class="hidden sm:inline">Clear Chat</span>
            </button>
          </div>
        </header>

        <!-- Chat Messages -->
        <div ref="chatBox" class="flex-1 overflow-y-auto px-6 py-4 space-y-6 scroll-smooth">
          <div v-for="(msg, index) in messages"
               :key="index"
               :class="['transition-all duration-300 ease-out animate-message']">
            <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
              <div class="flex gap-3 max-w-[85%]">
                <div v-if="msg.role === 'bot'" class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <MessageCircle class="w-5 h-5 text-gray-500" />
                </div>
                <div :class="[
                  'p-4 rounded-2xl shadow-sm',
                  msg.role === 'user' ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none'
                ]">
                  <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                </div>
              </div>
            </div>
             <p :class="[
                'text-xs text-gray-400 mt-2',
                msg.role === 'user' ? 'text-right' : 'text-left ml-11'
              ]">{{ msg.time }}</p>
          </div>
        </div>

        <!-- Input Area -->
        <footer class="sticky bottom-0 z-10 bg-white/70 backdrop-blur-lg rounded-b-2xl border-t border-gray-200 p-4">
          <div class="flex items-center gap-4">
            <input v-model="message"
                   @keyup.enter="sendMessage"
                   :disabled="isLoading"
                   placeholder="Type your message..."
                   class="flex-1 p-4 bg-gray-100 border-transparent rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
            <button @click="sendMessage"
                    :disabled="isLoading || !message.trim()"
                    class="p-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors">
              <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin" />
              <Send v-else class="w-6 h-6" />
            </button>
          </div>
        </footer>
      </div>

      <ConfirmModal
       :isOpen="isModalOpen" @update:isOpen="val => isModalOpen = val"
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
    </div>
  </AppLayout>
</template>


<script setup>
import AppLayout from "@/Layout/AppLayout.vue";
import { ref, onMounted, nextTick } from "vue";
import { Send, Trash2, Loader2, MessageCircle } from 'lucide-vue-next';
import ConfirmModal from "@/Components/ConfirmModal.vue";
import axios from "axios";
import { get } from "lodash";

const chatBox = ref(null);
const message = ref("");
const messages = ref([]);
const isLoading = ref(false);
const isModalOpen = ref(false);

const createMessage = (role, content) => ({
  role,
  content,
  time: new Date().toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true
  }),
  animate: true
});

const scrollToBottom = async () => {
  await nextTick();
  if (chatBox.value) {
    chatBox.value.scrollTop = chatBox.value.scrollHeight;
  }
};

const handleConfirm = async (event) => {
  if (event.confirmed) {
    await clearChat();
  }
};

const handleCancel = () => {
  console.log("User canceled clearing chat");
};

const showClearChatModal = () => {
  isModalOpen.value = true;
};

const clearChat = async () => {
  try {
    await axios.post('/clear-chat');
    messages.value = [];
    await getChat();
  } catch (error) {
    console.error("Error clearing chat:", error);
  }
};

const getChat = async () => {
  try {
    const { data } = await axios.get('/chat-history');

    messages.value = data.history.map(item => ({
        role: item.sender === 'user' ? 'user' : 'bot',
        content: item.message,
        time: new Date(item.created_at).toLocaleTimeString("en-US", {
            hour: "numeric",
            minute: "2-digit",
            hour12: true
        })
    }));

    if (messages.value.length === 0) {
        await createIntroMessage();
    }

    await scrollToBottom();
  } catch (error) {
    console.error("Error fetching chat history:", error);
  }
};

const createIntroMessage = async () => {
  try {
    const { data } = await axios.post('/create-intro-message');
    if (data.status === 'success' && data.message) {
      messages.value.push({
        role: 'bot',
        content: data.message,
        time: new Date().toLocaleTimeString("en-US", {
          hour: "numeric",
          minute: "2-digit",
          hour12: true
        })
      });
    }
  } catch (error) {
    console.error("Error creating intro message:", error);
  }
};

const sendMessage = async () => {
  if (!message.value.trim()) return;

  const userMessage = message.value;
  message.value = "";

  try {
    isLoading.value = true;

    messages.value.push(createMessage("user", userMessage));
    await scrollToBottom();

    const response = await axios.post('/chat', { message: userMessage });

    if (response.data.status === 'success') {
      messages.value.push(createMessage("bot", response.data.response));
      await scrollToBottom();
    } else {
      messages.value.push(createMessage("bot", "Sorry, I encountered an error. Please try again."));
    }
  } catch (error) {
    console.error('Request failed:', error);
    messages.value.push(createMessage("bot", "Sorry, I encountered an error. Please try again."));
  } finally {
    isLoading.value = false;
    await scrollToBottom();
  }
};

onMounted(() => {
    getChat();
});

</script>

<style scoped>
.animate-message {
  animation: slideIn 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Custom Scrollbar */
div::-webkit-scrollbar {
  width: 6px;
}

div::-webkit-scrollbar-track {
  background: transparent;
}

div::-webkit-scrollbar-thumb {
  background-color: #D1D5DB;
  border-radius: 20px;
}

div::-webkit-scrollbar-thumb:hover {
  background-color: #9CA3AF;
}

.scroll-smooth {
  overscroll-behavior: contain;
}
</style>