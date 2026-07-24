<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { A11y, Autoplay, Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [A11y, Autoplay, Navigation, Pagination];

const breakpoints = {
    640: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
    1280: { slidesPerView: 4 },
};

const levelStyles = {
    Internasional: 'bg-gold-400/20 text-gold-600 ring-gold-400/40',
    Nasional: 'bg-sage-500/15 text-sage-700 ring-sage-400/40',
    Provinsi: 'bg-cream-300/50 text-sage-700 ring-cream-500/50',
    default: 'bg-sage-100 text-sage-700 ring-sage-200',
};
</script>

<template>
    <Swiper :modules="modules" :slides-per-view="1.2" :space-between="20" :breakpoints="breakpoints" grab-cursor
        :loop="items.length > 4" :autoplay="{ delay: 3800, disableOnInteraction: false, pauseOnMouseEnter: true }"
        :pagination="{ clickable: true }" navigation class="achievement-swiper pb-16!">
        <SwiperSlide v-for="item in items" :key="item.title" class="h-auto!">
            <article
                class="group relative flex h-full flex-col items-start overflow-hidden rounded-3xl border border-cream-300 bg-cream-50 p-6 transition duration-300 hover:-translate-y-1.5 hover:border-gold-400 hover:bg-white hover:shadow-[0_20px_45px_-22px_rgba(166,125,54,0.45)]">
                <!-- Kilau emas tipis yang muncul saat hover. -->
                <div
                    class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-gold-400/20 opacity-0 blur-2xl transition duration-500 group-hover:opacity-100">
                </div>

                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-br from-gold-300/40 to-sage-200/60 text-2xl">
                    {{ item.icon }}
                </div>

                <span class="mt-4 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset"
                    :class="levelStyles[item.level] ?? levelStyles.default">
                    {{ item.level }}
                </span>

                <h3 class="mt-3 font-display text-base font-semibold leading-snug text-sage-900">{{ item.title }}</h3>

                <p class="mt-2 text-sm leading-relaxed text-sage-700/80">{{ item.description }}</p>

                <div class="mt-5 flex w-full items-center justify-between border-t border-sage-100 pt-4 text-xs">
                    <span class="font-semibold text-sage-600">{{ item.student }}</span>
                    <span class="rounded-full bg-sage-100 px-2.5 py-1 font-medium text-sage-700">{{ item.grade }}</span>
                </div>
            </article>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
.achievement-swiper :deep(.swiper-button-next),
.achievement-swiper :deep(.swiper-button-prev) {
    color: #a87d36;
    --swiper-navigation-size: 24px;
}

.achievement-swiper :deep(.swiper-pagination-bullet) {
    background: #cbab7d;
    opacity: 0.6;
}

.achievement-swiper :deep(.swiper-pagination-bullet-active) {
    background: #3f6b56;
    opacity: 1;
}
</style>
