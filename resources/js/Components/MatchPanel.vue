<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Sparkles, Check, X, Plus, MessageCircleQuestion } from 'lucide-vue-next';

const props = defineProps({
  jobId: { type: Number, required: true },
  jobTitle: { type: String, default: '' },
  match: { type: Object, required: true },
});

// Local copy: adding a skill updates the score without reloading the page.
const match = ref({ ...props.match });
watch(() => props.match, (v) => { match.value = { ...v }; explanation.value = v.explanation; });

const explanation = ref(props.match.explanation);
const notice = ref('');
const loading = ref(false);
const error = ref('');
const adding = ref(null);

const addSkill = async (skill) => {
  adding.value = skill;
  error.value = '';
  try {
    const { data } = await axios.post(`/jobs/${props.jobId}/match/skills`, { skill });
    match.value = data.match;
    explanation.value = data.match.explanation;
    notice.value = `Added ${skill} to your skills.`;
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not update your skills right now.';
  } finally {
    adding.value = null;
  }
};

const askHref = computed(() => {
  if (!match.value.missing.length) return `/chat?job=${props.jobId}`;
  const skills = match.value.missing.slice(0, 3).join(', ');
  const q = `I'm missing ${skills} for the ${props.jobTitle || 'job I\'m looking at'}. What's the fastest way to learn enough to apply, and how should I show it?`;
  return `/chat?job=${props.jobId}&q=${encodeURIComponent(q)}`;
});

const scoreClass = computed(() => {
  const s = match.value.score;
  if (s === null) return 'text-gray-400';
  if (s >= 75) return 'text-green-600';
  if (s >= 50) return 'text-amber-600';
  return 'text-red-600';
});

const explain = async () => {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.post(`/jobs/${props.jobId}/match/explain`);
    explanation.value = data.explanation;
    notice.value = data.notice || '';
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not load an explanation right now.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="bg-white p-6 rounded-xl shadow-md space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-bold text-gray-900">Your match</h3>
      <span class="text-3xl font-extrabold" :class="scoreClass">
        {{ match.score === null ? '—' : `${match.score}%` }}
      </span>
    </div>

    <p v-if="!match.hasProfile" class="text-sm text-gray-600">
      <Link href="/resume" class="text-indigo-600 font-medium hover:underline">Upload your resume</Link>
      to see how well you match this job.
    </p>
    <p v-else-if="match.score === null" class="text-sm text-gray-600">
      This job doesn't list any skills yet, so there's nothing to compare.
    </p>

    <div v-if="match.matched.length">
      <h4 class="text-sm font-medium text-gray-500 mb-2">Skills you have</h4>
      <div class="flex flex-wrap gap-2">
        <span v-for="s in match.matched" :key="s" class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">
          <Check class="h-3 w-3" /> {{ s }}
        </span>
      </div>
    </div>
    <div v-if="match.missing.length && match.hasProfile">
      <h4 class="text-sm font-medium text-gray-500 mb-2">Missing</h4>
      <div class="flex flex-wrap gap-2">
        <span v-for="s in match.missing" :key="s" class="inline-flex items-center gap-1 rounded-full bg-red-50 py-1 pl-2 pr-1 text-xs font-medium text-red-700">
          <X class="h-3 w-3" /> {{ s }}
          <button type="button" @click="addSkill(s)" :disabled="!!adding"
            class="ml-0.5 inline-flex items-center gap-0.5 rounded-full bg-white px-1.5 py-0.5 text-[11px] text-gray-700 ring-1 ring-gray-200 hover:bg-green-50 hover:text-green-700 disabled:opacity-50"
            :title="`I have ${s}: add it to my skills`">
            <Plus class="h-3 w-3" /> {{ adding === s ? '…' : 'I have this' }}
          </button>
        </span>
      </div>
    </div>
    <Link v-if="match.hasProfile" :href="askHref" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:underline">
      <MessageCircleQuestion class="h-4 w-4" /> {{ match.missing.length ? 'Ask the assistant how to close the gap' : 'Ask the assistant about this job' }}
    </Link>
    <p v-if="notice" class="text-xs text-gray-500">{{ notice }}</p>

    <div v-if="explanation" class="border-t pt-4 space-y-3">
      <p class="text-sm text-gray-700">{{ explanation.summary }}</p>
      <ul v-if="explanation.tips?.length" class="space-y-2">
        <li v-for="t in explanation.tips" :key="t.skill" class="text-sm text-gray-700">
          <span class="font-medium text-indigo-700">{{ t.skill }}:</span> {{ t.tip }}
        </li>
      </ul>
    </div>
    <button v-else-if="match.score !== null" @click="explain" :disabled="loading"
      class="w-full inline-flex items-center justify-center gap-2 rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 disabled:opacity-50">
      <Sparkles class="h-4 w-4" /> {{ loading ? 'Thinking…' : 'Explain my match' }}
    </button>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

    <p class="text-xs text-gray-400">AI suggestion, not a hiring decision. The score is calculated from skills and experience only.</p>
  </div>
</template>
