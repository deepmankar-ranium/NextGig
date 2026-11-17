<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Sparkles, RotateCcw, Copy, Check, AlertCircle, Briefcase, X } from 'lucide-vue-next';
import AppLayout from '@/Layout/AppLayout.vue';
import Composer from '@/Components/Chat/Composer.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import { formatAssistant, timeLabel } from '@/utils/chat';

const props = defineProps({
  focusJob: { type: Object, default: null }, // { id, title } from ?job=
  prefill: { type: String, default: '' }, // from ?q=
  myJobs: { type: Array, default: () => [] }, // employers: their postings
});

const page = usePage();
const me = computed(() => page.props.auth.user);
const isEmployer = computed(() => me.value?.role_id === 2);

const SUGGESTIONS = {
  seeker: [
    'Which skills should I learn next for backend roles?',
    'How do I prepare for a technical interview?',
    'Write a short, specific opening line for a cover letter',
    'How can I explain a career gap?',
  ],
  employer: [
    'Write 5 interview questions for a Laravel developer',
    'How do I make a job post more inclusive?',
    'What should a fair take-home task look like?',
    'How do I give helpful feedback to rejected candidates?',
  ],
};
// The job the assistant should focus on; sent with every message while set.
const focusId = ref(props.focusJob?.id ?? '');
const focusTitle = computed(() => props.focusJob?.id === focusId.value
  ? props.focusJob.title
  : props.myJobs.find((j) => j.id === focusId.value)?.title);

const FOCUSED = {
  seeker: (t) => [`What am I missing for ${t}?`, `Help me prepare for an interview for ${t}`, `Write a cover letter opening for ${t}`, `Is ${t} a good fit for me?`],
  employer: (t) => [`Write 6 interview questions for ${t}`, `How can I make the ${t} post more inclusive?`, `Design a fair take-home task for ${t}`, `How do I move ${t} applicants forward faster?`],
};
const suggestions = computed(() => {
  const key = isEmployer.value ? 'employer' : 'seeker';
  return focusTitle.value ? FOCUSED[key](focusTitle.value) : SUGGESTIONS[key];
});

const messages = ref([]); // { role: 'user'|'bot', content, at, error? }
const draft = ref(props.prefill || '');
const loading = ref(true);
const thinking = ref(false);
const confirmClear = ref(false);
const copied = ref(null);
const scroller = ref(null);
const composer = ref(null);

const scrollToBottom = () => nextTick(() => scroller.value?.scrollTo({ top: scroller.value.scrollHeight, behavior: 'smooth' }));

const load = async () => {
  try {
    const { data } = await axios.get('/chat-history');
    messages.value = data.history.map((m) => ({ role: m.sender === 'user' ? 'user' : 'bot', content: m.message, at: m.created_at }));
  } catch {
    messages.value = [];
  } finally {
    loading.value = false;
    scrollToBottom();
  }
};

const send = async (text = draft.value) => {
  const content = text.trim();
  if (!content || thinking.value) return;

  draft.value = '';
  messages.value.push({ role: 'user', content, at: new Date().toISOString() });
  thinking.value = true;
  scrollToBottom();

  try {
    const { data } = await axios.post('/chat', { message: content, job_id: focusId.value || null });
    if (typeof data?.response !== 'string' || !data.response.trim()) throw new Error('Empty reply');
    messages.value.push({ role: 'bot', content: data.response, at: new Date().toISOString() });
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.response || 'The assistant is unavailable right now. Please try again in a minute.';
    messages.value.push({ role: 'bot', content: msg, at: new Date().toISOString(), error: true });
  } finally {
    thinking.value = false;
    scrollToBottom();
    composer.value?.focus();
  }
};

const clear = async () => {
  confirmClear.value = false;
  await axios.post('/clear-chat').catch(() => {});
  messages.value = [];
};

const copy = async (i) => {
  try {
    await navigator.clipboard.writeText(messages.value[i].content);
    copied.value = i;
    setTimeout(() => { if (copied.value === i) copied.value = null; }, 1500);
  } catch { /* clipboard unavailable */ }
};

onMounted(load);
</script>

