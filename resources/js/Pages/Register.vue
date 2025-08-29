<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { User, Briefcase } from 'lucide-vue-next'

const props = defineProps({
  roles: {
    type: Array,
    required: true,
    // Expect items like: { id: 1, name: 'Job Seeker' } / { id: 2, name: 'Employer' }
  },
})

const form = useForm({
  role_id: null, // use null to avoid falsy 0/string quirks
})

/**
 * Normalize any incoming id (string/number) to a number for strict comparisons.
 * This avoids "1" === 1 issues that can keep the button disabled.
 */
const normalizeId = (id) => (id === null || id === undefined || id === '' ? null : Number(id))

const selectRole = (id) => {
  form.role_id = normalizeId(id)
}

const canSubmit = computed(() => form.role_id !== null && !form.processing)

const submit = () => {
  form.post('/register/store-role', {
    preserveScroll: true,
  })
}

/** optional: choose icon by role name */
const iconByName = (name) => (name === 'Employer' ? Briefcase : User)
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="text-center text-3xl font-bold text-indigo-600">NextGig</h2>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Join as a Job Seeker or Employer
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Already have an account?
        <Link href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-white py-8 px-4 shadow-xl rounded-xl sm:px-10">
        <form @submit.prevent="submit" class="space-y-8">
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">
              Choose your account type
            </h3>

            <div v-if="roles && roles.length" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <!-- Render from data to keep things reactive -->
              <div
                v-for="role in roles"
                :key="role.id"
                @click="selectRole(role.id)"
                :class="[
                  normalizeId(form.role_id) === normalizeId(role.id)
                    ? 'border-indigo-600 ring-2 ring-indigo-600'
                    : 'border-gray-300',
                  'relative flex items-center space-x-3 rounded-lg border bg-white px-6 py-5 shadow-sm cursor-pointer transition-all hover:border-gray-400'
                ]"
              >
                <div class="flex-shrink-0">
                  <component :is="iconByName(role.name)" class="h-10 w-10 text-indigo-600" />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ role.name }}</p>
                  <p class="text-sm text-gray-500">
                    {{ role.name === 'Employer'
                      ? 'Hire talented professionals.'
                      : 'Find your next job opportunity.' }}
                  </p>
                </div>
              </div>
            </div>

            <div v-else class="mt-4 text-center text-gray-500">Loading roles...</div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="!canSubmit"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Continue
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
