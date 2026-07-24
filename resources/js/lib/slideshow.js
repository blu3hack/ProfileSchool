import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Rotasi indeks sederhana untuk slideshow (hero beranda).
 *
 * Sengaja tidak menyentuh DOM: komponen yang memakainya bebas menentukan
 * cara transisinya (di hero: crossfade lewat <Transition>/opacity).
 *
 * - berhenti saat tab tidak terlihat, supaya tidak ada slide yang "terlewat";
 * - `pause()`/`resume()` dipakai saat kursor berada di atas hero;
 * - `prefers-reduced-motion` → tidak berputar otomatis, pengunjung tetap bisa
 *   berpindah lewat tombol indikator.
 *
 * @param {import('vue').Ref<number>|(() => number)} length jumlah slide
 * @param {{ interval?: number }} options
 */
export function useSlideshow(length, options = {}) {
    const interval = options.interval ?? 6000;

    const count = computed(() => {
        const value = typeof length === 'function' ? length() : length.value;

        return Number.isFinite(value) ? Math.max(0, value) : 0;
    });

    const active = ref(0);
    const paused = ref(false);

    let timer = null;
    let reduced = false;

    const stop = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        stop();

        if (reduced || paused.value || count.value < 2) {
            return;
        }

        timer = window.setInterval(() => {
            active.value = (active.value + 1) % count.value;
        }, interval);
    };

    /** Loncat ke slide tertentu; timer di-restart agar durasi tampilnya penuh. */
    const goTo = (index) => {
        if (count.value === 0) {
            return;
        }

        active.value = ((index % count.value) + count.value) % count.value;
        start();
    };

    const next = () => goTo(active.value + 1);
    const previous = () => goTo(active.value - 1);

    const pause = () => {
        paused.value = true;
        stop();
    };

    const resume = () => {
        paused.value = false;
        start();
    };

    const onVisibilityChange = () => (document.hidden ? stop() : start());

    onMounted(() => {
        reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        document.addEventListener('visibilitychange', onVisibilityChange);
        start();
    });

    onBeforeUnmount(() => {
        document.removeEventListener('visibilitychange', onVisibilityChange);
        stop();
    });

    // Slide bisa berkurang (mis. data di-refresh Inertia) — jaga indeks tetap valid.
    watch(count, (value) => {
        if (active.value >= value) {
            active.value = 0;
        }

        start();
    });

    return { active, paused, goTo, next, previous, pause, resume };
}
