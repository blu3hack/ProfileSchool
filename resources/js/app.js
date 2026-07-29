import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { createSmoothScroll, destroySmoothScroll, resetScroll } from './lib/smooth-scroll';
import { syncThemeRoot } from './lib/theme';
import reveal from './directives/reveal';
import parallax from './directives/parallax';

const appName = import.meta.env.VITE_APP_NAME || 'SMPI Alazka Surabaya';

/**
 * Smooth scroll (Lenis) hanya untuk halaman publik — TIDAK di panel admin.
 *
 * Lenis membajak event wheel/touch di seluruh dokumen: ia memanggil
 * `preventDefault()` lalu menggulir <html> sendiri lewat animasi. Efek
 * sampingnya, setiap area ber-scroll di dalam halaman (sidebar admin, isi
 * modal, daftar pustaka media, tabel yang melebar) tidak lagi menerima roda
 * mouse maupun gesture touchpad — satu-satunya cara menggulirnya adalah
 * menyeret batang scrollbar. Landing page memang butuh gulir teranimasi karena
 * animasinya disinkronkan ScrollTrigger; panel admin tidak. Di sana browser
 * dibiarkan mengurus gulirnya sendiri, apa adanya dan native.
 */
const isAdminUrl = (url) => (url ?? '').split('?')[0].startsWith('/admin');

const applyScrollMode = (url) => (isAdminUrl(url) ? destroySmoothScroll() : createSmoothScroll());

/**
 * `data-theme` di <html> hanya berlaku untuk halaman publik. Pada muat pertama
 * skrip di <head> yang memasangnya; fungsi ini menjaganya tetap benar saat
 * pengunjung berpindah publik ⇄ /admin lewat navigasi Inertia (tanpa reload),
 * karena panel admin punya tampilan terangnya sendiri.
 */
const applyThemeScope = (url) => syncThemeRoot(!isAdminUrl(url));

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

applyScrollMode(window.location.pathname);
applyThemeScope(window.location.pathname);

// Setiap pindah halaman Inertia: sesuaikan mode gulir & cakupan tema
// (publik ⇄ admin), kembali ke atas, lalu hitung ulang posisi ScrollTrigger.
router.on('navigate', (event) => {
    const url = event.detail?.page?.url ?? window.location.pathname;

    applyScrollMode(url);
    applyThemeScope(url);
    resetScroll();
});
