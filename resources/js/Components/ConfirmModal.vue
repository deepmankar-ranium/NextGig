<script setup>
import { ref, watch } from 'vue';
import { AlertCircle } from 'lucide-vue-next';

const props = defineProps({
  isOpen: Boolean
});

const emit = defineEmits(['confirm', 'cancel', 'update:isOpen']);
const isModalOpen = ref(props.isOpen);

watch(() => props.isOpen, val => isModalOpen.value = val);
watch(isModalOpen, val => emit('update:isOpen', val));

const handleConfirm = () => {
  emit('confirm', { confirmed: true });
  isModalOpen.value = false;
};

const handleCancel = () => {
  emit('cancel', { confirmed: false });
  isModalOpen.value = false;
};
</script>

<template>
  <Transition name="fade">
    <div v-if="isModalOpen"
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-[100] flex items-center justify-center p-4"
         @click.self="handleCancel">
      <Transition name="scale">
        <div v-if="isModalOpen"
             class="bg-white rounded-xl shadow-2xl w-full max-w-md"
             @click.stop>
          <div class="p-6 bg-red-50 border-b border-red-100">
            <div class="flex items-center gap-3">
              <AlertCircle class="w-6 h-6 text-red-600" />
              <h3 class="text-xl font-semibold">Clear Chat History</h3>
            </div>
          </div>

          <div class="p-6">
            <p class="text-gray-600">Are you sure you want to clear all chat messages? This action cannot be undone.</p>
          </div>

          <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button @click="handleCancel"
                    class="px-4 py-2 text-gray-700 bg-white border rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button @click="handleConfirm"
                    class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">
              Clear Chat
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.scale-enter-from,
.scale-leave-to {
  transform: scale(0.95);
  opacity: 0;
}
</style>