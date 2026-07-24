<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { gsap, ScrollTrigger } from '../../lib/smooth-scroll';

/**
 * Linimasa prestasi: garis cahaya vertikal yang terisi mengikuti scroll,
 * dengan kartu melayang bergantian kiri–kanan.
 */
const props = defineProps({
    items: { type: Array, default: () => [] },
});

const root = ref(null);
const line = ref(null);

let ctx = null;

const levelTone = {
    Internasional: 'bg-lilac-500/12 text-lilac-500 ring-lilac-300/60',
    Nasional: 'bg-mint-500/12 text-mint-600 ring-mint-300/60',
    Provinsi: 'bg-gold-500/12 text-gold-600 ring-gold-300/60',
};

const toneOf = (level) => levelTone[level] ?? levelTone.Nasional;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    // gsap.context menampung semua tween + trigger agar sekali revert bersih.
    ctx = gsap.context(() => {
        // Garis cahaya terisi seiring scroll.
        gsap.fromTo(
            line.value,
            { scaleY: 0 },
            {
                scaleY: 1,
                ease: 'none',
                transformOrigin: 'top center',
                scrollTrigger: {
                    trigger: root.value,
                    start: 'top 70%',
                    end: 'bottom 75%',
                    scrub: true,
                },
            },
        );

        const rows = gsap.utils.toArray('[data-timeline-row]', root.value);

        rows.forEach((row, index) => {
            const card = row.querySelector('[data-timeline-card]');
            const node = row.querySelector('[data-timeline-node]');
            const fromLeft = index % 2 === 0;

            gsap.from(card, {
                x: fromLeft ? -70 : 70,
                y: 40,
                opacity: 0,
                rotateY: fromLeft ? -12 : 12,
                duration: 1,
                ease: 'power3.out',
                scrollTrigger: { trigger: row, start: 'top 82%', once: true },
            });

            gsap.from(node, {
                scale: 0,
                opacity: 0,
                duration: 0.7,
                delay: 0.15,
                ease: 'back.out(2)',
                scrollTrigger: { trigger: row, start: 'top 82%', once: true },
            });
        });

        // Konten baru bisa menggeser layout — hitung ulang sekali setelah mount.
        ScrollTrigger.refresh();
    }, root.value);
});

onBeforeUnmount(() => ctx?.revert());
</script>

<template>
    <div ref="root" class="relative" style="perspective: 1200px">
        <!-- Rel linimasa -->
        <div class="pointer-events-none absolute inset-y-0 left-5 w-px bg-sage-200/70 md:left-1/2 md:-translate-x-1/2">
        </div>
        <div ref="line"
            class="pointer-events-none absolute inset-y-0 left-5 w-[3px] -translate-x-[1px] rounded-full bg-linear-to-b from-mint-400 via-lilac-400 to-gold-400 shadow-[0_0_18px_rgba(87,198,164,0.65)] md:left-1/2 md:-translate-x-1/2">
        </div>

        <ol class="relative space-y-10 md:space-y-16">
            <li v-for="(item, index) in props.items" :key="item.title" data-timeline-row
                class="relative flex flex-col gap-4 pl-14 md:grid md:grid-cols-2 md:items-center md:gap-12 md:pl-0">
                <!-- Titik simpul -->
                <span data-timeline-node
                    class="absolute left-5 top-6 z-10 flex h-10 w-10 -translate-x-1/2 items-center justify-center rounded-full glass-panel text-lg md:left-1/2 md:top-1/2 md:-translate-y-1/2">
                    <span class="absolute inset-0 rounded-full bg-mint-300/40 blur-md"></span>
                    <span class="relative">{{ item.icon }}</span>
                </span>

                <!-- Kartu prestasi, bergantian sisi kiri/kanan di layar lebar -->
                <div data-timeline-card
                    class="glass-panel group rounded-[1.75rem] p-6 transition duration-500 hover:-translate-y-1.5 hover:shadow-[0_28px_60px_-30px_rgba(36,57,49,0.6)]"
                    :class="index % 2 === 0
                        ? 'md:col-start-1 md:mr-4 md:text-right'
                        : 'md:col-start-2 md:ml-4'">
                    <div class="flex flex-wrap items-center gap-2"
                        :class="index % 2 === 0 ? 'md:justify-end' : ''">
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset"
                            :class="toneOf(item.level)">
                            {{ item.level }}
                        </span>
                        <span class="neo-inset rounded-full px-3 py-1 text-[11px] font-bold text-sage-600">
                            {{ item.year }}
                        </span>
                    </div>

                    <h3 class="mt-4 font-display text-lg font-bold leading-snug text-sage-900">{{ item.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-sage-700/80">{{ item.description }}</p>

                    <div class="mt-5 hairline"></div>

                    <p class="mt-4 text-sm font-semibold text-sage-800">
                        {{ item.student }}
                        <span class="ml-1 text-xs font-medium text-sage-500">· {{ item.grade }}</span>
                    </p>
                </div>

                <!-- Kolom kosong penyeimbang grid -->
                <div class="hidden md:block" :class="index % 2 === 0 ? 'md:col-start-2' : 'md:col-start-1 md:row-start-1'">
                </div>
            </li>
        </ol>
    </div>
</template>
