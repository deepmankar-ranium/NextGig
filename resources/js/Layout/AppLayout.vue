<script setup>
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Header from './Header.vue';
import Footer from './Footer.vue';
import { useNotificationStore } from '../store/notifications';

const page = usePage();
const notificationStore = useNotificationStore();

onMounted(() => {
    // If the user is logged in, initialize the WebSocket listener from the store
    if (page.props.auth?.user?.id) {
        notificationStore.initializeWebSocket(page.props.auth.user.id);
    }
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Header />

    <!-- Main Content -->
    <main class="flex-grow">
      <slot></slot>
    </main>

    <Footer />
  </div>
</template>
