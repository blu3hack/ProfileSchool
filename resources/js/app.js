import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

import { createSmoothScroll, resetScroll } from './lib/smooth-scroll';
import reveal from './directives/reveal';
import parallax from './directives/parallax';

const appName = import.meta.env.VITE_APP_NAME || 'Alazka Profile';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(reveal)
            .use(parallax)
            .mount(el);
    },
    progress: {
        color: '#6366f1',
    },
});

createSmoothScroll();

// Setiap pindah halaman Inertia: kembali ke atas & hitung ulang posisi ScrollTrigger.
router.on('navigate', () => resetScroll());
