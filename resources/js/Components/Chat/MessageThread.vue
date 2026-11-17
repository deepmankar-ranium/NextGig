<script setup>
import { computed, ref, watch, nextTick, onMounted } from 'vue';
import { Copy, Trash2, Check } from 'lucide-vue-next';
import Avatar from './Avatar.vue';
import { groupMessages, dayLabel, timeLabel } from '@/utils/chat';

const props = defineProps({
  messages: { type: Array, required: true },
  me: { type: Object, required: true },
  other: { type: Object, required: true },
});
const emit = defineEmits(['delete']);

const days = computed(() => groupMessages(props.messages));
const scroller = ref(null);
const copiedId = ref(null);

const scrollToBottom = (smooth = false) => nextTick(() => {
  scroller.value?.scrollTo({ top: scroller.value.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
});
onMounted(() => scrollToBottom());
watch(() => props.messages.length, () => scrollToBottom(true));
watch(() => props.other.id, () => scrollToBottom());

const copy = async (m) => {
  try {
    await navigator.clipboard.writeText(m.message);
    copiedId.value = m.id;
    setTimeout(() => { if (copiedId.value === m.id) copiedId.value = null; }, 1500);
  } catch { /* clipboard unavailable */ }
};
</script>

<template>
  <div ref="scroller" class="flex-1 overflow-y-auto bg-gray-50 px-3 py-4 sm:px-6" aria-live="polite">
    <div v-if="!messages.length" class="flex h-full flex-col items-center justify-center text-center">
      <Avatar :id="other.id" :name="other.name" :src="other.photo_url" size="lg" />
      <p class="mt-3 text-sm font-medium text-gray-900">Start your conversation with {{ other.name }}</p>
      <p class="mt-1 text-xs text-gray-500">Messages are private between the two of you.</p>
    </div>

    <section v-for="day in days" :key="day.date" class="space-y-3">
      <div class="flex justify-center py-2">
        <span class="rounded-full bg-white px-3 py-0.5 text-[11px] font-medium text-gray-500 shadow-sm ring-1 ring-gray-200">{{ dayLabel(day.date) }}</span>
      </div>

      <div v-for="(run, ri) in day.runs" :key="ri" class="flex items-end gap-2" :class="run.senderId === me.id ? 'justify-end' : 'justify-start'">
        <Avatar v-if="run.senderId !== me.id" :id="other.id" :name="other.name" :src="other.photo_url" size="sm" class="mb-5 hidden sm:flex" />

        <div class="flex max-w-[85%] flex-col gap-1 sm:max-w-[70%]" :class="run.senderId === me.id ? 'items-end' : 'items-start'">
          <div v-for="(m, mi) in run.messages" :key="m.id" class="group flex items-center gap-1" :class="run.senderId === me.id ? 'flex-row-reverse' : ''">
            <div class="whitespace-pre-wrap break-words px-3.5 py-2 text-sm leading-relaxed shadow-sm"
              :class="[
                run.senderId === me.id ? 'bg-indigo-600 text-white' : 'border border-gray-200 bg-white text-gray-900',
                run.senderId === me.id
                  ? ['rounded-2xl', mi === run.messages.length - 1 ? 'rounded-br-md' : '']
                  : ['rounded-2xl', mi === run.messages.length - 1 ? 'rounded-bl-md' : ''],
                m.temp ? 'opacity-70' : '',
              ]">{{ m.message }}</div>

            <!-- Inline actions (hover on desktop, always reachable by keyboard) -->
            <div v-if="!m.temp" class="flex gap-0.5 opacity-0 transition-opacity focus-within:opacity-100 group-hover:opacity-100">
              <button type="button" @click="copy(m)" class="rounded-md p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-700" :aria-label="copiedId === m.id ? 'Copied' : 'Copy message'">
                <Check v-if="copiedId === m.id" class="h-3.5 w-3.5 text-green-600" />
                <Copy v-else class="h-3.5 w-3.5" />
              </button>
              <button v-if="m.sender_id === me.id" type="button" @click="emit('delete', m)" class="rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600" aria-label="Delete message">
                <Trash2 class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
          <p class="px-1 text-[11px] text-gray-400">
            {{ run.messages[run.messages.length - 1].temp ? 'Sending…' : timeLabel(run.messages[run.messages.length - 1].created_at) }}
            <template v-if="run.senderId === me.id && !run.messages[run.messages.length - 1].temp">
              · {{ run.messages[run.messages.length - 1].read_at ? 'Read' : 'Sent' }}
            </template>
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
