import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Menyalakan autoplay Swiper hanya selama carousel-nya benar-benar terlihat.
 *
 * Autoplay bawaan Swiper jalan terus sejak halaman dimuat: saat pengunjung
 * membaca section paling bawah, carousel di atas tetap menganimasi transform
 * tiap frame. Di beranda ada dua carousel sekaligus (berita & kegiatan) yang
 * berbagi CPU dengan smooth scroll (Lenis) + ScrollTrigger, jadi kerja sia-sia
 * itu paling terasa sebagai scroll tersendat di perangkat kelas bawah.
 *
 * Tab yang disembunyikan juga dihentikan — browser sudah melambatkan timer di
 * background, tapi menghentikannya membuat slide tidak "meloncat jauh" begitu
 * pengunjung kembali.
 *
 * Dipakai dengan menyambungkan `onSwiper` ke event `@swiper` milik <Swiper>.
 *
 * @param {{ rootMargin?: string }} options
 */
export function useAutoplayInView(options = {}) {
    const rootMargin = options.rootMargin ?? '0px';

    let swiper = null;
    let observer = null;
    let visible = false;

    /**
     * Status terakhir yang sudah kita terapkan. Dipakai supaya `start()` tidak
     * dipanggil berulang untuk kondisi yang sama — pemanggilan ulang akan
     * me-reset hitungan delay dan membuat slide seolah tertahan.
     */
    let running = null;

    const sync = () => {
        const autoplay = swiper?.autoplay;

        if (!autoplay) {
            return;
        }

        const shouldRun = visible && !document.hidden;

        if (shouldRun === running) {
            return;
        }

        running = shouldRun;

        if (shouldRun) {
            autoplay.start();
        } else {
            autoplay.stop();
        }
    };

    const onVisibilityChange = () => sync();

    /** Dipasang ke `@swiper`; instance-nya membawa elemen akar carousel. */
    const onSwiper = (instance) => {
        swiper = instance;

        observer?.disconnect();

        // Tanpa IntersectionObserver, biarkan autoplay berjalan seperti semula.
        if (typeof IntersectionObserver === 'undefined' || !instance.el) {
            return;
        }

        observer = new IntersectionObserver((entries) => {
            visible = entries.some((entry) => entry.isIntersecting);
            sync();
        }, { rootMargin });

        observer.observe(instance.el);
    };

    onMounted(() => {
        document.addEventListener('visibilitychange', onVisibilityChange);
    });

    onBeforeUnmount(() => {
        document.removeEventListener('visibilitychange', onVisibilityChange);
        observer?.disconnect();
        observer = null;
        swiper = null;
    });

    return { onSwiper };
}
