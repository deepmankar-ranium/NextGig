<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Header from './Header.vue';
import Footer from './Footer.vue';
import BottomNav from './BottomNav.vue';

// fullHeight: the page fills the viewport below the header (Inbox, Assistant) and has no footer.
const props = defineProps({ fullHeight: { type: Boolean, default: false } });

const page = usePage();
const loggedIn = computed(() => !!page.props.auth?.user);
</script>

<template>
  <div class="flex min-h-screen flex-col bg-gray-50">
    <Header />

    <main class="flex-grow" :class="{ 'pb-20 md:pb-0': loggedIn && !fullHeight }">
      <slot></slot>
    </main>

    <Footer v-if="!fullHeight" class="hidden md:block" />
    <BottomNav v-if="loggedIn" />
  </div>
</template>
