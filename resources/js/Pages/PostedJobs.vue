<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layout/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { router } from '@inertiajs/vue3';
import { Plus, Tag, Users, Pencil, Eye, FolderKanban } from 'lucide-vue-next';

defineProps({
  jobListings: Object,
  isEmployer: Boolean,
});

const formatDate = (iso) => new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
const goToPage = (url) => url && router.get(url, {}, { preserveScroll: true });
</script>

<template>
  <AppLayout>
    <div class="page">
      <PageHeader title="My postings" subtitle="Your open roles and how many people applied.">
        <template #actions>
          <Link href="/tags" class="btn-secondary"><Tag class="h-4 w-4" /> Tags</Link>
          <Link href="/jobs/create" class="btn-primary"><Plus class="h-4 w-4" /> Post a job</Link>
        </template>
      </PageHeader>

      <div v-if="jobListings.data?.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="job in jobListings.data" :key="job.id" class="card flex flex-col">
          <div class="card-body flex-1">
            <div class="flex items-start justify-between gap-2">
              <Link :href="`/jobs/${job.id}`" class="font-semibold text-gray-900 hover:text-indigo-600">{{ job.title }}</Link>
              <span v-if="job.new_count" class="badge shrink-0 bg-indigo-100 text-indigo-700">{{ job.new_count }} new</span>
            </div>
            <p class="mt-1 text-xs text-gray-500">Posted {{ formatDate(job.created_at) }}</p>
            <div v-if="job.tags?.length" class="mt-3 flex flex-wrap gap-1">
              <span v-for="t in job.tags.slice(0, 5)" :key="t.id" class="badge bg-gray-100 text-gray-700">{{ t.name }}</span>
            </div>
            <p class="mt-4 flex items-center gap-2 text-sm text-gray-700">
              <Users class="h-4 w-4 text-gray-400" />
              {{ job.applications_count }} applicant{{ job.applications_count === 1 ? '' : 's' }}
            </p>
          </div>
          <div class="flex divide-x divide-gray-100 border-t border-gray-100 text-sm">
            <Link :href="`/view-applications?job=${job.id}`" class="flex flex-1 items-center justify-center gap-1 py-2.5 font-medium text-indigo-600 hover:bg-indigo-50">
              <Users class="h-4 w-4" /> Applicants
            </Link>
            <Link :href="`/jobs/${job.id}/edit`" class="flex flex-1 items-center justify-center gap-1 py-2.5 text-gray-600 hover:bg-gray-50">
              <Pencil class="h-4 w-4" /> Edit
            </Link>
            <Link :href="`/jobs/${job.id}`" class="flex flex-1 items-center justify-center gap-1 py-2.5 text-gray-600 hover:bg-gray-50">
              <Eye class="h-4 w-4" /> View
            </Link>
          </div>
        </div>
      </div>

      <EmptyState v-else :icon="FolderKanban" title="No postings yet" text="Post your first role. You can draft the description with AI.">
        <Link href="/jobs/create" class="btn-primary"><Plus class="h-4 w-4" /> Post a job</Link>
      </EmptyState>

      <div v-if="jobListings.last_page > 1" class="mt-8 flex justify-center gap-2">
        <button type="button" class="btn-secondary btn-sm" :disabled="!jobListings.prev_page_url" @click="goToPage(jobListings.prev_page_url)">Previous</button>
        <span class="self-center text-sm text-gray-500">Page {{ jobListings.current_page }} of {{ jobListings.last_page }}</span>
        <button type="button" class="btn-secondary btn-sm" :disabled="!jobListings.next_page_url" @click="goToPage(jobListings.next_page_url)">Next</button>
      </div>
    </div>
  </AppLayout>
</template>
