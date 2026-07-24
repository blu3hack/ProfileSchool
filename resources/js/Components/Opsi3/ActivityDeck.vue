<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { A11y, Autoplay, EffectCards, Keyboard } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-cards';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [A11y, Autoplay, EffectCards, Keyboard];

/**
 * Aksen kartu. Warna permukaan disuntikkan lewat custom property
 * `--deck-accent`, bukan kelas gradasi Tailwind, supaya kepekatannya bisa
 * dilemahkan otomatis saat tema terang (lihat `.deck-surface` di app.css).
 */
const accents = {
    mint: { accent: 'var(--color-aqua-500)', ring: 'ring-aqua-400/60', text: 'text-aqua-200' },
    gold: { accent: 'var(--color-solar-400)', ring: 'ring-solar-400/60', text: 'text-solar-300' },
    sky: { accent: 'var(--color-volt-400)', ring: 'ring-volt-400/60', text: 'text-volt-300' },
    lilac: { accent: 'var(--color-plasma-400)', ring: 'ring-plasma-400/60', text: 'text-plasma-300' },
};

const accentOf = (name) => accents[name] ?? accents.mint;

// Offset & rotasi lebih besar dari Opsi 2 supaya tumpukan terasa dalam.
const cards = {
    perSlideOffset: 12,
    perSlideRotate: 4,
    slideShadows: false,
};

/**
 * Tumpukan berputar terus seperti carousel berita: setelah kartu terakhir,
 * kembali ke kartu pertama. Loop butuh minimal beberapa slide agar Swiper
 * bisa menggandakan slide tanpa celah.
 */
const hasLoop = props.items.length > 2;
</script>

<template>
    <Swiper :modules="modules" effect="cards" :card-effect="cards" :loop="hasLoop" grab-cursor
        :keyboard="{ enabled: true }"
        :autoplay="{ delay: 4200, disableOnInteraction: false, pauseOnMouseEnter: true }" class="deck-swiper">
        <SwiperSlide v-for="item in props.items" :key="item.title">
            <article
                class="deck-surface relative flex h-full flex-col justify-between overflow-hidden rounded-[2rem] p-7 text-slate-50 ring-1 ring-inset"
                :class="accentOf(item.accent).ring"
                :style="{ '--deck-accent': accentOf(item.accent).accent }">
                <div class="relative">
                    <span
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-3xl">
                        {{ item.icon }}
                    </span>

                    <h3 class="mt-5 font-display text-xl font-bold leading-snug">{{ item.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-100/85">{{ item.description }}</p>
                </div>

                <div class="relative mt-6 flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-current" :class="accentOf(item.accent).text"
                        aria-hidden="true"></span>
                    <span class="text-xs font-semibold tracking-wide">{{ item.schedule }}</span>
                </div>
            </article>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
.deck-swiper {
    width: 100%;
    max-width: 23rem;
    height: 25rem;
    overflow: visible;
    /* Biarkan halaman tetap bisa di-scroll vertikal saat jari menggeser kartu
       ke samping; hanya gerak horizontal yang ditangani carousel. Ini yang
       membuat swipe terasa "menempel" di jari tanpa menahan scroll. */
    touch-action: pan-y;
}

.deck-swiper :deep(.swiper-slide) {
    border-radius: 2rem;
    /* Shadow tunggal & tipis — cukup memisahkan kartu dari latar tanpa memaksa
       repaint berat setiap frame saat digeser (glow cyan lama dihapus). */
    box-shadow: 0 18px 40px -24px rgba(0, 0, 0, 0.85);
    /* Percepatan perangkat keras: promosikan tiap slide ke layer GPU sendiri
       sehingga swipe hanya menggeser tekstur (murah), bukan menggambar ulang. */
    will-change: transform;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}
</style>
