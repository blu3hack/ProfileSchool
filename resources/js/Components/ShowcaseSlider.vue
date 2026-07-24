<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectCoverflow, Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-coverflow';

defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [Autoplay, EffectCoverflow, Navigation, Pagination];
</script>

<template>
    <Swiper :modules="modules" effect="coverflow" grab-cursor :centered-slides="true" slides-per-view="auto" :loop="true"
        :coverflow-effect="{ rotate: 0, stretch: 0, depth: 160, modifier: 2.2, slideShadows: false }"
        :autoplay="{ delay: 3200, disableOnInteraction: false }" :pagination="{ clickable: true }" navigation
        class="showcase-swiper pb-14!">
        <SwiperSlide v-for="item in items" :key="item.title" class="w-72! sm:w-80!">
            <article
                class="h-72 rounded-2xl border border-white/10 bg-linear-to-br from-white/10 to-white/2 p-6 backdrop-blur">
                <div class="mb-4 text-3xl">{{ item.icon }}</div>
                <h3 class="text-lg font-semibold text-white">{{ item.title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">{{ item.description }}</p>
            </article>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
.showcase-swiper :deep(.swiper-button-next),
.showcase-swiper :deep(.swiper-button-prev) {
    color: #a5b4fc;
    --swiper-navigation-size: 26px;
}

.showcase-swiper :deep(.swiper-pagination-bullet) {
    background: #94a3b8;
}

.showcase-swiper :deep(.swiper-pagination-bullet-active) {
    background: #6366f1;
}
</style>
