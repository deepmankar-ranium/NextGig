<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-8">
      <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Job Applications</h1>
        <p class="text-gray-600">Manage and review candidate applications</p>
      </header>

      <!-- Filters section remains the same -->
      <div class="bg-white p-6 rounded-xl shadow-sm mb-6">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-[300px]">
            <div class="relative">
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search applications..." 
                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              />
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <Search class="w-5 h-5" />
              </span>
            </div>
          </div>
          
          <div class="w-[200px]">
            <VueMultiselect
              v-model="filterStatus"
              :options="statusOptions"
              :searchable="false"
              placeholder="Status"
              class="multiselect-custom"
              label="text"
              track-by="value"
            />
          </div>
        </div>
      </div>

      <!-- Applications Table -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-white border-b border-gray-100">
                <th v-for="header in tableHeaders" :key="header" class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="application in filteredApplications" 
                :key="application.id" 
                class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors"
              >
                <td class="px-6 py-4 font-medium text-gray-900">#{{ application.id }}</td>
                <td class="px-6 py-4 font-medium text-gray-900">{{ application.job_listing?.title || 'N/A' }}</td>
                <td class="px-6 py-4 font-medium text-gray-900">{{ application.job_seeker?.name || 'N/A' }}</td>
                <td class="px-6 py-4">
                  <button 
                    @click="openPopup('resume', application.resume_text)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                  >
                    <FileText class="w-4 h-4 mr-2" />
                    View Resume
                  </button>
                </td>
                <td class="px-6 py-4">
                  <button 
                    @click="openPopup('coverLetter', application.cover_letter)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                  >
                    <FileText class="w-4 h-4 mr-2" />
                    View Letter
                  </button>
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                    :class="statusClasses(application.application_status)"
                  >
                    <span class="w-2 h-2 rounded-full mr-2" :class="statusDotClasses(application.application_status)" />
                    {{ application.application_status }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2" v-if="application.application_status === 'pending'">
                    <button 
                     @click="showConfirmPopup(application.id, 'approved')"
                      class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="isProcessing"
                      title="Approve"
                    >
                      <Check class="w-5 h-5" />
                    </button>
                    <button 
                      @click="showConfirmPopup(application.id, 'rejected')"
                      class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="isProcessing"
                      title="Reject"
                    >
                      <X class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Content Modal -->
      <div v-if="isPopupVisible" class="fixed inset-0 z-40 overflow-y-auto" @click="closePopup">
        <div class="flex items-center justify-center min-h-screen">
          <div class="fixed inset-0 bg-black/30 transition-opacity"></div>
          <div 
            class="relative bg-white rounded-lg max-w-2xl w-full mx-4 overflow-hidden shadow-xl transform transition-all"
            @click.stop
          >
            <div class="p-6">
              <div class="max-h-[70vh] overflow-y-auto">
                <pre class="whitespace-pre-wrap font-sans text-sm">{{ popupContent }}</pre>
              </div>
              <div class="mt-6 flex justify-end">
                <button
                  @click="closePopup"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <div v-if="isConfirmPopupVisible" class="fixed inset-0 z-50 overflow-y-auto" @click="closeConfirmPopup">
        <div class="flex items-center justify-center min-h-screen">
          <div class="fixed inset-0 bg-black/30 transition-opacity"></div>
          <div 
            class="relative bg-white rounded-lg max-w-sm w-full mx-4 overflow-hidden shadow-xl transform transition-all"
            @click.stop
          >
            <div class="p-6">
              <h3 class="text-lg font-medium mb-4">Confirm Status Update</h3>
              <p class="text-gray-600 mb-6">
                Are you sure you want to mark this application as {{ confirmStatus }}?
              </p>
              <div class="flex justify-end gap-4">
                <button
                @click="isConfirmPopupVisible = false"

                  class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button
               @click="updateStatus(confirmApplicationId, confirmStatus)"
                class="px-4 py-2 bg-gray-600 text-white hover:bg-gray-700 rounded-lg transition-colors"
                :disabled="isProcessing"
              >
                {{ isProcessing ? 'Processing...' : 'Confirm' }}
              </button>
              
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import VueMultiselect from 'vue-multiselect'
import AppLayout from '@/Layout/AppLayout.vue'
import 'vue-multiselect/dist/vue-multiselect.css'
import { Check, X, FileText, Search } from 'lucide-vue-next'
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  applications: {
    type: Array,
    required: true
  }
})

const tableHeaders = ['ID', 'Job Listing', 'Applicant', 'Resume', 'Cover Letter', 'Status', 'Actions']
const statusOptions = ref([
  { text: "All", value: "" },
  { text: "Pending", value: "pending" },
  { text: "Approved", value: "approved" },
  { text: "Rejected", value: "rejected" }
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
const statusClasses = status => ({
  'bg-yellow-100 text-yellow-800': status === 'pending',
  'bg-green-100 text-green-800': status === 'approved',
  'bg-red-100 text-red-800': status === 'rejected'
})

const statusDotClasses = status => ({
  'bg-yellow-400': status === 'pending',
  'bg-green-400': status === 'approved',
  'bg-red-400': status === 'rejected'
})

// Content popup handlers
const openPopup = (type, content) => {
  popupContent.value = content
  isPopupVisible.value = true
}

const closePopup = () => {
  isPopupVisible.value = false
  popupContent.value = ''
}
const isConfirmPopupVisible = ref(false);
const confirmApplicationId = ref(null);
const confirmStatus = ref('');

const showConfirmPopup = (applicationId, status) => {
  confirmApplicationId.value = applicationId;
  confirmStatus.value = status;
  isConfirmPopupVisible.value = true;
};

const updateStatus = (applicationId, status) => {
  router.put(`/applications/${applicationId}`, { application_status: status }, {
    preserveScroll: true,
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