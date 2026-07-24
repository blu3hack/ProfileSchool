import 'lenis/dist/lenis.css';

import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

let lenis = null;
let rafHandler = null;

/**
 * Aktifkan smooth scroll (Lenis) dan sinkronkan dengan GSAP ScrollTrigger,
 * supaya animasi on-scroll tetap presisi saat scroll di-interpolasi.
 */
export function createSmoothScroll(options = {}) {
    if (lenis) {
        return lenis;
    }

    lenis = new Lenis({
        duration: 1.1,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,
        touchMultiplier: 1.6,
        ...options,
    });

    lenis.on('scroll', ScrollTrigger.update);

    rafHandler = (time) => lenis.raf(time * 1000);
    gsap.ticker.add(rafHandler);
    gsap.ticker.lagSmoothing(0);

    return lenis;
}

export function getSmoothScroll() {
    return lenis;
}

/** Dipakai saat pindah halaman Inertia: balik ke atas tanpa animasi. */
export function resetScroll() {
    lenis?.scrollTo(0, { immediate: true });
    ScrollTrigger.refresh();
}

export function destroySmoothScroll() {
    if (!lenis) {
        return;
    }

    if (rafHandler) {
        gsap.ticker.remove(rafHandler);
        rafHandler = null;
    }

    lenis.destroy();
    lenis = null;
}

export { gsap, ScrollTrigger };
