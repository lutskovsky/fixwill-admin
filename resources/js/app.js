import '../css/app.css';
import './bootstrap';

import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {createApp, h} from 'vue';
import {route, ZiggyVue} from '../../vendor/tightenco/ziggy'; // Import the route function

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({el, App, props, plugin}) {
        const vueApp = createApp({render: () => h(App, props)})
            .use(plugin)
            .use(ZiggyVue);

        vueApp.config.globalProperties.$route = route;


        return vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
