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
};
</script>

<template>
    <Swiper :modules="modules" :slides-per-view="1.15" :space-between="24" :breakpoints="breakpoints" grab-cursor
        :loop="items.length > 3" :autoplay="{ delay: 4500, disableOnInteraction: false, pauseOnMouseEnter: true }"
        :pagination="{ clickable: true }" navigation class="news-swiper pb-16!">
        <SwiperSlide v-for="item in items" :key="item.title" class="h-auto!">
            <article
                class="group flex h-full flex-col overflow-hidden rounded-3xl border border-sage-100 bg-white shadow-[0_10px_40px_-24px_rgba(36,57,49,0.5)] transition duration-300 hover:-translate-y-1.5 hover:border-sage-300 hover:shadow-[0_20px_50px_-20px_rgba(36,57,49,0.35)]">
                <!-- Placeholder gambar: ganti dengan <img> saat aset tersedia. -->
                <div class="relative flex h-48 items-center justify-center overflow-hidden"
                    :class="item.tone ?? 'bg-linear-to-br from-sage-200 to-cream-200'">
                    <img v-if="item.image" :src="item.image" :alt="item.title" loading="lazy"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    <span v-else class="text-5xl transition duration-500 group-hover:scale-110">{{ item.icon }}</span>

                    <span
                        class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-sage-700 backdrop-blur">
                        {{ item.category }}
                    </span>
                </div>

                <div class="flex flex-1 flex-col p-6">
                    <time class="text-xs font-medium uppercase tracking-wider text-sage-500">{{ item.date }}</time>

                    <h3 class="mt-2 font-display text-lg font-semibold leading-snug text-sage-900">
                        {{ item.title }}
                    </h3>

                    <p class="mt-3 flex-1 text-sm leading-relaxed text-sage-700/80">{{ item.excerpt }}</p>

                    <a :href="item.href ?? '#berita'"
                        class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-sage-600 transition group-hover:gap-3 hover:text-gold-600">
                        Baca Selengkapnya
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
.news-swiper :deep(.swiper-button-next),
.news-swiper :deep(.swiper-button-prev) {
    color: #3f6b56;
    --swiper-navigation-size: 24px;
}

.news-swiper :deep(.swiper-pagination-bullet) {
    background: #9bbfab;
    opacity: 0.6;
}

.news-swiper :deep(.swiper-pagination-bullet-active) {
    background: #c69a4c;
    opacity: 1;
}
</style>
