<template>
  <AppLayout>
    <div class="py-12 bg-gray-50 min-h-screen">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl text-gray-900 font-semibold">Applied Jobs</h1>
          <p class="mt-1 text-sm text-gray-500">Status updates appear here live — no need to refresh.</p>
        </div>

        <div v-if="!applications.length" class="rounded-xl bg-white p-10 text-center text-gray-500 shadow-sm">
          You haven't applied to any jobs yet.
          <Link href="/Jobs" class="text-indigo-600 font-medium hover:underline">Browse jobs</Link>
        </div>

        <ul class="space-y-4">
          <li v-for="app in applications" :key="app.id"
              class="rounded-xl bg-white p-6 shadow-sm transition-shadow"
              :class="{ 'ring-2 ring-indigo-400': flashId === app.id }">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
              <div>
                <Link :href="`/Jobs/job/${app.job_listing.id}`" class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                  {{ app.job_listing.title }}
                </Link>
                <p class="text-sm text-gray-500">{{ app.job_listing.employer?.name }}</p>
              </div>
              <span class="self-start rounded-full px-3 py-1 text-xs font-semibold" :class="stageBadgeClass(app.application_status)">
                {{ STAGE_LABELS[app.application_status] ?? app.application_status }}
              </span>
            </div>

            <!-- Timeline -->
            <ol class="mt-6 grid grid-cols-5 gap-2" :aria-label="`Progress for ${app.job_listing.title}`">
              <li v-for="stage in PIPELINE" :key="stage" class="text-center">
                <div class="h-2 rounded-full" :class="stageBarClass(app, stage)"></div>
                <p class="mt-2 text-xs font-medium" :class="reached(app, stage) ? 'text-gray-900' : 'text-gray-400'">
                  {{ STAGE_LABELS[stage] }}
                </p>
                <p v-if="reachedAt(app, stage)" class="text-[11px] text-gray-500">{{ formatDate(reachedAt(app, stage)) }}</p>
              </li>
            </ol>
            <p v-if="app.application_status === 'rejected'" class="mt-4 text-sm text-red-700">
              The employer decided not to move forward ({{ formatDate(reachedAt(app, 'rejected')) }}). You can apply again from the job page.
            </p>
          </li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { useNotificationStore } from '@/store/notifications';
import { PIPELINE, STAGE_LABELS, stageBadgeClass } from '@/utils/stages';

const props = defineProps({
  appliedJobs: { type: Array, default: () => [] },
});

const applications = ref(props.appliedJobs.map((a) => ({ ...a, timeline: [...a.timeline] })));
const flashId = ref(null);
const store = useNotificationStore();

onMounted(() => store.clearApplicationUpdates());

// Live updates from the private user channel (see store/notifications.js).
watch(() => store.lastApplicationUpdate, (event) => {
  if (!event) return;
  const app = applications.value.find((a) => a.id === event.application_id);
  if (!app) return;
  app.application_status = event.status;
  app.timeline.push({ status: event.status, at: event.at });
  flashId.value = app.id;
  setTimeout(() => { if (flashId.value === app.id) flashId.value = null; }, 2500);
  store.clearApplicationUpdates();
});

const lastIndexOf = (app, stage) => {
  for (let i = app.timeline.length - 1; i >= 0; i--) {
    if (app.timeline[i].status === stage) return i;
  }
  return -1;
};

// The current run of the pipeline starts at the most recent "applied" (re-applications restart it).
const currentRun = (app) => {
  const start = lastIndexOf(app, 'applied');
  return start === -1 ? app.timeline : app.timeline.slice(start);
};

const reachedAt = (app, stage) => [...currentRun(app)].reverse().find((t) => t.status === stage)?.at ?? null;

const reached = (app, stage) => {
  if (app.application_status === 'rejected') return !!reachedAt(app, stage);
  return PIPELINE.indexOf(stage) <= PIPELINE.indexOf(app.application_status);
};

const stageBarClass = (app, stage) => {
  if (!reached(app, stage)) return 'bg-gray-200';
  if (app.application_status === 'rejected') return 'bg-gray-400';
  return stage === app.application_status ? 'bg-indigo-600' : 'bg-indigo-300';
};

const formatDate = (iso) => (iso ? new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short' }) : '');
</script>
