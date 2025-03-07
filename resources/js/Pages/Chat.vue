<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-900 py-5">
      <div class="max-w-3xl mx-auto h-screen flex flex-col relative bg-white" 
           :class="{ 'blur-xs pointer-events-none': isModalOpen }">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
          <div class="flex justify-between items-center px-6 py-4">
            <div class="flex items-center gap-3">
              <MessageCircle class="w-6 h-6 text-blue-500" />
              <h2 class="text-xl font-bold text-gray-800">Chat Assistant</h2>
            </div>
            <button @click="showClearChatModal"
                    class="flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg">
              <Trash2 class="w-4 h-4" />
              <span class="hidden sm:inline">Clear Chat</span>
            </button>
          </div>
        </header>

        <!-- Chat Messages -->
        <div ref="chatBox" class="flex-1 overflow-y-auto px-4 py-6 pb-20 space-y-4 scroll-smooth min-h-80" >
          <div v-for="(msg, index) in messages" 
               :key="index"
               :class="['transition-all duration-300 ease-out animate-message']">
            <div :class="msg.role === 'user' ? 'flex flex-col items-end' : 'flex flex-col items-start'">
              <div :class="[
                'max-w-[80%] p-3 rounded-2xl shadow-sm',
                msg.role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-800'
              ]">
                <p class="whitespace-pre-line">{{ msg.content }}</p>
              </div>
              <time class="text-xs text-gray-500 mt-1 px-2">{{ msg.time }}</time>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <footer class="sticky bottom-0 z-10 bg-white/80 backdrop-blur-md border-t border-gray-100 p-4">
          <div class="flex items-center gap-3">
            <input v-model="message" 
                   @keyup.enter="sendMessage" 
                   :disabled="isLoading"
                   placeholder="Type your message..."
                   class="flex-1 p-3 bg-white border rounded-lg focus:ring-2 focus:ring-blue-500" />
            <button @click="sendMessage" 
                    :disabled="isLoading || !message.trim()"
                    class="p-3 bg-blue-500 hover:bg-blue-600 disabled:opacity-50 text-white rounded-lg">
              <Loader2 v-if="isLoading" class="w-5 h-5 animate-spin" />
              <Send v-else class="w-5 h-5" />
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
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Custom Scrollbar */
div::-webkit-scrollbar {
  width: 8px;
}

div::-webkit-scrollbar-track {
  background: transparent;
}

div::-webkit-scrollbar-thumb {
  background-color: #CBD5E1;
  border-radius: 20px;
}

div::-webkit-scrollbar-thumb:hover {
  background-color: #94A3B8;
}

/* Prevent overscroll behavior */
.scroll-smooth {
  overscroll-behavior: contain;
}
</style>