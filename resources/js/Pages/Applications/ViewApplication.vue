<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layout/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Tabs from '@/Components/UI/Tabs.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import StageBadge from '@/Components/UI/StageBadge.vue';
import { Check, X, FileText, Search, MessageSquare, Users, ChevronDown } from 'lucide-vue-next';
import { STAGE_LABELS, nextStages } from '@/utils/stages';

const props = defineProps({
  applications: { type: Array, required: true },
  ranked: { type: Boolean, default: true },
});

// ---- Tabs + filters (kept in the URL so dashboard links can deep-link) ----
const TAB_STAGES = {
  all: null,
  new: ['applied', 'viewed'],
  progress: ['shortlisted', 'interview'],
  offer: ['offer'],
  rejected: ['rejected'],
};
const params = new URLSearchParams(window.location.search);
const activeTab = ref(TAB_STAGES[params.get('tab')] !== undefined ? params.get('tab') : 'all');
const jobFilter = ref(params.get('job') || '');
const searchQuery = ref('');

watch([activeTab, jobFilter], () => {
  const url = new URL(window.location);
  activeTab.value === 'all' ? url.searchParams.delete('tab') : url.searchParams.set('tab', activeTab.value);
  jobFilter.value ? url.searchParams.set('job', jobFilter.value) : url.searchParams.delete('job');
  window.history.replaceState(window.history.state, '', url);
});

const jobs = computed(() => {
  const map = new Map();
  props.applications.forEach((a) => a.job_listing && map.set(String(a.job_listing.id), a.job_listing.title));
  return [...map].map(([id, title]) => ({ id, title }));
});

const inJob = computed(() => props.applications.filter((a) => !jobFilter.value || String(a.job_listing?.id) === jobFilter.value));
const countFor = (key) => (TAB_STAGES[key] ? inJob.value.filter((a) => TAB_STAGES[key].includes(a.application_status)).length : inJob.value.length);

const tabs = computed(() => [
  { key: 'all', label: 'All', count: countFor('all') },
  { key: 'new', label: 'New', count: countFor('new') },
  { key: 'progress', label: 'In progress', count: countFor('progress') },
  { key: 'offer', label: 'Offers', count: countFor('offer') },
  { key: 'rejected', label: 'Rejected', count: countFor('rejected') },
]);

const visible = computed(() => {
  const stages = TAB_STAGES[activeTab.value];
  const q = searchQuery.value.trim().toLowerCase();

  return inJob.value.filter((a) => (!stages || stages.includes(a.application_status))
    && (!q || a.job_seeker?.name?.toLowerCase().includes(q) || a.job_listing?.title?.toLowerCase().includes(q)));
});

const toggleRanking = () => {
  router.get('/view-applications', {
    ...(props.ranked ? { ranked: 0 } : {}),
    ...(activeTab.value !== 'all' ? { tab: activeTab.value } : {}),
    ...(jobFilter.value ? { job: jobFilter.value } : {}),
  }, { preserveScroll: true });
};

// ---- "Why?" panel ----
const expanded = ref(null);
const toggleWhy = (id) => { expanded.value = expanded.value === id ? null : id; };
const scoreClass = (s) => (s === null ? 'bg-gray-100 text-gray-500' : s >= 75 ? 'bg-green-100 text-green-700' : s >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600');

// ---- Documents (opening one records "viewed") ----
const doc = ref(null);
const openDoc = (title, content, application) => {
  doc.value = { title, content };
  if (application?.application_status === 'applied') {
    axios.post(`/applications/${application.id}/viewed`)
      .then(() => { application.application_status = 'viewed'; })
      .catch(() => {});
  }
};

// ---- Stage changes ----
const confirm = ref(null); // { id, status, reopen }
const isProcessing = ref(false);
const askMove = (id, status, reopen = false) => { confirm.value = { id, status, reopen }; };

const doMove = () => {
  const { id, status, reopen } = confirm.value;
  router.put(`/applications/${id}`, { application_status: status, reopen }, {
    preserveScroll: true,
    preserveState: true,
    onStart: () => { isProcessing.value = true; },
    onFinish: () => { isProcessing.value = false; },
    onSuccess: () => { confirm.value = null; },
  });
};

const onKey = (e) => {
  if (e.key !== 'Escape') return;
  doc.value = null;
  confirm.value = null;
};
onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));

