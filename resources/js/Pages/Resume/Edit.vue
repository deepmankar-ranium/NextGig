<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import AccountTabs from '@/Components/UI/AccountTabs.vue';
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { FileText, Upload, Sparkles, X } from 'lucide-vue-next';

const props = defineProps({
  hasResume: Boolean,
  profile: { type: Object, default: null },
  profileUpdatedAt: { type: String, default: null },
});

const page = usePage();
const status = computed(() => page.props.flash?.status);

const upload = useForm({ resume: null });
const fileName = ref('');

const onFile = (e) => {
  upload.resume = e.target.files[0] ?? null;
  fileName.value = upload.resume?.name ?? '';
};

const submitUpload = () => {
  upload.post('/resume', { forceFormData: true, preserveScroll: true });
};

const form = useForm({
  skills: [...(props.profile?.skills ?? [])],
  years_experience: props.profile?.years_experience ?? 0,
  roles: [...(props.profile?.roles ?? [])],
  summary: props.profile?.summary ?? '',
});

const newSkill = ref('');
const addSkill = () => {
  const s = newSkill.value.trim();
  if (s && !form.skills.includes(s.toLowerCase())) form.skills.push(s);
  newSkill.value = '';
};
const removeSkill = (i) => form.skills.splice(i, 1);

const rolesText = computed({
  get: () => form.roles.join('\n'),
  set: (v) => { form.roles = v.split('\n').map((r) => r.trim()).filter(Boolean); },
});

const saveProfile = () => form.put('/resume/profile', { preserveScroll: true });
</script>

<template>
  <AppLayout>
    <div>
      <div class="page-narrow space-y-6">
        <PageHeader title="Account" subtitle="Upload a PDF and we'll pull out your skills and experience. You can edit everything before it's used for matching.">
          <template #tabs><AccountTabs /></template>
        </PageHeader>

        <div v-if="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ status }}</div>

        <!-- Upload -->
        <section class="card card-body">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <FileText class="h-5 w-5 text-indigo-600" /> {{ hasResume ? 'Replace resume' : 'Upload resume' }}
          </h2>
          <form @submit.prevent="submitUpload" class="mt-4 flex flex-col sm:flex-row gap-3 sm:items-center">
            <label class="flex-1 cursor-pointer rounded-md border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-600 hover:border-indigo-400">
              <input type="file" accept="application/pdf" class="sr-only" @change="onFile" />
              {{ fileName || 'Choose a PDF (max 5 MB)' }}
            </label>
            <button type="submit" :disabled="!upload.resume || upload.processing"
              class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
              <Upload class="h-4 w-4" />
              {{ upload.processing ? 'Reading your resume…' : 'Upload & parse' }}
            </button>
          </form>
          <p v-if="upload.errors.resume" class="mt-2 text-sm text-red-600">{{ upload.errors.resume }}</p>
          <p v-if="page.props.errors?.ai" class="mt-2 text-sm text-red-600">{{ page.props.errors.ai }}</p>
          <p class="mt-3 text-xs text-gray-500">
            Your contact details are removed before the text is sent to the AI. Only you can see your resume and parsed profile.
          </p>
        </section>

        <!-- Parsed profile -->
        <section v-if="profile" class="card card-body">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <Sparkles class="h-5 w-5 text-indigo-600" /> Parsed profile
          </h2>
          <p class="text-xs text-gray-500 mt-1">AI suggestion: check it and fix anything that's wrong.</p>

          <form @submit.prevent="saveProfile" class="mt-6 space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Skills</label>
              <div class="mt-2 flex flex-wrap gap-2">
                <span v-for="(skill, i) in form.skills" :key="skill + i"
                  class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-sm text-indigo-700">
                  {{ skill }}
                  <button type="button" @click="removeSkill(i)" :aria-label="`Remove ${skill}`"><X class="h-3 w-3" /></button>
                </span>
              </div>
              <div class="mt-2 flex gap-2">
                <input v-model="newSkill" @keydown.enter.prevent="addSkill" placeholder="Add a skill"
                  class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm" />
                <button type="button" @click="addSkill" class="rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Add</button>
              </div>
              <p v-if="form.errors.skills" class="mt-1 text-sm text-red-600">{{ form.errors.skills }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Years of experience</label>
              <input v-model.number="form.years_experience" type="number" min="0" max="60" step="0.5"
                class="mt-2 w-32 rounded-md border border-gray-300 px-3 py-2 text-sm" />
              <p v-if="form.errors.years_experience" class="mt-1 text-sm text-red-600">{{ form.errors.years_experience }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Roles (one per line, most recent first)</label>
              <textarea v-model="rolesText" rows="3" class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Summary</label>
              <textarea v-model="form.summary" rows="3" class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>

            <button type="submit" :disabled="form.processing"
              class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
              Save profile
            </button>
          </form>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
