import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import '../css/app.css';
import { useNotificationStore } from './store/notifications';

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
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast)
            .use(pinia);

        if (props.initialPage.props.auth?.user?.id) {
            const notificationStore = useNotificationStore(pinia);
            notificationStore.initializeWebSocket(
                props.initialPage.props.auth.user.id,
            );
            notificationStore.fetchUnreadCount();
        }

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