const formatDate = (iso) => (iso ? new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short' }) : '');
</script>

<template>
  <AppLayout>
    <div class="page">
      <PageHeader title="Applicants" subtitle="Review candidates for your roles. You make every decision.">
        <template #actions>
          <button type="button" @click="toggleRanking" class="btn-secondary">
            {{ ranked ? 'Sorted by match · turn off' : 'Newest first · sort by match' }}
          </button>
        </template>
        <template #tabs>
          <Tabs v-model="activeTab" :tabs="tabs" label="Applicant stages" />
        </template>
      </PageHeader>

      <!-- Filters -->
      <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input v-model="searchQuery" type="search" placeholder="Search applicant or role" class="input pl-9" aria-label="Search applicants" />
        </div>
        <select v-model="jobFilter" class="input sm:w-64" aria-label="Filter by role">
          <option value="">All roles</option>
          <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.title }}</option>
        </select>
      </div>
      <p v-if="ranked" class="mb-4 text-xs text-gray-500">
        Match scores use only skills and years of experience. They're a suggestion: nobody is rejected automatically.
      </p>

      <EmptyState v-if="!visible.length" :icon="Users"
        :title="applications.length ? 'No applicants here' : 'No applicants yet'"
        :text="applications.length ? 'Try another tab or role.' : 'When people apply to your roles, they show up here.'" />

      <!-- Desktop table -->
      <div v-else class="card hidden overflow-hidden md:block">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <tr>
              <th class="px-4 py-3 font-medium">Applicant</th>
              <th class="px-4 py-3 font-medium">Role</th>
              <th v-if="ranked" class="px-4 py-3 font-medium">Match</th>
              <th class="px-4 py-3 font-medium">Stage</th>
              <th class="px-4 py-3 font-medium">Documents</th>
              <th class="px-4 py-3 font-medium">Move to</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="a in visible" :key="a.id">
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900">{{ a.job_seeker?.name || 'Applicant' }}</span>
                    <Link v-if="a.job_seeker?.user_id" :href="`/messages/${a.job_seeker.user_id}`" class="text-gray-400 hover:text-indigo-600" title="Message applicant">
                      <MessageSquare class="h-4 w-4" />
                    </Link>
                  </div>
                  <p class="text-xs text-gray-500">Applied {{ formatDate(a.created_at) }}</p>
                </td>
                <td class="px-4 py-3 text-gray-700">{{ a.job_listing?.title }}</td>
                <td v-if="ranked" class="px-4 py-3">
                  <button v-if="a.match" type="button" @click="toggleWhy(a.id)" class="inline-flex items-center gap-1">
                    <span class="badge" :class="scoreClass(a.match.score)">{{ a.match.score === null ? '—' : `${a.match.score}%` }}</span>
                    <ChevronDown class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': expanded === a.id }" />
                    <span class="sr-only">Why this score</span>
                  </button>
                </td>
                <td class="px-4 py-3"><StageBadge :status="a.application_status" /></td>
                <td class="px-4 py-3">
                  <div class="flex gap-1">
                    <button type="button" class="btn-ghost btn-sm" @click="openDoc('Resume', a.resume_text, a)"><FileText class="h-4 w-4" /> Resume</button>
                    <button type="button" class="btn-ghost btn-sm" @click="openDoc('Cover letter', a.cover_letter, a)">Letter</button>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div v-if="nextStages(a.application_status).length" class="flex flex-wrap gap-1">
                    <button v-for="s in nextStages(a.application_status)" :key="s" type="button" :disabled="isProcessing"
                      @click="askMove(a.id, s)"
                      class="btn btn-sm border"
                      :class="s === 'rejected' ? 'border-red-200 text-red-700 hover:bg-red-50' : 'border-indigo-200 text-indigo-700 hover:bg-indigo-50'">
                      {{ s === 'rejected' ? 'Reject' : STAGE_LABELS[s] }}
                    </button>
                  </div>
                  <button v-else type="button" class="btn-secondary btn-sm" @click="askMove(a.id, 'shortlisted', true)">Reopen…</button>
                </td>
              </tr>
              <tr v-if="ranked && expanded === a.id && a.match" class="bg-indigo-50/40">
                <td :colspan="6" class="px-4 py-4">
                  <div class="grid gap-4 text-sm md:grid-cols-3">
                    <div>
                      <p class="mb-1 font-medium text-gray-700">Has</p>
                      <p v-if="!a.match.matched.length" class="text-gray-500">None of the listed skills</p>
                      <span v-for="s in a.match.matched" :key="s" class="badge mb-1 mr-1 bg-green-100 text-green-700">{{ s }}</span>
                    </div>
                    <div>
                      <p class="mb-1 font-medium text-gray-700">Missing</p>
                      <p v-if="!a.match.missing.length" class="text-gray-500">Nothing</p>
                      <span v-for="s in a.match.missing" :key="s" class="badge mb-1 mr-1 bg-red-100 text-red-700">{{ s }}</span>
                    </div>
                    <div>
                      <p class="mb-1 font-medium text-gray-700">History</p>
                      <ul class="space-y-1 text-xs text-gray-600">
                        <li v-for="(h, i) in a.history" :key="i">{{ STAGE_LABELS[h.to] ?? h.to }} · {{ h.by }} · {{ new Date(h.at).toLocaleString() }}</li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <ul v-if="visible.length" class="space-y-3 md:hidden">
        <li v-for="a in visible" :key="a.id" class="card card-body space-y-3">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="font-semibold text-gray-900">{{ a.job_seeker?.name || 'Applicant' }}</p>
              <p class="truncate text-sm text-gray-500">{{ a.job_listing?.title }} · {{ formatDate(a.created_at) }}</p>
            </div>
            <StageBadge :status="a.application_status" class="shrink-0" />
          </div>

          <button v-if="ranked && a.match" type="button" @click="toggleWhy(a.id)" class="flex w-full items-center justify-between text-sm">
            <span class="text-gray-600">Match</span>
            <span class="flex items-center gap-1">
              <span class="badge" :class="scoreClass(a.match.score)">{{ a.match.score === null ? '—' : `${a.match.score}%` }}</span>
              <ChevronDown class="h-4 w-4 text-gray-400" :class="{ 'rotate-180': expanded === a.id }" />
            </span>
          </button>
          <div v-if="ranked && expanded === a.id && a.match" class="space-y-2 rounded-lg bg-indigo-50/50 p-3 text-xs">
            <p><span class="font-medium text-gray-700">Has:</span> {{ a.match.matched.join(', ') || 'none' }}</p>
            <p><span class="font-medium text-gray-700">Missing:</span> {{ a.match.missing.join(', ') || 'nothing' }}</p>
          </div>

          <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-secondary btn-sm" @click="openDoc('Resume', a.resume_text, a)"><FileText class="h-4 w-4" /> Resume</button>
            <button type="button" class="btn-secondary btn-sm" @click="openDoc('Cover letter', a.cover_letter, a)">Letter</button>
            <Link v-if="a.job_seeker?.user_id" :href="`/messages/${a.job_seeker.user_id}`" class="btn-secondary btn-sm"><MessageSquare class="h-4 w-4" /> Message</Link>
          </div>

          <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-3">
            <template v-if="nextStages(a.application_status).length">
              <button v-for="s in nextStages(a.application_status)" :key="s" type="button" :disabled="isProcessing"
                @click="askMove(a.id, s)" class="btn btn-sm border"
                :class="s === 'rejected' ? 'border-red-200 text-red-700' : 'border-indigo-200 text-indigo-700'">
                {{ s === 'rejected' ? 'Reject' : STAGE_LABELS[s] }}
              </button>
            </template>
            <button v-else type="button" class="btn-secondary btn-sm" @click="askMove(a.id, 'shortlisted', true)">Reopen…</button>
          </div>
        </li>
      </ul>
    </div>

    <!-- Document viewer -->
    <div v-if="doc" class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 sm:items-center sm:p-4" @click="doc = null">
      <div class="max-h-[85vh] w-full max-w-2xl overflow-hidden rounded-t-2xl bg-white shadow-xl sm:rounded-2xl" @click.stop role="dialog" aria-modal="true" :aria-label="doc.title">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
          <h3 class="font-semibold text-gray-900">{{ doc.title }}</h3>
          <button type="button" class="btn-ghost btn-sm" @click="doc = null" aria-label="Close"><X class="h-5 w-5" /></button>
        </div>
        <pre class="max-h-[65vh] overflow-y-auto whitespace-pre-wrap px-5 py-4 font-sans text-sm text-gray-700">{{ doc.content }}</pre>
      </div>
    </div>

    <!-- Confirm stage change -->
    <div v-if="confirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click="confirm = null">
      <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl" @click.stop role="dialog" aria-modal="true">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full" :class="confirm.status === 'rejected' ? 'bg-red-100' : 'bg-green-100'">
          <X v-if="confirm.status === 'rejected'" class="h-6 w-6 text-red-600" />
          <Check v-else class="h-6 w-6 text-green-600" />
        </div>
        <h3 class="mt-4 text-lg font-semibold text-gray-900">
          {{ confirm.reopen ? 'Reopen application?' : `Move to ${STAGE_LABELS[confirm.status]}?` }}
        </h3>
        <p class="mt-2 text-sm text-gray-500">
          <template v-if="confirm.reopen">It moves back to {{ STAGE_LABELS[confirm.status] }}. This is recorded in the history.</template>
          <template v-else>The applicant sees this in real time{{ ['shortlisted', 'interview', 'offer'].includes(confirm.status) ? ' and gets an email' : '' }}.</template>
        </p>
        <div class="mt-6 flex justify-center gap-3">
          <button type="button" class="btn-secondary" @click="confirm = null">Cancel</button>
          <button type="button" :class="confirm.status === 'rejected' ? 'btn-danger' : 'btn-primary'" :disabled="isProcessing" @click="doMove">
            {{ isProcessing ? 'Saving…' : 'Confirm' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
