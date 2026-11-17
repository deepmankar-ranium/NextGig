<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  user: { type: Object, required: true },
  reasons: { type: Object, required: true },
});
const emit = defineEmits(['close']);

const form = useForm({ reason: '', details: '', block: true });

const submit = () => form.post(route('users.report', props.user.id), {
  preserveScroll: true,
  preserveState: true,
  only: ['conversation', 'flash'],
  onSuccess: () => emit('close'),
});

const onKey = (e) => { if (e.key === 'Escape') emit('close'); };
onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click="emit('close')">
    <form class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" :aria-label="`Report ${user.name}`" @click.stop @submit.prevent="submit">
      <h3 class="text-base font-semibold text-gray-900">Report {{ user.name }}</h3>
      <p class="mt-1 text-sm text-gray-600">Our team reviews every report. {{ user.name.split(' ')[0] }} isn't told who reported them.</p>

      <fieldset class="mt-4 space-y-2">
        <legend class="sr-only">Reason</legend>
        <label v-for="(label, key) in reasons" :key="key"
          class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2 text-sm"
          :class="form.reason === key ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:bg-gray-50'">
          <input v-model="form.reason" type="radio" name="reason" :value="key" class="text-indigo-600 focus:ring-indigo-500" />
          {{ label }}
        </label>
      </fieldset>
      <p v-if="form.errors.reason" class="mt-1 text-xs text-red-600">Choose a reason.</p>

      <label class="label mt-4" for="report-details">Anything else? (optional)</label>
      <textarea id="report-details" v-model="form.details" rows="3" maxlength="2000" class="input mt-1" placeholder="e.g. Asked me to pay a fee to get the job"></textarea>

      <label class="mt-3 flex items-center gap-2 text-sm text-gray-700">
        <input v-model="form.block" type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500" />
        Also block {{ user.name.split(' ')[0] }}
      </label>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
        <button type="submit" class="btn-danger" :disabled="!form.reason || form.processing">{{ form.processing ? 'Sending…' : 'Report' }}</button>
      </div>
    </form>
  </div>
</template>
