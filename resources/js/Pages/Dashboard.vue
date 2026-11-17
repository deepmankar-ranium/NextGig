<script setup>
import { computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useNotificationStore } from '@/store/notifications';
import AppLayout from '@/Layout/AppLayout.vue';
import StageBadge from '@/Components/UI/StageBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { FileText, Briefcase, Sparkles, Plus, Users, ArrowRight, Inbox as InboxIcon } from 'lucide-vue-next';

const props = defineProps({
  role: String,
  name: String,
  // seeker
  hasProfile: Boolean,
  skillCount: Number,
  topMatches: { type: Array, default: () => [] },
  counts: { type: Object, default: () => ({}) },
  recentApplications: { type: Array, default: () => [] },
  // employer
  company: String,
  stats: { type: Object, default: () => ({}) },
  recentApplicants: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
});

// A live status change (toast) also refreshes the counts and recent list here.
const store = useNotificationStore();
watch(() => store.lastApplicationUpdate, (e) => { if (e) router.reload({ preserveScroll: true }); });


const firstName = computed(() => (props.name || '').split(' ')[0]);
const greeting = computed(() => {
  const h = new Date().getHours();
  return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
});
const scoreClass = (s) => (s >= 75 ? 'bg-green-100 text-green-700' : s >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600');
const ago = (iso) => {
  if (!iso) return '';
  const d = (Date.now() - new Date(iso).getTime()) / 86400000;
  return d < 1 ? 'today' : d < 2 ? 'yesterday' : `${Math.floor(d)} days ago`;
};
</script>

<template>
  <AppLayout>
    <div class="page space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ greeting }}, {{ firstName }}</h1>
        <p class="mt-1 text-sm text-gray-600" v-if="role === 'employer'">Here's what's happening at {{ company }}.</p>
        <p class="mt-1 text-sm text-gray-600" v-else-if="role === 'seeker'">Your matches and applications at a glance.</p>
      </div>

      <!-- ================= Job seeker ================= -->
      <template v-if="role === 'seeker'">
        <div v-if="!hasProfile" class="card card-body flex flex-col gap-4 border-indigo-200 bg-indigo-50 sm:flex-row sm:items-center">
          <div class="rounded-full bg-white p-3"><FileText class="h-6 w-6 text-indigo-600" /></div>
          <div class="flex-1">
            <p class="font-semibold text-indigo-900">Add your resume to see match scores</p>
            <p class="text-sm text-indigo-800">We'll pull out your skills so every job shows how well you fit and what's missing.</p>
          </div>
          <Link href="/resume" class="btn-primary">Upload resume</Link>
        </div>

        <div class="grid grid-cols-3 gap-3 sm:gap-4">
          <Link href="/applied-jobs" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Active</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ counts.active ?? 0 }}</p>
          </Link>
          <Link href="/applied-jobs" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Interviews</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ counts.interviews ?? 0 }}</p>
          </Link>
          <Link href="/applied-jobs?tab=offers" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Offers</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ counts.offers ?? 0 }}</p>
          </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
          <section class="lg:col-span-3">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="card-title flex items-center gap-2"><Sparkles class="h-4 w-4 text-indigo-600" /> Top matches for you</h2>
              <Link href="/jobs?sort=match" class="text-sm font-medium text-indigo-600 hover:underline">See all</Link>
            </div>
            <div v-if="topMatches.length" class="grid gap-3 sm:grid-cols-2">
              <Link v-for="job in topMatches" :key="job.id" :href="`/jobs/${job.id}`" class="card card-body block hover:border-indigo-300">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="truncate font-semibold text-gray-900">{{ job.title }}</p>
                    <p class="truncate text-sm text-gray-500">{{ job.employer }}</p>
                  </div>
                  <span class="badge shrink-0" :class="scoreClass(job.score)">{{ job.score }}%</span>
                </div>
                <p v-if="job.missing.length" class="mt-3 text-xs text-gray-500">Missing: {{ job.missing.join(', ') }}</p>
                <p v-else class="mt-3 text-xs text-green-700">You have every listed skill</p>
              </Link>
            </div>
            <EmptyState v-else :icon="Briefcase" title="No matches yet"
              :text="hasProfile ? 'New jobs will show up here as soon as they match your skills.' : 'Upload your resume to get personalised matches.'">
              <Link href="/jobs" class="btn-secondary">Browse all jobs</Link>
            </EmptyState>
            <p class="mt-2 text-xs text-gray-400">AI suggestion, not a hiring decision.</p>
          </section>

          <section class="lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="card-title">Recent applications</h2>
              <Link href="/applied-jobs" class="text-sm font-medium text-indigo-600 hover:underline">View all</Link>
            </div>
            <ul v-if="recentApplications.length" class="card divide-y divide-gray-100">
              <li v-for="a in recentApplications" :key="a.id">
                <Link :href="`/jobs/${a.job_id}`" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-gray-900">{{ a.title }}</p>
                    <p class="truncate text-xs text-gray-500">{{ a.employer }} · updated {{ ago(a.updated_at) }}</p>
                  </div>
                  <StageBadge :status="a.status" class="shrink-0" />
                </Link>
              </li>
            </ul>
            <EmptyState v-else :icon="Briefcase" title="No applications yet" text="When you apply, you'll follow every step here in real time." />
          </section>
        </div>
      </template>

      <!-- ================= Employer ================= -->
      <template v-else-if="role === 'employer'">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
          <Link href="/posted-jobs" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Open roles</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.roles }}</p>
          </Link>
          <Link href="/view-applications" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Applicants</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.applicants }}</p>
          </Link>
          <Link href="/view-applications?tab=new" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">New, unreviewed</p>
            <p class="mt-1 text-2xl font-bold text-indigo-600">{{ stats.new }}</p>
          </Link>
          <Link href="/view-applications?tab=progress" class="card card-body hover:border-indigo-300">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">In progress</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ stats.inProgress }}</p>
          </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
          <section class="lg:col-span-3">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="card-title flex items-center gap-2"><Users class="h-4 w-4 text-indigo-600" /> Latest applicants</h2>
              <Link href="/view-applications" class="text-sm font-medium text-indigo-600 hover:underline">Review all</Link>
            </div>
            <ul v-if="recentApplicants.length" class="card divide-y divide-gray-100">
              <li v-for="a in recentApplicants" :key="a.id">
                <Link :href="`/view-applications?job=${a.job_id}`" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-gray-900">{{ a.name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ a.job }} · {{ ago(a.created_at) }}</p>
                  </div>
                  <div class="flex shrink-0 items-center gap-2">
                    <span v-if="a.score !== null && a.score !== undefined" class="badge" :class="scoreClass(a.score)">{{ a.score }}%</span>
                    <StageBadge :status="a.status" />
                  </div>
                </Link>
              </li>
            </ul>
            <EmptyState v-else :icon="InboxIcon" title="No applicants yet" text="Applications to your roles will appear here." />
          </section>

          <section class="lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="card-title">Your roles</h2>
              <Link href="/jobs/create" class="btn-primary btn-sm"><Plus class="h-4 w-4" /> Post a job</Link>
            </div>
            <ul v-if="roles.length" class="card divide-y divide-gray-100">
              <li v-for="r in roles" :key="r.id">
                <Link :href="`/view-applications?job=${r.id}`" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-gray-900">{{ r.title }}</p>
                    <p class="text-xs text-gray-500">{{ r.applicants }} applicant{{ r.applicants === 1 ? '' : 's' }}</p>
                  </div>
                  <span v-if="r.new" class="badge shrink-0 bg-indigo-100 text-indigo-700">{{ r.new }} new</span>
                  <ArrowRight v-else class="h-4 w-4 shrink-0 text-gray-300" />
                </Link>
              </li>
            </ul>
            <EmptyState v-else :icon="Briefcase" title="No roles yet" text="Post your first job. You can draft it with AI.">
              <Link href="/jobs/create" class="btn-primary">Post a job</Link>
            </EmptyState>
          </section>
        </div>
      </template>

      <template v-else>
        <EmptyState :icon="Briefcase" title="Welcome to NextGig" text="Browse the latest jobs.">
          <Link href="/jobs" class="btn-primary">Browse jobs</Link>
        </EmptyState>
      </template>
    </div>
  </AppLayout>
</template>
