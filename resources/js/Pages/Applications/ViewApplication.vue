<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
      <!-- Header -->
      <div class="max-w-7xl mx-auto">
        <div class="md:flex md:items-center md:justify-between pb-8">
          <div class="min-w-0 flex-1">
            <button @click="handleBack" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
              <ChevronLeftIcon class="h-5 w-5 mr-1" />
              Back
            </button>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
              Job Applications
            </h1>
            <p class="mt-2 text-lg text-gray-600">
              Manage and review candidate applications for your job postings.
            </p>
          </div>
        </div>

        <!-- Filters section -->
        <div class="bg-white p-6 rounded-xl shadow-md mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <label for="search-applications" class="block text-sm font-medium text-gray-700">Search by applicant or job</label>
              <div class="mt-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Search class="w-5 h-5 text-gray-400" />
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  id="search-applications"
                  placeholder="e.g., 'Jane Doe' or 'Software Engineer'"
                  class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
            </div>

            <div>
              <label for="status-filter" class="block text-sm font-medium text-gray-700">Filter by status</label>
              <VueMultiselect
                id="status-filter"
                v-model="filterStatus"
                :options="statusOptions"
                :searchable="false"
                placeholder="Select a status"
                class="multiselect-custom mt-1"
                label="text"
                track-by="value"
              />
            </div>
          </div>
        </div>

        <!-- Ranking toggle -->
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg bg-white px-4 py-3 shadow-sm">
          <p class="text-sm text-gray-600">
            <span class="font-medium text-gray-800">{{ ranked ? 'Sorted by match score' : 'Plain list (newest first)' }}.</span>
            Match scores are an AI-assisted suggestion; no applicant is rejected automatically.
          </p>
          <button type="button" @click="toggleRanking" class="shrink-0 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ ranked ? 'Turn ranking off' : 'Turn ranking on' }}
          </button>
        </div>

        <!-- Applications Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                  <th v-for="header in tableHeaders" :key="header" scope="col" class="px-6 py-3">
                    {{ header }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <template v-for="application in filteredApplications" :key="application.id">
                <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 font-medium text-gray-900">#{{ application.id }}</td>
                  <td class="px-6 py-4 font-medium text-gray-900">{{ application.job_listing?.title || 'N/A' }}</td>
                  <td class="px-6 py-4 font-medium text-gray-900">
                    <div class="flex items-center gap-2">
                      <span>{{ application.job_seeker?.name || 'N/A' }}</span>
                      <Link
                        v-if="application.job_seeker?.user_id"
                        :href="`/messages/${application.job_seeker.user_id}`"
                        class="p-1 text-indigo-600 hover:bg-indigo-50 rounded-md"
                        title="Message applicant"
                      >
                        <MessageSquare class="w-4 h-4" />
                      </Link>
                    </div>
                  </td>
                  <td v-if="ranked" class="px-6 py-4">
                    <button v-if="application.match" @click="toggleWhy(application.id)" class="text-left">
                      <span class="font-semibold" :class="application.match.score === null ? 'text-gray-400' : application.match.score >= 75 ? 'text-green-600' : application.match.score >= 50 ? 'text-amber-600' : 'text-gray-700'">
                        {{ application.match.score === null ? '—' : application.match.score + '%' }}
                      </span>
                      <span class="block text-xs text-indigo-600 underline">why?</span>
                    </button>
                  </td>
                  <td class="px-6 py-4">
                    <button
                      @click="openPopup('resume', application.resume_text, application)"
                      class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    >
                      <FileText class="w-4 h-4 mr-2" />
                      Resume
                    </button>
                  </td>
                  <td class="px-6 py-4">
                    <button
                      @click="openPopup('coverLetter', application.cover_letter, application)"
                      class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    >
                      <FileText class="w-4 h-4 mr-2" />
                      Letter
                    </button>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="statusClasses(application.application_status)"
                    >
                      <span class="w-2 h-2 rounded-full mr-1.5" :class="statusDotClasses(application.application_status)" />
                      {{ STAGE_LABELS[application.application_status] ?? application.application_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap items-center gap-1" v-if="nextStages(application.application_status).length">
                      <button
                        v-for="stage in nextStages(application.application_status)"
                        :key="stage"
                        @click="showConfirmPopup(application.id, stage)"
                        :disabled="isProcessing"
                        class="px-2 py-1 text-xs font-medium rounded-md border transition-colors disabled:opacity-50"
                        :class="stage === 'rejected' ? 'border-red-200 text-red-700 hover:bg-red-50' : 'border-indigo-200 text-indigo-700 hover:bg-indigo-50'"
                      >
                        {{ stage === 'rejected' ? 'Reject' : STAGE_LABELS[stage] }}
                      </button>
                    </div>
                    <button v-else @click="showConfirmPopup(application.id, 'shortlisted', true)"
                      class="px-2 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-600 hover:bg-gray-50">
                      Reopen…
                    </button>
                  </td>
                </tr>
                <tr v-if="ranked && expanded === application.id && application.match" class="bg-indigo-50/40 border-b">
                  <td :colspan="tableHeaders.length" class="px-6 py-4">
                    <div class="grid gap-4 md:grid-cols-3 text-sm">
                      <div>
                        <p class="font-medium text-gray-700 mb-1">Matches</p>
                        <p v-if="!application.match.matched.length" class="text-gray-500">None</p>
                        <span v-for="s in application.match.matched" :key="s" class="inline-block mr-1 mb-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">{{ s }}</span>
                      </div>
                      <div>
                        <p class="font-medium text-gray-700 mb-1">Missing</p>
                        <p v-if="!application.match.missing.length" class="text-gray-500">None</p>
                        <span v-for="s in application.match.missing" :key="s" class="inline-block mr-1 mb-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs">{{ s }}</span>
                      </div>
                      <div>
                        <p class="font-medium text-gray-700 mb-1">Status history</p>
                        <ul class="space-y-1 text-xs text-gray-600">
                          <li v-for="(h, i) in application.history" :key="i">
                            {{ h.to }} · {{ h.by }} · {{ new Date(h.at).toLocaleString() }}
                          </li>
                        </ul>
                      </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Score uses only the applicant's skills and years of experience against this job's tags. Name, email, photo, gender, age and location are never used. You make every decision.</p>
                  </td>
                </tr>
                </template>
                <tr v-if="filteredApplications.length === 0">
                    <td :colspan="tableHeaders.length" class="text-center py-10 text-gray-500">
                        No applications found for the selected criteria.
                    </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Content Modal -->
      <div v-if="isPopupVisible" class="fixed inset-0 z-40 flex items-center justify-center p-4" @click="closePopup">
        <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
        <div
          class="relative bg-white rounded-lg max-w-2xl w-full mx-auto overflow-hidden shadow-xl transform transition-all"
          @click.stop
        >
          <div class="p-6">
            <div class="max-h-[70vh] overflow-y-auto pr-4">
              <pre class="whitespace-pre-wrap font-sans text-sm text-gray-700">{{ popupContent }}</pre>
            </div>
            <div class="mt-6 flex justify-end">
              <button
                @click="closePopup"
                class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <div v-if="isConfirmPopupVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click="closeConfirmPopup">
        <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
        <div
          class="relative bg-white rounded-lg max-w-sm w-full mx-auto overflow-hidden shadow-xl transform transition-all"
          @click.stop
        >
          <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full" :class="confirmStatus !== 'rejected' ? 'bg-green-100' : 'bg-red-100'">
                <Check v-if="confirmStatus !== 'rejected'" class="h-6 w-6 text-green-600" />
                <X v-else class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="text-lg font-medium mt-5">Confirm Status Update</h3>
            <p class="text-gray-500 text-sm mt-2 mb-6">
              <template v-if="confirmReopen">Reopen this application and move it back to <span class="font-semibold">{{ STAGE_LABELS[confirmStatus] }}</span>? This is recorded in the history.</template>
              <template v-else>Move this application to <span class="font-semibold">{{ STAGE_LABELS[confirmStatus] }}</span>? The applicant is notified{{ ['shortlisted','interview','offer'].includes(confirmStatus) ? ' by email and' : '' }} in real time.</template>
            </p>
            <div class="flex justify-center gap-4">
              <button
                @click="closeConfirmPopup"
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors text-sm font-medium"
              >
                Cancel
              </button>
              <button
                @click="updateStatus(confirmApplicationId, confirmStatus)"
                class="px-4 py-2 text-white rounded-lg transition-colors text-sm font-medium"
:class="confirmStatus !== 'rejected' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                :disabled="isProcessing"
              >
                {{ isProcessing ? 'Processing...' : 'Confirm' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
.multiselect-custom .multiselect__tags {
  border-color: #d1d5db; /* gray-300 */
  border-radius: 0.375rem; /* rounded-md */
}
.multiselect-custom .multiselect__input, .multiselect-custom .multiselect__single {
    background-color: transparent;
}
.multiselect-custom .multiselect__tag {
  background-color: #4f46e5; /* indigo-600 */
}
.multiselect-custom .multiselect__tag-icon:focus, .multiselect-custom .multiselect__tag-icon:hover {
  background-color: #4338ca; /* indigo-700 */
}
.multiselect-custom .multiselect__option--highlight {
  background-color: #6366f1; /* indigo-500 */
}
.multiselect-custom .multiselect__option--selected {
  background-color: #eef2ff; /* indigo-50 */
  color: #3730a3; /* indigo-800 */
}
.multiselect-custom.multiselect--active .multiselect__tags {
    border-color: #6366f1; /* indigo-500 */
    box-shadow: 0 0 0 1px #6366f1; /* focus:ring-indigo-500 */
}
</style>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import VueMultiselect from 'vue-multiselect'
import AppLayout from '@/Layout/AppLayout.vue'
import 'vue-multiselect/dist/vue-multiselect.css'
import { Check, X, FileText, Search, ChevronLeftIcon, MessageSquare } from 'lucide-vue-next'
import { onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { STAGE_LABELS, stageBadgeClass, nextStages } from '@/utils/stages'

const props = defineProps({
  applications: {
    type: Array,
    required: true
  },
  ranked: {
    type: Boolean,
    default: true
  }
})

const expanded = ref(null)
const toggleWhy = (id) => { expanded.value = expanded.value === id ? null : id }
const toggleRanking = () => {
  router.get('/view-applications', props.ranked ? { ranked: 0 } : {}, { preserveScroll: true })
}

const handleBack = () => {
  window.history.back();
};

const tableHeaders = computed(() => props.ranked
  ? ['ID', 'Job Listing', 'Applicant', 'Match', 'Resume', 'Cover Letter', 'Status', 'Actions']
  : ['ID', 'Job Listing', 'Applicant', 'Resume', 'Cover Letter', 'Status', 'Actions'])
const statusOptions = ref([
  { text: "All", value: "" },
  ...['applied', 'viewed', 'shortlisted', 'interview', 'offer', 'rejected'].map(v => ({ text: STAGE_LABELS[v], value: v }))
])

// Search and filter states
const searchQuery = ref('')
const filterStatus = ref({ text: "All", value: "" })

// Popup states
const isPopupVisible = ref(false)
const popupContent = ref('')

// Confirmation states


const filteredApplications = computed(() => {
  return props.applications.filter(app => {
    const matchesSearch = !searchQuery.value ||
      app.job_listing?.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      app.job_seeker?.name.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = !filterStatus.value.value ||
      app.application_status === filterStatus.value.value

    return matchesSearch && matchesStatus
  })
})

// Status styling helpers
const statusClasses = status => stageBadgeClass(status)

const statusDotClasses = status => ({
  'bg-gray-400': status === 'applied',
  'bg-blue-400': status === 'viewed',
  'bg-indigo-400': status === 'shortlisted',
  'bg-amber-400': status === 'interview',
  'bg-green-400': status === 'offer',
  'bg-red-400': status === 'rejected'
})

// Content popup handlers
const openPopup = (type, content, application) => {
  popupContent.value = content
  isPopupVisible.value = true
  // First time the employer opens an application, record "viewed" (notifies the applicant live).
  if (application?.application_status === 'applied') {
    axios.post(`/applications/${application.id}/viewed`)
      .then(() => { application.application_status = 'viewed' })
      .catch(() => {})
  }
}

const closePopup = () => {
  isPopupVisible.value = false
  popupContent.value = ''
}

const isConfirmPopupVisible = ref(false);
const confirmApplicationId = ref(null);
const confirmStatus = ref('');
const isProcessing = ref(false);

const confirmReopen = ref(false);

const showConfirmPopup = (applicationId, status, reopen = false) => {
  confirmApplicationId.value = applicationId;
  confirmStatus.value = status;
  confirmReopen.value = reopen;
  isConfirmPopupVisible.value = true;
};

const closeConfirmPopup = () => {
  isConfirmPopupVisible.value = false;
};

const updateStatus = (applicationId, status) => {
  router.put(`/applications/${applicationId}`, { application_status: status, reopen: confirmReopen.value }, {
    preserveScroll: true,
    onStart: () => isProcessing.value = true,
    onFinish: () => isProcessing.value = false,
    onSuccess: () => {
      isConfirmPopupVisible.value = false;
    }
  });
};

// Keyboard handlers
const handleEscKey = (event) => {
  if (event.key === 'Escape') {
    if (isPopupVisible.value) closePopup()
    if (isConfirmPopupVisible.value) closeConfirmPopup()
  }
}


onMounted(() => {
  document.addEventListener('keydown', handleEscKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscKey)
})
</script>

<style>
.multiselect-custom {
  width: 100%;
}

/* Prevent body scroll when modal is open */
body.modal-open {
  overflow: hidden;
}
</style>
