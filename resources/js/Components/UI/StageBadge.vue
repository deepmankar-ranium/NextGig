<script setup>
import { STAGE_LABELS, stageBadgeClass } from '@/utils/stages';

defineProps({ status: { type: String, required: true } });
</script>

<template>
  <span class="badge" :class="stageBadgeClass(status)">{{ STAGE_LABELS[status] ?? status }}</span>
</template>
