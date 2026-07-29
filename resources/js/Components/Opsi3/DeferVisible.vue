<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Menunda pembuatan isi slot sampai wadahnya mendekati layar.
 *
 * Dipasangkan dengan `defineAsyncComponent`, ini yang membuat berkas komponen
 * berat (mis. Swiper) baru diunduh saat pengunjung hampir sampai ke section-nya
 * — bukan ikut terunduh di muatan awal beranda.
 *
 * `rootMargin` sengaja longgar supaya isinya sudah siap sebelum benar-benar
 * terlihat; placeholder `minHeight` menahan tinggi agar tata letak tidak
 * melompat saat isi akhirnya masuk.
 */
const props = defineProps({
    /** Jarak ancang-ancang sebelum wadah masuk layar. */
    rootMargin: { type: String, default: '600px 0px' },
    /** Tinggi cadangan selama isi belum dibuat. */
    minHeight: { type: String, default: '20rem' },
});

const root = ref(null);
const shown = ref(false);

let observer = null;
let idleHandle = null;

const disconnect = () => {
    observer?.disconnect();
    observer = null;
};

/**
 * Isi slot dibuat saat main thread menganggur, bukan langsung di dalam callback
 * IntersectionObserver.
 *
 * Pemicunya adalah gulir, jadi callback-nya berjalan tepat ketika main thread
 * paling sibuk — Lenis menginterpolasi gulir, ScrollTrigger memperbarui puluhan
 * trigger. Menambahkan inisialisasi Swiper (evaluasi modul + kloning slide untuk
 * loop + pengukuran tata letak) di frame yang sama menghasilkan satu long task,
 * dan selama itu SEMUA input pengunjung hanya mengantre. Itulah bentuk yang
 * terlihat di DevTools sebagai INP dengan "input delay" besar tapi "processing
 * duration" nyaris nol: bukan penanganannya yang lambat, melainkan gilirannya
 * yang tak kunjung datang.
 *
 * `rootMargin` 600px memberi ruang ancang-ancang; `timeout` menjamin isinya
 * tetap dibuat walau main thread tak pernah benar-benar senggang.
 */
const schedule = (callback) => (typeof requestIdleCallback === 'function'
    ? requestIdleCallback(callback, { timeout: 500 })
    : setTimeout(callback, 1));

const unschedule = (handle) => (typeof cancelIdleCallback === 'function'
    ? cancelIdleCallback(handle)
    : clearTimeout(handle));

const reveal = () => {
    if (shown.value || idleHandle !== null) {
        return;
    }

    disconnect();

    idleHandle = schedule(() => {
        idleHandle = null;
        shown.value = true;
    });
};

onMounted(() => {
    // Tanpa IntersectionObserver, tampilkan langsung — lebih baik berat
    // daripada kosong permanen.
    if (typeof IntersectionObserver === 'undefined') {
        reveal();

        return;
    }

    observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            reveal();
        }
    }, { rootMargin: props.rootMargin });

    observer.observe(root.value);
});

onBeforeUnmount(() => {
    disconnect();

    if (idleHandle !== null) {
        unschedule(idleHandle);
        idleHandle = null;
    }
});
</script>

<template>
    <div ref="root" :style="shown ? null : { minHeight: props.minHeight }">
        <slot v-if="shown" />
    </div>
</template>
