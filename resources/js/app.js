import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import { TOAST_OPTIONS } from './utils/toast';
import '../css/app.css';
import GlobalNotifications from './Components/GlobalNotifications.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // GlobalNotifications sits beside every page: flash toasts + the live connection.
        const app = createApp({ render: () => [h(App, props), h(GlobalNotifications)] })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, TOAST_OPTIONS)
            .use(pinia);

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
