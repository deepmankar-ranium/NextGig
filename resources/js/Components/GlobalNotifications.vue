<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotificationStore } from '@/store/notifications';
import { notify } from '@/utils/toast';

/**
 * Rendered once next to every page (app.js):
 *  - shows `flash.success` / `flash.error` from the server as toasts;
 *  - keeps the real-time connection pointed at whoever is signed in, so toasts and badges work
 *    straight after login (no full reload) and stop after logout.
 */
const page = usePage();
const store = useNotificationStore();

const flash = computed(() => page.props.flash ?? {});
let lastFlashId = null;
watch(() => flash.value.id, (id) => {
  if (!id || id === lastFlashId) return;
  lastFlashId = id;
  if (flash.value.success) notify({ variant: 'success', body: flash.value.success, id: `flash-${id}` });
  if (flash.value.error) notify({ variant: 'error', body: flash.value.error, id: `flash-${id}-e` });
}, { immediate: true });

watch(() => page.props.auth?.user?.id ?? null, (id) => {
  if (id) {
    store.connect(id);
    store.fetchUnreadCount();
  } else {
    store.disconnect();
  }
}, { immediate: true });
</script>

<template>
  <span hidden></span>
</template>
