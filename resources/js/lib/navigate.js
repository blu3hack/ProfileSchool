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
 *
 * Target yang BUKAN `#section` diperlakukan sebagai tautan biasa. Itu bukan
 * kasus teoretis: kolom "Target" pada menu navigasi bebas diisi apa saja, dan
 * sejak ada Halaman Kustom, mengarahkan sebuah menu ke `/datasiswa` adalah hal
 * yang wajar dilakukan admin. Tanpa cabang ini, nilai seperti itu masuk ke
 * `document.querySelector('/datasiswa')` — bukan selektor CSS yang sah, jadi
 * peramban melempar SyntaxError dan menu itu mati sama sekali.
 */
export const goToSection = (hash) => {
    if (typeof hash !== 'string' || !hash.startsWith('#')) {
        const href = (hash ?? '').trim();

        if (!href) {
            return;
        }

        // Alamat di dalam situs lewat Inertia (tanpa muat ulang penuh);
        // sisanya — https://, mailto:, tel: — diserahkan ke peramban.
        if (href.startsWith('/')) {
            router.visit(href);
        } else {
            window.location.href = href;
        }

        return;
    }

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
