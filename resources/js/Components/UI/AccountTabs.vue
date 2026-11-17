<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Tabs from './Tabs.vue';

const page = usePage();
const roleId = computed(() => page.props.auth.user?.role_id);

const tabs = computed(() => [
  { key: '/profile', label: 'Account', href: '/profile' },
  ...(roleId.value === 3 ? [{ key: '/resume', label: 'Resume & skills', href: '/resume' }] : []),
  ...(roleId.value === 2 ? [{ key: '/tags', label: 'Tags', href: '/tags' }] : []),
]);
const active = computed(() => tabs.value.find((t) => page.url.startsWith(t.href))?.key ?? '/profile');
</script>

<template>
  <Tabs :tabs="tabs" :model-value="active" label="Account sections" />
</template>
