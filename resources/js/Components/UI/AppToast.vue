<script setup>
import { router } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-vue-next';
import Avatar from '@/Components/Chat/Avatar.vue';

/**
 * One toast design for the whole app. `avatar` turns it into a message toast; `href` makes the
 * whole toast a link (e.g. open the conversation).
 */
const props = defineProps({
  variant: { type: String, default: 'info' }, // success | error | info | message
  title: { type: String, default: '' },
  body: { type: String, default: '' },
  href: { type: String, default: null },
  avatar: { type: Object, default: null }, // { id, name, src }
});
const emit = defineEmits(['close-toast']);

const ICONS = { success: CheckCircle2, error: AlertCircle, info: Info };
const ICON_CLASS = { success: 'text-green-600', error: 'text-red-600', info: 'text-indigo-600' };

const open = () => {
  if (!props.href) return;
  emit('close-toast');
  router.visit(props.href);
};
</script>

<template>
  <div class="flex w-full items-start gap-3 rounded-xl border bg-white p-3.5 text-left shadow-lg"
    :class="[variant === 'error' ? 'border-red-200' : 'border-gray-200', href ? 'cursor-pointer hover:bg-gray-50' : '']"
    :role="variant === 'error' ? 'alert' : 'status'" @click="open">
    <Avatar v-if="avatar" :id="avatar.id" :name="avatar.name" :src="avatar.src" size="sm" />
    <component :is="ICONS[variant] ?? Info" v-else class="mt-0.5 h-5 w-5 shrink-0" :class="ICON_CLASS[variant] ?? ICON_CLASS.info" />

    <div class="min-w-0 flex-1">
      <p v-if="title" class="truncate text-sm font-semibold text-gray-900">{{ title }}</p>
      <p v-if="body" class="line-clamp-2 break-words text-sm text-gray-600">{{ body }}</p>
      <p v-if="href && variant === 'message'" class="mt-1 text-xs font-medium text-indigo-600">Reply</p>
    </div>

    <button type="button" class="-m-1 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Dismiss" @click.stop="emit('close-toast')">
      <X class="h-4 w-4" />
    </button>
  </div>
</template>
