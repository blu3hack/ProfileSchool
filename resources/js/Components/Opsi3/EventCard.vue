<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import EventCountdown from './EventCountdown.vue';
import HoloTilt from './HoloTilt.vue';
import { newsAccent } from '../../lib/news-accent';

/**
 * Kartu satu agenda: gambar, jadwal, judul, dan hitung mundurnya.
 * Dipakai di section "Next Event" beranda, halaman indeks agenda, dan
 * daftar agenda lain di halaman detail.
 */
const props = defineProps({
    item: { type: Object, required: true },
    /** `compact` dipakai di daftar agenda lain — media lebih pendek. */
    compact: { type: Boolean, default: false },
});

const accent = computed(() => newsAccent(props.item.accent));

const href = computed(
    () => props.item.href ?? (props.item.slug ? `/event/${props.item.slug}` : '/event'),
);
</script>

<template>
    <HoloTilt class="h-full" :max="8" :lift="20" :glare-color="accent.glare">
        <Link :href="href" :aria-label="`Lihat detail acara: ${props.item.title}`"
            class="holo-panel holo-edge group flex h-full flex-col overflow-hidden rounded-[1.75rem] transition-shadow duration-500"
            :class="accent.glow">
            <!-- Area media: pakai gambar bila ada, kalau tidak jatuh ke ikon. -->
            <div class="relative flex items-center justify-center overflow-hidden bg-linear-to-br"
                :class="[accent.media, props.compact ? 'h-32' : 'h-44']">
                <div class="pattern-lattice-neon absolute inset-0 opacity-25"></div>
                <div class="scanlines absolute inset-0 opacity-60"></div>

                <img v-if="props.item.image" :src="props.item.image" :alt="props.item.title" loading="lazy"
                    class="relative h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105 group-hover:opacity-100">
                <span v-else
                    class="depth-2 relative text-5xl drop-shadow-[0_0_18px_rgba(52,226,245,0.6)] transition duration-500 group-hover:-translate-y-1">
                    {{ props.item.icon }}
                </span>

                <span
                    class="absolute left-4 top-4 rounded-full border px-3 py-1 text-[11px] font-bold tracking-wide backdrop-blur"
                    :class="accent.badge">
                    {{ props.item.category }}
                </span>

                <span class="hairline-neon absolute inset-x-0 bottom-0"></span>
            </div>

            <div class="relative flex flex-1 flex-col p-6">
                <!-- Jadwal: tanggal + jam, ditulis berdampingan agar sekali baca. -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                    <time :datetime="props.item.startsAt">{{ props.item.date }}</time>
                    <span v-if="props.item.time" class="flex items-center gap-1" :class="accent.link">
                        <span aria-hidden="true">⏱</span>
                        {{ props.item.time }}
                    </span>
                </div>

                <h3 class="mt-2 font-display font-bold leading-snug text-slate-50 transition duration-300"
                    :class="[props.compact ? 'text-base' : 'text-lg', accent.hoverTitle]">
                    {{ props.item.title }}
                </h3>

                <p v-if="props.item.location"
                    class="mt-2 flex items-start gap-1.5 text-xs leading-relaxed text-slate-400">
                    <span aria-hidden="true">📍</span>
                    {{ props.item.location }}
                </p>

                <p v-if="!props.compact && props.item.excerpt"
                    class="mt-3 flex-1 text-sm leading-relaxed text-slate-300/75">
                    {{ props.item.excerpt }}
                </p>

                <!-- Hitung mundur menempel di dasar kartu supaya seluruh kartu
                     dalam satu baris punya garis dasar yang sama. -->
                <div class="mt-5">
                    <EventCountdown :starts-at="props.item.startsAt" :ends-at="props.item.endsAt"
                        :accent="props.item.accent" compact />
                </div>

                <span
                    class="mt-5 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                    :class="accent.link">
                    Lihat Detail Acara
                    <span aria-hidden="true">&rarr;</span>
                </span>
            </div>
        </Link>
    </HoloTilt>
</template>
