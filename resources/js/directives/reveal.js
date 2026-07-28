import { gsap, ScrollTrigger } from '../lib/smooth-scroll';

const presets = {
    up: { y: 48, opacity: 0 },
    down: { y: -48, opacity: 0 },
    left: { x: 48, opacity: 0 },
    right: { x: -48, opacity: 0 },
    fade: { opacity: 0 },
    zoom: { scale: 0.9, opacity: 0 },
};

/**
 * v-reveal — animasikan elemen saat masuk viewport.
 *
 * <div v-reveal />                                  animasi default (naik + fade)
 * <div v-reveal="'left'" />                         pilih preset
 * <div v-reveal="{ from: 'zoom', delay: .2 }" />    atur detail
 * <div v-reveal="{ stagger: .12 }" />               animasikan anak-anak elemen
 */
export const reveal = {
    mounted(el, binding) {
        const options = typeof binding.value === 'string' ? { from: binding.value } : binding.value ?? {};

        const {
            from = 'up',
            delay = 0,
            duration = 0.9,
            stagger = 0,
            start = 'top 85%',
            once = true,
        } = options;

        const targets = stagger ? Array.from(el.children) : el;
        const fromVars = presets[from] ?? presets.up;

        /**
         * Selama animasi berjalan, elemen ditandai `reveal-running`.
         *
         * Alasannya: sebagian besar `v-reveal` membungkus panel ber-
         * `backdrop-filter` (kartu keunggulan, kartu berita). Menganimasikan
         * opacity/transform di atas subtree ber-backdrop-filter memaksa browser
         * menyaring ulang seluruh isinya tiap frame — persis jeda yang terasa
         * saat sebuah section baru masuk layar. Kelas ini menukar panel ke latar
         * solid selama animasi (lihat `.reveal-running` di app.css), lalu efek
         * kacanya dikembalikan begitu animasi selesai.
         */
        let isRunning = false;

        // Penjaga agar `onUpdate` (dipanggil tiap frame) hanya menyentuh DOM
        // sekali di awal dan sekali di akhir animasi. `onUpdate` dipakai —
        // bukan `onStart` — karena `onStart` tidak ikut terpicu saat tween
        // diputar mundur (`once: false`).
        const running = (state) => {
            if (isRunning === state) {
                return;
            }

            isRunning = state;
            el.classList.toggle('reveal-running', state);
        };

        const tween = gsap.from(targets, {
            ...fromVars,
            duration,
            delay,
            stagger,
            ease: 'power3.out',
            paused: true,
            onUpdate: () => running(true),
            onComplete: () => running(false),
            onReverseComplete: () => running(false),
        });

        el._revealTrigger = ScrollTrigger.create({
            trigger: el,
            start,
            once,
            onEnter: () => tween.play(),
            onLeaveBack: () => (once ? null : tween.reverse()),
        });

        el._revealTween = tween;
    },

    unmounted(el) {
        el._revealTrigger?.kill();
        el._revealTween?.kill();
        delete el._revealTrigger;
        delete el._revealTween;
    },
};

export default {
    install(app) {
        app.directive('reveal', reveal);
    },
};
