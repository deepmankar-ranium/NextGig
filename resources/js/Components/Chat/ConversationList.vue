<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, MessageSquare } from 'lucide-vue-next';
import Avatar from './Avatar.vue';
import { listTime } from '@/utils/chat';

const props = defineProps({
  conversations: { type: Array, required: true },
  selectedId: { type: Number, default: null },
});

const query = ref('');
const filtered = computed(() => {
  const q = query.value.trim().toLowerCase();
  return q ? props.conversations.filter((c) => c.name.toLowerCase().includes(q)) : props.conversations;
});
</script>

<template>
  <aside class="flex h-full flex-col border-r border-gray-200 bg-white">
    <div class="space-y-3 border-b border-gray-100 px-4 pb-3 pt-4">
      <h2 class="text-lg font-semibold text-gray-900">Messages</h2>
      <div class="relative">
        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        <input v-model="query" type="search" placeholder="Search conversations" aria-label="Search conversations"
          class="w-full rounded-lg border-0 bg-gray-100 py-2 pl-9 pr-3 text-sm placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-indigo-500" />
      </div>
    </div>

    <ul v-if="filtered.length" class="flex-1 overflow-y-auto py-1">
      <li v-for="c in filtered" :key="c.id">
        <Link :href="`/messages/${c.id}`" preserve-scroll
          class="mx-2 flex items-center gap-3 rounded-lg px-3 py-3 transition-colors"
          :class="c.id === selectedId ? 'bg-indigo-50' : 'hover:bg-gray-50'"
          :aria-current="c.id === selectedId ? 'true' : undefined">
          <Avatar :id="c.id" :name="c.name" :src="c.photo_url" />
          <div class="min-w-0 flex-1">
            <div class="flex items-baseline justify-between gap-2">
              <p class="truncate text-sm" :class="c.unread ? 'font-semibold text-gray-900' : 'font-medium text-gray-900'">{{ c.name }}</p>
              <span class="shrink-0 text-[11px]" :class="c.unread ? 'font-semibold text-indigo-600' : 'text-gray-400'">{{ listTime(c.last_at) }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <p class="truncate text-xs" :class="c.unread ? 'text-gray-800' : 'text-gray-500'">
                <template v-if="c.last_message"><span v-if="c.last_from_me">You: </span>{{ c.last_message }}</template>
                <span v-else class="italic">{{ c.role }} · start the conversation</span>
              </p>
              <span v-if="c.unread" class="flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[11px] font-semibold text-white">
                {{ c.unread }}
              </span>
            </div>
          </div>
        </Link>
      </li>
    </ul>

    <div v-else class="flex flex-1 flex-col items-center justify-center px-6 text-center">
      <div class="mb-3 rounded-full bg-indigo-50 p-3"><MessageSquare class="h-6 w-6 text-indigo-600" /></div>
      <p class="text-sm font-medium text-gray-900">{{ query ? 'No matches' : 'No conversations yet' }}</p>
      <p v-if="!query" class="mt-1 text-xs text-gray-500">Message an employer from a job page, or an applicant from your Applicants list.</p>
    </div>
  </aside>
</template>
