<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { A11y, Autoplay, EffectCards, Keyboard } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-cards';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [A11y, Autoplay, EffectCards, Keyboard];

const accents = {
    mint: 'from-mint-400 via-mint-500 to-sage-600',
    gold: 'from-gold-300 via-gold-400 to-gold-600',
    sky: 'from-sky-soft-300 via-sky-soft-400 to-sky-soft-500',
    lilac: 'from-lilac-300 via-lilac-400 to-lilac-500',
};

const accentOf = (name) => accents[name] ?? accents.mint;

const cards = {
    perSlideOffset: 9,
    perSlideRotate: 3,
    slideShadows: false,
};
</script>

<template>
    <Swiper :modules="modules" effect="cards" :card-effect="cards" grab-cursor :keyboard="{ enabled: true }"
        :autoplay="{ delay: 4200, disableOnInteraction: false, pauseOnMouseEnter: true }" class="cards-swiper">
        <SwiperSlide v-for="item in props.items" :key="item.title">
            <article class="relative flex h-full flex-col justify-between overflow-hidden rounded-[2rem] bg-linear-to-br p-7 text-white"
                :class="accentOf(item.accent)">
                <div class="pattern-lattice absolute inset-0 opacity-25 mix-blend-overlay"></div>
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/25 blur-3xl">
                </div>

                <div class="relative">
                    <span
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/25 text-3xl backdrop-blur">
                        {{ item.icon }}
                    </span>

                    <h3 class="mt-5 font-display text-xl font-bold leading-snug">{{ item.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-white/85">{{ item.description }}</p>
                </div>

                <div class="relative mt-6 flex items-center gap-2 rounded-2xl bg-white/15 px-4 py-3 backdrop-blur">
                    <span aria-hidden="true">🕒</span>
                    <span class="text-xs font-semibold tracking-wide">{{ item.schedule }}</span>
                </div>
            </article>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
.cards-swiper {
    width: 100%;
    max-width: 22rem;
    height: 24rem;
    overflow: visible;
}

.cards-swiper :deep(.swiper-slide) {
    border-radius: 2rem;
    box-shadow: 0 30px 60px -32px rgba(36, 57, 49, 0.75);
}
</style>
