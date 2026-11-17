<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Camera, Trash2, Mail, Briefcase, CheckCircle2 } from 'lucide-vue-next';
import AppLayout from '@/Layout/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import AccountTabs from '@/Components/UI/AccountTabs.vue';
import Avatar from '@/Components/Chat/Avatar.vue';

const props = defineProps({
  user: Object,
});

const form = useForm({
  full_name: props.user.full_name,
  photo: null,
  remove_photo: false,
});

const fileInput = ref(null);
const preview = ref(null);
const saved = ref(false);

const shownPhoto = computed(() => {
  if (preview.value) return preview.value;
  if (form.remove_photo) return null;
  return props.user.photo_url;
});
const hasPhoto = computed(() => !!shownPhoto.value);

const revokePreview = () => { if (preview.value) URL.revokeObjectURL(preview.value); preview.value = null; };
onBeforeUnmount(revokePreview);

const pickPhoto = (e) => {
  const file = e.target.files?.[0];
  e.target.value = '';
  if (!file) return;
  form.clearErrors('photo');
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    form.setError('photo', 'Choose a JPG, PNG or WebP image.');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    form.setError('photo', 'Your photo must be 5 MB or smaller.');
    return;
  }
  revokePreview();
  preview.value = URL.createObjectURL(file);
  form.photo = file;
  form.remove_photo = false;
};

const removePhoto = () => {
  revokePreview();
  form.photo = null;
  form.remove_photo = !!props.user.photo_url;
};

const dirty = computed(() => form.full_name.trim() !== props.user.full_name || !!form.photo || form.remove_photo);

const submit = () => {
  form.post('/profile', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      revokePreview();
      form.defaults({ full_name: form.full_name.trim(), photo: null, remove_photo: false });
      form.reset();
      saved.value = true;
      setTimeout(() => (saved.value = false), 2500);
    },
  });
};

const roleLabel = computed(() => (({ 2: 'Employer', 3: 'Job seeker' })[props.user.role_id] ?? props.user.role?.name));
</script>

<template>
  <AppLayout>
    <div class="page-narrow">
      <PageHeader title="Account" subtitle="Your name and photo, as other people see them.">
        <template #tabs><AccountTabs /></template>
      </PageHeader>

      <form class="card overflow-hidden" @submit.prevent="submit">
        <!-- Photo -->
        <div class="flex flex-col items-center gap-5 p-6 sm:flex-row sm:p-8">
          <button type="button" class="group relative rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
            aria-label="Change profile photo" @click="fileInput.click()">
            <Avatar :id="user.id" :name="form.full_name || user.full_name" :src="shownPhoto" size="xl" />
            <span class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 opacity-0 transition group-hover:opacity-100">
              <Camera class="h-6 w-6 text-white" />
            </span>
          </button>
          <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="pickPhoto" />

          <div class="text-center sm:text-left">
            <p class="text-lg font-semibold text-gray-900">{{ form.full_name || user.full_name }}</p>
            <p class="text-sm text-gray-500">{{ roleLabel }}</p>
            <div class="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
              <button type="button" class="btn-secondary btn-sm" @click="fileInput.click()">
                <Camera class="h-4 w-4" /> {{ hasPhoto ? 'Change photo' : 'Upload photo' }}
              </button>
              <button v-if="hasPhoto" type="button" class="btn-ghost btn-sm text-red-600 hover:bg-red-50 hover:text-red-700" @click="removePhoto">
                <Trash2 class="h-4 w-4" /> Remove
              </button>
            </div>
            <p class="mt-2 text-xs text-gray-500">JPG, PNG or WebP, up to 5 MB. Cropped to a square.</p>
            <p v-if="form.errors.photo" class="mt-1 text-xs text-red-600">{{ form.errors.photo }}</p>
          </div>
        </div>

        <!-- Details -->
        <div class="space-y-5 border-t border-gray-200 p-6 sm:p-8">
          <div>
            <label for="full_name" class="label">Full name</label>
            <input id="full_name" v-model="form.full_name" type="text" autocomplete="name" required maxlength="255" class="input mt-1" />
            <p v-if="form.errors.full_name" class="mt-1 text-xs text-red-600">{{ form.errors.full_name }}</p>
          </div>

          <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
              <dt class="label flex items-center gap-1.5"><Mail class="h-4 w-4 text-gray-400" /> Email</dt>
              <dd class="mt-1 truncate text-sm text-gray-900">{{ user.email }}</dd>
              <dd class="text-xs text-gray-500">Used to sign in; can't be changed here.</dd>
            </div>
            <div>
              <dt class="label flex items-center gap-1.5"><Briefcase class="h-4 w-4 text-gray-400" /> Account type</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ roleLabel }}</dd>
            </div>
          </dl>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:px-8">
          <span v-if="saved" class="flex items-center gap-1 text-sm text-green-700"><CheckCircle2 class="h-4 w-4" /> Saved</span>
          <button v-if="dirty" type="button" class="btn-ghost" :disabled="form.processing" @click="revokePreview(); form.reset(); form.clearErrors()">Cancel</button>
          <button type="submit" class="btn-primary" :disabled="!dirty || form.processing">
            {{ form.processing ? 'Saving…' : 'Save changes' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
