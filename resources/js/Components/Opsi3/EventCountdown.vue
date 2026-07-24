<script setup>
import { computed } from 'vue';

import { useCountdown } from '../../lib/countdown';
import { newsAccent } from '../../lib/news-accent';

/**
 * Hitung mundur menuju satu acara.
 *
 * Tiga keadaan yang mungkin tampil:
 *  - menunggu  → empat blok angka (hari/jam/menit/detik);
 *  - berlangsung → penanda "Sedang Berlangsung" yang berdenyut;
 *  - selesai   → penanda "Telah Terlaksana".
 */
const props = defineProps({
    startsAt: { type: String, default: '' },
    endsAt: { type: String, default: null },
    accent: { type: String, default: 'mint' },
    /** `compact` dipakai di dalam kartu; versi normal untuk halaman detail. */
    compact: { type: Boolean, default: false },
});

const { units, ongoing, finished } = useCountdown(
    () => props.startsAt,
    { endsAt: () => props.endsAt },
);

const tone = computed(() => newsAccent(props.accent));

/** Teks alternatif untuk pembaca layar — angka yang berdetak tidak dibacakan. */
const summary = computed(() => {
    if (finished.value) {
        return 'Acara telah terlaksana.';
    }

    if (ongoing.value) {
        return 'Acara sedang berlangsung.';
    }

    const [days, hours, minutes] = units.value;

    return `Menuju acara: ${days.value} hari ${hours.value} jam ${minutes.value} menit lagi.`;
});
</script>

<template>
    <div>
        <p class="sr-only" aria-live="off">{{ summary }}</p>

        <!-- Sedang berlangsung / sudah selesai: angka tidak lagi relevan. -->
        <div v-if="ongoing || finished" aria-hidden="true"
            class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold"
            :class="ongoing ? tone.badge : 'border-void-600 text-slate-400'">
            <span v-if="ongoing" class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-current opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-current"></span>
            </span>
            {{ ongoing ? 'Sedang Berlangsung' : 'Telah Terlaksana' }}
        </div>

        <div v-else aria-hidden="true" class="flex" :class="props.compact ? 'gap-1.5' : 'gap-2 sm:gap-3'">
            <div v-for="unit in units" :key="unit.key"
                class="holo-panel-lite flex flex-1 flex-col items-center rounded-2xl"
                :class="props.compact ? 'px-2 py-2' : 'px-3 py-3 sm:px-5 sm:py-4'">
                <span class="font-display font-extrabold tabular-nums text-slate-50"
                    :class="[props.compact ? 'text-lg' : 'text-2xl sm:text-4xl', tone.countdownGlow]">
                    {{ unit.value }}
                </span>
                <span class="mt-0.5 font-bold uppercase tracking-[0.16em] text-slate-400"
                    :class="props.compact ? 'text-[9px]' : 'text-[10px] sm:text-[11px]'">
                    {{ unit.label }}
                </span>
            </div>
        </div>
    </div>
</template>
