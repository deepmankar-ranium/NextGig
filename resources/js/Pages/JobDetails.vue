<script setup>
import { computed } from 'vue';
import { CalendarIcon, CurrencyDollarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  job: {
    type: Object,
    required: true
  }
});
const isOwner = ref(usePage().props.isOwner);

const formattedSalary = computed(() => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(props.job.salary);
});
</script>

<template>
  <AppLayout>
    <div class="w-full flex justify-between items-center mt-8 px-6">
      <Link href="/Jobs"
        class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all
               bg-white text-gray-700 hover:bg-gray-100 border-gray-300">
        ← Go Back
    </Link>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-10">
      <!-- Job Details Card -->
      <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-5">
          <h1 class="text-2xl font-bold text-gray-900">{{ job.title }}</h1>
          <p class="text-sm text-gray-600 mt-1">{{ job.company }}</p>
        </div>

        <div class="border-t border-gray-200 px-6 py-5">
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-700">Description</dt>
              <dd class="mt-1 text-sm text-gray-600">{{ job.description }}</dd>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <CurrencyDollarIcon class="h-5 w-5 text-gray-400" />
              <span>{{ formattedSalary }} per year</span>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <MapPinIcon class="h-5 w-5 text-gray-400" />
              <span>Remote</span>
            </div>
          </dl>
        </div>
      </div>

      <div v-if="isOwner"  class="mt-6 flex justify-end">
        <Link 
          :href="`/Jobs/job/${job.id}/edit`"
          class="px-6 py-3 text-sm font-medium border rounded-lg shadow-md transition-all
                 bg-black text-white hover:bg-gray-900">
          Edit Job
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