<template>
  <AppLayout full-height>
    <div class="flex h-[calc(100dvh-8rem)] flex-col bg-white md:h-[calc(100dvh-4rem)]">
      <!-- Header -->
      <header class="shrink-0 border-b border-gray-200">
        <div class="mx-auto flex h-14 max-w-3xl items-center gap-3 px-4">
          <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100"><Sparkles class="h-4 w-4 text-indigo-600" /></span>
          <div class="min-w-0 flex-1">
            <h1 class="text-sm font-semibold text-gray-900">Career assistant</h1>
            <p class="truncate text-xs text-gray-500">
              {{ isEmployer ? 'Knows your postings and applicant counts.' : 'Knows your skills, applications and matches.' }} AI suggestions only.
            </p>
          </div>
          <select v-if="isEmployer && myJobs.length" v-model="focusId" aria-label="Which job to talk about"
            class="hidden max-w-[14rem] rounded-lg border-gray-300 py-1.5 pl-2 pr-8 text-xs text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 sm:block">
            <option value="">All my postings</option>
            <option v-for="j in myJobs" :key="j.id" :value="j.id">{{ j.title }}</option>
          </select>
          <button v-if="messages.length" type="button" @click="confirmClear = true" class="btn-ghost btn-sm" title="Start a new conversation">
            <RotateCcw class="h-4 w-4" /><span class="hidden sm:inline">New chat</span>
          </button>
        </div>
      </header>

      <!-- Conversation -->
      <div ref="scroller" class="flex-1 overflow-y-auto" aria-live="polite">
        <div class="mx-auto max-w-3xl px-4 py-6">
          <div v-if="loading" class="flex justify-center py-20"><span class="h-6 w-6 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" /></div>

          <!-- Welcome -->
          <div v-else-if="!messages.length" class="flex flex-col items-center pt-8 text-center sm:pt-16">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600"><Sparkles class="h-6 w-6 text-white" /></span>
            <h2 class="mt-4 text-xl font-semibold text-gray-900">How can I help{{ me?.full_name ? `, ${me.full_name.split(' ')[0]}` : '' }}?</h2>
            <p class="mt-1 max-w-md text-sm text-gray-500">
              {{ isEmployer ? 'Ask about your job posts, interviews or hiring fairly. I can see your postings and how many people are at each stage.' : 'Ask about your job search, resume, cover letters or interviews. I can see your skills, your applications and your best matches.' }}
            </p>
            <div class="mt-8 grid w-full gap-2 sm:grid-cols-2">
              <button v-for="s in suggestions" :key="s" type="button" @click="send(s)"
                class="rounded-xl border border-gray-200 px-4 py-3 text-left text-sm text-gray-700 transition-colors hover:border-indigo-300 hover:bg-indigo-50">
                {{ s }}
              </button>
            </div>
          </div>

          <!-- Messages -->
          <div v-else class="space-y-6">
            <div v-for="(m, i) in messages" :key="i" class="flex gap-3" :class="m.role === 'user' ? 'justify-end' : ''">
              <template v-if="m.role === 'user'">
                <div class="max-w-[85%] whitespace-pre-wrap break-words rounded-2xl rounded-br-md bg-indigo-600 px-4 py-2.5 text-sm leading-relaxed text-white">{{ m.content }}</div>
              </template>
              <template v-else>
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full" :class="m.error ? 'bg-red-100' : 'bg-indigo-100'">
                  <AlertCircle v-if="m.error" class="h-4 w-4 text-red-600" />
                  <Sparkles v-else class="h-4 w-4 text-indigo-600" />
                </span>
                <div class="group min-w-0 flex-1">
                  <div v-if="m.error" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm text-red-800">{{ m.content }}</div>
                  <div v-else class="break-words text-sm leading-relaxed text-gray-800 [&>p:first-child]:mt-0" v-html="formatAssistant(m.content)"></div>
                  <div v-if="!m.error" class="mt-1 flex items-center gap-2 text-[11px] text-gray-400">
                    <span>{{ timeLabel(m.at) }}</span>
                    <button type="button" @click="copy(i)" class="inline-flex items-center gap-1 rounded px-1 py-0.5 opacity-0 transition-opacity hover:bg-gray-100 hover:text-gray-700 focus:opacity-100 group-hover:opacity-100">
                      <Check v-if="copied === i" class="h-3 w-3 text-green-600" /><Copy v-else class="h-3 w-3" />
                      {{ copied === i ? 'Copied' : 'Copy' }}
                    </button>
                  </div>
                </div>
              </template>
            </div>

            <!-- Typing indicator -->
            <div v-if="thinking" class="flex gap-3" aria-label="Assistant is typing">
              <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-100"><Sparkles class="h-4 w-4 text-indigo-600" /></span>
              <div class="flex items-center gap-1 rounded-2xl bg-gray-100 px-4 py-3">
                <span v-for="d in 3" :key="d" class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" :style="{ animationDelay: `${d * 120}ms` }" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Composer -->
      <div class="shrink-0">
        <div class="mx-auto max-w-3xl">
          <div v-if="focusTitle" class="flex items-center gap-2 px-4 pt-2 text-xs text-gray-600">
            <Briefcase class="h-3.5 w-3.5 text-gray-400" />
            <span class="truncate">Talking about <span class="font-medium text-gray-900">{{ focusTitle }}</span></span>
            <button type="button" class="rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Stop focusing on this job" @click="focusId = ''"><X class="h-3.5 w-3.5" /></button>
          </div>
          <Composer ref="composer" v-model="draft" :busy="thinking" :max-length="1000" placeholder="Ask about jobs, resumes, interviews…" autofocus class="border-t-0 sm:pb-4" @send="send()" />
        </div>
      </div>
    </div>

    <ConfirmDialog v-if="confirmClear" title="Start a new conversation?" message="This clears your chat history with the assistant."
      confirm-label="Clear" danger @confirm="clear" @cancel="confirmClear = false" />
  </AppLayout>
</template>
