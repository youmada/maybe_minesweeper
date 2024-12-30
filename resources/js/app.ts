import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import DefaultLayout from './Layouts/DefaultLayout.vue';
import { useSaveDataStore } from './stores/singlePlayData';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        );
        page.then((module) => {
            // 下記コードでLayout/DefaultLayout.vueを設定
            module.default.layout = module.default.layout || DefaultLayout;
        });

        return page;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(ZiggyVue);
        app.use(pinia);
        // Inertia ナビゲーションフック
        router.on('navigate', () => {
            const saveDataStore = useSaveDataStore();
            saveDataStore.loadSaveData();
        });
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
