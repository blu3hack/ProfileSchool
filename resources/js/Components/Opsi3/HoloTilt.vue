<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { gsap } from '../../lib/smooth-scroll';

/**
 * Kartu tilt 3D versi Opsi 3 — lebih ekstrem dari Opsi 2:
 * kemiringan lebih dalam, sorotan neon mengikuti kursor, kilau diagonal
 * yang menyapu, dan sedikit "angkat" pada sumbu Z saat hover.
 *
 * gsap.quickTo dipakai agar pointermove tidak membuat tween baru tiap frame.
 */
const props = defineProps({
    /** Sudut kemiringan maksimum (derajat). */
    max: { type: Number, default: 14 },
    scale: { type: Number, default: 1.04 },
    /** Seberapa jauh kartu terangkat mendekati kamera saat hover (px). */
    lift: { type: Number, default: 40 },
    glare: { type: Boolean, default: true },
    /** Warna sorotan kursor — sesuaikan dengan aksen kartu. */
    glareColor: { type: String, default: 'rgba(124, 243, 255, 0.28)' },
    /**
     * Radius lapisan overlay. Harus disamakan dengan radius kartu di dalam
     * slot, karena `border-radius: inherit` tidak bisa menembus wrapper tilt.
     */
    radius: { type: String, default: '1.75rem' },
});

const scene = ref(null);
const body = ref(null);
const glare = ref(null);

let setRotateX = null;
let setRotateY = null;
let setScale = null;
let setZ = null;
let setGlareX = null;
let setGlareY = null;
let reduced = false;

const hovering = ref(false);

/**
 * Ukuran & posisi kartu, DI-CACHE.
 *
 * Dulu `getBoundingClientRect()` dipanggil di dalam `pointermove` — jadi tiap
 * gerakan kursor: baca tata letak → GSAP menulis transform → gerakan berikutnya
 * membaca lagi. Pola baca-setelah-tulis itulah "forced reflow": browser wajib
 * menghitung ulang tata letak secara sinkron sebelum bisa menjawab pembacaannya,
 * puluhan kali per detik, dan DevTools melaporkannya sebagai insight
 * "Forced reflow".
 *
 * Nilainya hanya berubah saat kartu bergerak — yaitu ketika jendela diubah
 * ukurannya atau halaman digulir. Jadi cukup diukur sekali saat kursor masuk,
 * lalu diperbarui pada dua kejadian itu saja. Listener-nya pun hanya terpasang
 * selama kursor benar-benar berada di atas kartu.
 */
let rect = null;

const measure = () => {
    rect = scene.value?.getBoundingClientRect() ?? null;
};

const watchBounds = (on) => {
    const method = on ? 'addEventListener' : 'removeEventListener';

    window[method]('scroll', measure, { passive: true });
    window[method]('resize', measure, { passive: true });
};

onMounted(() => {
    reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced || !body.value) {
        return;
    }

    const options = { duration: 0.6, ease: 'power3.out' };

    setRotateX = gsap.quickTo(body.value, 'rotationX', options);
    setRotateY = gsap.quickTo(body.value, 'rotationY', options);
    setScale = gsap.quickTo(body.value, 'scale', options);
    setZ = gsap.quickTo(body.value, 'z', options);

    /**
     * Sorotan digeser lewat transform, bukan dengan menulis ulang
     * `radial-gradient(... at X% Y% ...)` seperti sebelumnya.
     *
     * Mengubah titik pusat gradasi berarti gradasi selebar 20rem itu DIGAMBAR
     * ULANG tiap kali kursor bergerak. Sebagai transform, ia cukup dilukis
     * sekali lalu digeser compositor — nol repaint.
     */
    if (glare.value) {
        setGlareX = gsap.quickTo(glare.value, 'x', options);
        setGlareY = gsap.quickTo(glare.value, 'y', options);
    }
});

onBeforeUnmount(() => {
    watchBounds(false);

    if (body.value) {
        gsap.killTweensOf(body.value);
    }

    if (glare.value) {
        gsap.killTweensOf(glare.value);
    }
});

const onPointerEnter = () => {
    if (reduced) {
        return;
    }

    measure();
    watchBounds(true);
};

const onPointerMove = (event) => {
    if (reduced || !setRotateX) {
        return;
    }

    // Kursor bisa masuk lewat jalur yang tidak memicu `pointerenter`
    // (mis. elemen anak yang baru dirender) — ukur di tempat bila perlu.
    if (!rect) {
        measure();

        if (!rect) {
            return;
        }
    }

    // Posisi kursor relatif terhadap pusat kartu: -0.5 … 0.5
    const px = (event.clientX - rect.left) / rect.width;
    const py = (event.clientY - rect.top) / rect.height;

    setRotateX(-(py - 0.5) * props.max * 2);
    setRotateY((px - 0.5) * props.max * 2);
    setScale(props.scale);
    setZ(props.lift);

    setGlareX?.(px * rect.width);
    setGlareY?.(py * rect.height);

    hovering.value = true;
};

const onPointerLeave = () => {
    hovering.value = false;
    watchBounds(false);
    rect = null;

    if (reduced || !setRotateX) {
        return;
    }

    setRotateX(0);
    setRotateY(0);
    setScale(1);
    setZ(0);
};
</script>

<template>
    <div ref="scene" class="stage-3d" @pointerenter="onPointerEnter" @pointermove="onPointerMove"
        @pointerleave="onPointerLeave">
        <div ref="body" class="stage-body relative h-full">
            <slot :hovering="hovering" />

            <!-- Sorotan neon mengikuti kursor. Gradasinya statis; yang bergerak
                 hanya `transform` bola di dalamnya (lihat catatan di script). -->
            <div v-if="props.glare"
                class="pointer-events-none absolute inset-0 overflow-hidden opacity-0 transition-opacity duration-300"
                :class="{ 'opacity-100': hovering }" :style="{ borderRadius: props.radius }">
                <div ref="glare" class="tilt-glare" :style="{ '--glare-color': props.glareColor }"></div>
            </div>

            <!-- Kilau diagonal yang menyapu sekali saat kursor masuk. -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden"
                :style="{ borderRadius: props.radius }">
                <div class="absolute -inset-y-12 -left-1/3 w-1/3 -skew-x-12 bg-linear-to-r from-transparent via-white/12 to-transparent transition-transform duration-900 ease-out"
                    :class="hovering ? 'translate-x-[420%]' : 'translate-x-0'"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Bola sorotan: gradasi berukuran tetap yang titik nolnya berada di sudut
   kiri-atas kartu. Margin negatif memusatkannya pada koordinat yang diberikan
   GSAP lewat `x`/`y`, sehingga JS cukup menulis transform — tanpa pernah
   menyentuh properti yang memicu paint. */
.tilt-glare {
    position: absolute;
    left: 0;
    top: 0;
    width: 28rem;
    height: 28rem;
    margin-left: -14rem;
    margin-top: -14rem;
    background: radial-gradient(closest-side, var(--glare-color), transparent 70%);
}
</style>
