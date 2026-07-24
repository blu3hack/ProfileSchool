<script setup>
import { Link } from '@inertiajs/vue3';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { A11y, Autoplay, EffectCoverflow, Keyboard, Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import HoloTilt from './HoloTilt.vue';
import { newsAccent } from '../../lib/news-accent';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [A11y, Autoplay, EffectCoverflow, Keyboard, Navigation, Pagination];

/** Peta aksen → kelas neon dipakai bersama kartu berita di halaman lain. */
const accentOf = (name) => newsAccent(name);

// Rotasi lebih tajam & kedalaman lebih besar dari Opsi 2 → kesan 3D kuat.
const coverflow = {
    rotate: 46,
    stretch: -10,
    depth: 260,
    modifier: 1,
    slideShadows: false,
};

const breakpoints = {
    640: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
};

const hasLoop = props.items.length > 3;

/** Tujuan kartu: halaman detail berita. */
const linkOf = (item) => item.href ?? (item.slug ? `/berita/${item.slug}` : '/berita');
</script>

<template>
    <Swiper :modules="modules" effect="coverflow" :slides-per-view="1.15" :space-between="24" :breakpoints="breakpoints"
        :centered-slides="true" :coverflow-effect="coverflow" :loop="hasLoop" grab-cursor
        :keyboard="{ enabled: true }"
        :autoplay="{ delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }"
        :pagination="{ clickable: true }" navigation class="neon-swiper pb-20!">
        <SwiperSlide v-for="item in props.items" :key="item.title" class="h-auto!">
            <HoloTilt class="h-full" :max="10" :lift="26" :glare-color="accentOf(item.accent).glare">
                <!-- Seluruh kartu jadi tautan: klik di mana pun membuka detail berita. -->
                <Link :href="linkOf(item)"
                    class="holo-panel holo-edge group flex h-full flex-col overflow-hidden rounded-[1.75rem] transition-shadow duration-500"
                    :class="accentOf(item.accent).glow" :aria-label="`Baca berita: ${item.title}`">
                    <!-- Placeholder media: ganti dengan <img> ketika aset tersedia. -->
                    <div class="relative flex h-44 items-center justify-center overflow-hidden bg-linear-to-br"
                        :class="accentOf(item.accent).media">
                        <div class="pattern-lattice-neon absolute inset-0 opacity-25"></div>
                        <div class="scanlines absolute inset-0 opacity-60"></div>

                        <img v-if="item.image" :src="item.image" :alt="item.title" loading="lazy"
                            class="relative h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105 group-hover:opacity-100">
                        <span v-else
                            class="depth-2 relative text-5xl drop-shadow-[0_0_18px_rgba(52,226,245,0.6)] transition duration-500 group-hover:-translate-y-1">
                            {{ item.icon }}
                        </span>

                        <span
                            class="absolute left-4 top-4 rounded-full border px-3 py-1 text-[11px] font-bold tracking-wide backdrop-blur"
                            :class="accentOf(item.accent).badge">
                            {{ item.category }}
                        </span>

                        <!-- Garis pemisah bercahaya di bawah area media. -->
                        <span class="hairline-neon absolute inset-x-0 bottom-0"></span>
                    </div>

                    <div class="relative flex flex-1 flex-col p-6">
                        <time class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ item.date }}
                        </time>

                        <h3 class="mt-2 font-display text-lg font-bold leading-snug text-slate-50">
                            {{ item.title }}
                        </h3>

                        <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-300/75">{{ item.excerpt }}</p>

                        <span
                            class="mt-5 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                            :class="accentOf(item.accent).link">
                            Baca Selengkapnya
                            <span aria-hidden="true">&rarr;</span>
                        </span>
                    </div>
                </Link>
            </HoloTilt>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
/* Slide non-aktif diredupkan & diturunkan saturasinya agar fokus ke tengah. */
.neon-swiper :deep(.swiper-slide) {
    opacity: 0.3;
    filter: saturate(0.6);
    transition: opacity 0.5s ease, filter 0.5s ease;
}

.neon-swiper :deep(.swiper-slide-active),
.neon-swiper :deep(.swiper-slide-next),
.neon-swiper :deep(.swiper-slide-prev) {
    opacity: 1;
    filter: saturate(1);
}

.neon-swiper :deep(.swiper-button-next),
.neon-swiper :deep(.swiper-button-prev) {
    --swiper-navigation-size: 20px;

    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    color: var(--color-aqua-300);
    background: var(--panel-bg);
    backdrop-filter: blur(14px);
    border: 1px solid var(--panel-border);
    box-shadow: 0 0 24px -6px rgba(52, 226, 245, 0.6);
    transition: transform 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

.neon-swiper :deep(.swiper-button-next:hover),
.neon-swiper :deep(.swiper-button-prev:hover) {
    transform: scale(1.08);
    color: var(--color-aqua-500);
    box-shadow: 0 0 36px -4px rgba(169, 123, 255, 0.8);
}

.neon-swiper :deep(.swiper-pagination-bullet) {
    width: 20px;
    height: 3px;
    border-radius: 9999px;
    background: var(--color-aqua-400);
    opacity: 0.35;
    transition: width 0.3s ease, opacity 0.3s ease, box-shadow 0.3s ease;
}

.neon-swiper :deep(.swiper-pagination-bullet-active) {
    width: 44px;
    opacity: 1;
    box-shadow: 0 0 14px rgba(52, 226, 245, 0.9);
}
</style>
