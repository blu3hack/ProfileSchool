<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import HoloTilt from './HoloTilt.vue';
import { newsAccent } from '../../lib/news-accent';

const props = defineProps({
    item: { type: Object, required: true },
    /** `compact` dipakai di daftar berita terkait — media lebih pendek. */
    compact: { type: Boolean, default: false },
});

const accent = computed(() => newsAccent(props.item.accent));

const href = computed(
    () => props.item.href ?? (props.item.slug ? `/berita/${props.item.slug}` : '/berita'),
);
</script>

<template>
    <HoloTilt class="h-full" :max="8" :lift="20" :glare-color="accent.glare">
        <Link :href="href" :aria-label="`Baca berita: ${props.item.title}`"
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

                <span class="absolute left-4 top-4 rounded-full border px-3 py-1 text-[11px] font-bold tracking-wide backdrop-blur"
                    :class="accent.badge">
                    {{ props.item.category }}
                </span>

                <span class="hairline-neon absolute inset-x-0 bottom-0"></span>
            </div>

            <div class="relative flex flex-1 flex-col p-6">
                <time :datetime="props.item.publishedAt"
                    class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                    {{ props.item.date }}
                </time>

                <h3 class="mt-2 font-display font-bold leading-snug text-slate-50 transition duration-300"
                    :class="[props.compact ? 'text-base' : 'text-lg', accent.hoverTitle]">
                    {{ props.item.title }}
                </h3>

                <p v-if="!props.compact" class="mt-3 flex-1 text-sm leading-relaxed text-slate-300/75">
                    {{ props.item.excerpt }}
                </p>

                <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                    :class="accent.link">
                    Baca Selengkapnya
                    <span aria-hidden="true">&rarr;</span>
                </span>
            </div>
        </Link>
    </HoloTilt>
</template>
