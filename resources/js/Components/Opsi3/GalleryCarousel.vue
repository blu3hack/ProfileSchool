<script setup>
import { computed } from 'vue';

/**
 * Carousel galeri dengan perilaku "alternate": dua baris foto bergerak otomatis
 * ke arah berlawanan (baris atas ke kiri, baris bawah ke kanan) membentuk
 * marquee tanpa henti. Berhenti saat disentuh/hover. Klik foto → buka popup.
 *
 * Ringan (murni animasi CSS transform) dan berhenti otomatis bila pengunjung
 * memilih "kurangi gerak".
 */
const props = defineProps({
    images: { type: Array, default: () => [] },
});

const emit = defineEmits(['open']);

/**
 * Bagi foto ke dua baris berselang-seling, sambil menyimpan indeks aslinya
 * (`i`) agar popup membuka foto yang benar dari daftar penuh.
 */
const rows = computed(() => {
    const withIndex = props.images.map((image, i) => ({ ...image, i }));
    const top = withIndex.filter((_, i) => i % 2 === 0);
    const bottom = withIndex.filter((_, i) => i % 2 === 1);

    return [top, bottom].filter((row) => row.length);
});
</script>

<template>
    <div class="gallery-marquee relative">
        <!-- Gradasi tepi agar foto "memudar" masuk/keluar, bukan terpotong tegas. -->
        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-16 bg-linear-to-r from-void-950 to-transparent sm:w-28"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-16 bg-linear-to-l from-void-950 to-transparent sm:w-28"></div>

        <div class="space-y-5">
            <div v-for="(row, rowIndex) in rows" :key="rowIndex" class="marquee-track overflow-hidden">
                <!-- Isi baris digandakan dua kali supaya perulangannya mulus. -->
                <div class="marquee-row flex w-max gap-5" :class="rowIndex % 2 === 0 ? 'marquee-left' : 'marquee-right'">
                    <button v-for="(image, cellIndex) in [...row, ...row]" :key="`${rowIndex}-${cellIndex}`" type="button"
                        class="group relative block h-40 w-60 shrink-0 overflow-hidden rounded-2xl border border-white/10 bg-void-900 transition duration-300 hover:border-aqua-400/50 sm:h-48 sm:w-72"
                        :aria-label="`Buka foto: ${image.title}`" @click="emit('open', image.i)">
                        <img :src="image.src" :srcset="image.srcset"
                            sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                            :alt="image.alt || image.title" loading="lazy" decoding="async"
                            class="h-full w-full object-cover opacity-85 transition duration-500 group-hover:scale-105 group-hover:opacity-100">
                        <div class="scanlines pointer-events-none absolute inset-0 opacity-30"></div>
                        <!-- Judul muncul dari bawah saat hover. -->
                        <div class="absolute inset-x-0 bottom-0 translate-y-2 bg-linear-to-t from-void-950/90 to-transparent px-4 pb-3 pt-8 opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                            <p class="line-clamp-1 text-sm font-bold text-slate-50">{{ image.title }}</p>
                        </div>
                        <span class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-void-950/60 text-xs text-aqua-200 opacity-0 transition duration-300 group-hover:opacity-100"
                            aria-hidden="true">⤢</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Marquee dua arah. Track digandakan 2×, jadi bergeser -50% = satu putaran
   penuh yang menyambung mulus. */
.marquee-row {
    animation-duration: 42s;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    will-change: transform;
}

.marquee-left {
    animation-name: marquee-left;
}

.marquee-right {
    animation-name: marquee-right;
}

/* Baris bawah bergeser lebih santai supaya kedua arah tidak terasa seragam. */
.marquee-right {
    animation-duration: 50s;
}

/* Jeda saat pengunjung ingin memperhatikan satu foto. */
.gallery-marquee:hover .marquee-row {
    animation-play-state: paused;
}

@keyframes marquee-left {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }
}

@keyframes marquee-right {
    from {
        transform: translateX(-50%);
    }

    to {
        transform: translateX(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .marquee-row {
        animation: none;
    }

    /* Tanpa animasi, baris cukup bisa digulir manual. */
    .marquee-track {
        overflow-x: auto;
    }
}
</style>
