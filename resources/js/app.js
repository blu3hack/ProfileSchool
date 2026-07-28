import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { createSmoothScroll, resetScroll } from './lib/smooth-scroll';
import reveal from './directives/reveal';
import parallax from './directives/parallax';

const appName = import.meta.env.VITE_APP_NAME || 'SMPI Alazka Surabaya';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Glob non-eager: tiap halaman jadi berkas terpisah yang baru diunduh saat
    // dibuka. Dengan `eager: true` seluruh halaman — termasuk panel admin dan
    // varian lama yang memakai Three.js — ikut masuk bundel beranda.
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue'),
    ),
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
