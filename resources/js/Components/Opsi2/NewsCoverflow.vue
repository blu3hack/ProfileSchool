<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { A11y, Autoplay, EffectCoverflow, Keyboard, Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [A11y, Autoplay, EffectCoverflow, Keyboard, Navigation, Pagination];

/** Peta aksen → kelas Tailwind, supaya warna kartu tetap konsisten. */
const accents = {
    mint: {
        media: 'from-mint-200 via-mint-100 to-canvas-100',
        badge: 'bg-mint-500/15 text-mint-600',
        glow: 'glow-mint',
        link: 'text-mint-600',
    },
    gold: {
        media: 'from-gold-300 via-cream-200 to-canvas-100',
        badge: 'bg-gold-500/15 text-gold-600',
        glow: 'glow-gold',
        link: 'text-gold-600',
    },
    sky: {
        media: 'from-sky-soft-200 via-sky-soft-100 to-canvas-100',
        badge: 'bg-sky-soft-500/15 text-sky-soft-500',
        glow: 'glow-sky',
        link: 'text-sky-soft-500',
    },
    lilac: {
        media: 'from-lilac-200 via-lilac-100 to-canvas-100',
        badge: 'bg-lilac-500/15 text-lilac-500',
        glow: 'glow-lilac',
        link: 'text-lilac-500',
    },
};

const accentOf = (name) => accents[name] ?? accents.mint;

const coverflow = {
    rotate: 34,
    stretch: 0,
    depth: 160,
    modifier: 1,
    slideShadows: false,
};

const breakpoints = {
    640: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
};

const hasLoop = props.items.length > 3;
</script>

<template>
    <Swiper :modules="modules" effect="coverflow" :slides-per-view="1.15" :space-between="20" :breakpoints="breakpoints"
        :centered-slides="true" :coverflow-effect="coverflow" :loop="hasLoop" grab-cursor
        :keyboard="{ enabled: true }"
        :autoplay="{ delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }"
        :pagination="{ clickable: true }" navigation class="coverflow-swiper pb-20!">
        <SwiperSlide v-for="item in props.items" :key="item.title" class="h-auto!">
            <article
                class="glass-panel group flex h-full flex-col overflow-hidden rounded-[1.75rem] transition duration-500 hover:-translate-y-1"
                :class="accentOf(item.accent).glow">
                <!-- Placeholder media: ganti dengan <img> ketika aset tersedia. -->
                <div class="relative flex h-44 items-center justify-center overflow-hidden bg-linear-to-br"
                    :class="accentOf(item.accent).media">
                    <div class="pattern-lattice absolute inset-0 opacity-60"></div>

                    <img v-if="item.image" :src="item.image" :alt="item.title" loading="lazy"
                        class="relative h-full w-full object-cover transition duration-700 group-hover:scale-105">
                    <span v-else class="relative text-5xl transition duration-500 group-hover:-translate-y-1">
                        {{ item.icon }}
                    </span>

                    <span
                        class="glass-panel absolute left-4 top-4 rounded-full px-3 py-1 text-[11px] font-bold tracking-wide"
                        :class="accentOf(item.accent).badge">
                        {{ item.category }}
                    </span>
                </div>

                <div class="flex flex-1 flex-col p-6">
                    <time class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sage-500">
                        {{ item.date }}
                    </time>

                    <h3 class="mt-2 font-display text-lg font-bold leading-snug text-sage-900">
                        {{ item.title }}
                    </h3>

                    <p class="mt-3 flex-1 text-sm leading-relaxed text-sage-700/80">{{ item.excerpt }}</p>

                    <a :href="item.href ?? '#berita'"
                        class="mt-5 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                        :class="accentOf(item.accent).link">
                        Baca Selengkapnya
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
/* Slide non-aktif diredupkan agar fokus jatuh ke kartu tengah. */
.coverflow-swiper :deep(.swiper-slide) {
    opacity: 0.45;
    transition: opacity 0.5s ease;
}

.coverflow-swiper :deep(.swiper-slide-active),
.coverflow-swiper :deep(.swiper-slide-next),
.coverflow-swiper :deep(.swiper-slide-prev) {
    opacity: 1;
}

.coverflow-swiper :deep(.swiper-button-next),
.coverflow-swiper :deep(.swiper-button-prev) {
    --swiper-navigation-size: 20px;

    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    color: #24886d;
    background: rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.75);
    box-shadow: 0 14px 34px -20px rgba(36, 57, 49, 0.7);
    transition: transform 0.3s ease, color 0.3s ease;
}

.coverflow-swiper :deep(.swiper-button-next:hover),
.coverflow-swiper :deep(.swiper-button-prev:hover) {
    transform: scale(1.08);
    color: #33a988;
}

.coverflow-swiper :deep(.swiper-pagination-bullet) {
    width: 20px;
    height: 4px;
    border-radius: 9999px;
    background: #8adcc2;
    opacity: 0.5;
    transition: width 0.3s ease, opacity 0.3s ease;
}

.coverflow-swiper :deep(.swiper-pagination-bullet-active) {
    width: 40px;
    background: #33a988;
    opacity: 1;
}
</style>
