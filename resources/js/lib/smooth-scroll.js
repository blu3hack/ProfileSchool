import 'lenis/dist/lenis.css';

import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * `ignoreMobileResize` — di ponsel, gulir pertama menyembunyikan address bar
 * sehingga `window.innerHeight` berubah dan ScrollTrigger ikut menghitung ulang
 * seluruh trigger di tengah gulir. Semua animasi `scrub` lalu meloncat ke posisi
 * barunya sekaligus. Perubahan tinggi saja kini diabaikan; rotasi layar (lebar
 * ikut berubah) tetap memicu refresh.
 */
ScrollTrigger.config({ ignoreMobileResize: true });

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
        // 1.1 detik terasa "melayang": input roda selesai jauh setelah jarinya
        // berhenti. 0.9 tetap mulus tapi lebih menempel pada gerakan pengguna.
        duration: 0.9,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,
        touchMultiplier: 1.6,
        ...options,
    });

    lenis.on('scroll', ScrollTrigger.update);

    rafHandler = (time) => lenis.raf(time * 1000);
    gsap.ticker.add(rafHandler);

    /**
     * lagSmoothing DIBIARKAN AKTIF (bawaan gsap: 500ms → dianggap 33ms).
     *
     * Sebelumnya dimatikan (`lagSmoothing(0)`). Akibatnya, setiap kali main
     * thread tersendat — muat pertama dengan cache kosong adalah kasus
     * terburuknya: mengurai bundel, mendekode foto hero, menyiapkan carousel —
     * frame berikutnya membawa selisih waktu besar dan Lenis memajukan gulir
     * sejauh itu sekaligus. Itulah "loncatan" yang terasa. Dengan pengaman ini,
     * frame yang tertunda diperlakukan sebagai satu frame biasa: animasi
     * tertinggal sedikit, tapi tidak pernah melompat.
     */
    gsap.ticker.lagSmoothing(500, 33);

    return lenis;
}

/**
 * Refresh ScrollTrigger yang digabung dalam satu frame.
 *
 * `ScrollTrigger.refresh()` menghitung ulang start/end SEMUA trigger di halaman
 * secara sinkron — di beranda jumlahnya puluhan. Bila dipanggil beberapa kali
 * saat mount (halaman + tiap komponen ber-trigger), biayanya berlipat dan
 * frame-frame pertama hilang. Semua pemanggilan dalam satu frame di sini
 * diciutkan jadi satu refresh.
 */
let refreshQueued = false;

export function refreshScrollTriggers() {
    if (refreshQueued) {
        return;
    }

    refreshQueued = true;

    requestAnimationFrame(() => {
        refreshQueued = false;
        ScrollTrigger.refresh();
    });
}

export function getSmoothScroll() {
    return lenis;
}

/** Dipakai saat pindah halaman Inertia: balik ke atas tanpa animasi. */
export function resetScroll() {
    lenis?.scrollTo(0, { immediate: true });
    refreshScrollTriggers();
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
