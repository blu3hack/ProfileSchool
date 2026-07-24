import { router } from '@inertiajs/vue3';

import { getSmoothScroll } from './smooth-scroll';

/** Gulir halus ke elemen; pakai Lenis bila tersedia. */
export const scrollToElement = (target, offset = -96) => {
    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset, duration: 1.4 });
    } else {
        target.scrollIntoView({ behavior: 'smooth' });
    }
};

/**
 * Buka tautan section (`#berita`, `#kontak`, …).
 *
 * Section-section itu hanya ada di landing page. Jadi bila elemennya tidak
 * ditemukan — misalnya pengunjung sedang berada di halaman berita — kita
 * pindah dulu ke landing lewat Inertia, baru menggulir setelah halaman siap.
 */
export const goToSection = (hash) => {
    const target = document.querySelector(hash);

    if (target) {
        scrollToElement(target);

        return;
    }

    router.visit(`/${hash}`, {
        onFinish: () => {
            // Tunggu satu frame supaya section sudah benar-benar ter-render.
            requestAnimationFrame(() => {
                const landed = document.querySelector(hash);

                if (landed) {
                    landed.scrollIntoView({ behavior: 'auto' });
                }
            });
        },
    });
};
