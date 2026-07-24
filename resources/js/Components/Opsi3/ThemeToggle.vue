<script setup>
import { computed } from 'vue';

import { useTheme } from '../../lib/theme';

/**
 * Sakelar tema dark ⇄ light. Knop-nya bergeser dengan pendar neon,
 * dan kedua ikon (bulan/matahari) tetap terlihat sebagai penanda arah.
 */
const { theme, toggleTheme } = useTheme();

const isDark = computed(() => theme.value === 'dark');
</script>

<template>
    <button type="button" role="switch" :aria-checked="isDark"
        :aria-label="isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
        :title="isDark ? 'Mode terang' : 'Mode gelap'"
        class="group relative flex h-10 w-[4.25rem] shrink-0 items-center rounded-full border border-aqua-400/30 bg-void-800/70 px-1 transition duration-300 hover:border-aqua-400/70"
        @click="toggleTheme">
        <!-- Knop yang bergeser -->
        <span
            class="absolute z-10 flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br transition-all duration-500 ease-out"
            :class="isDark
                ? 'translate-x-0 from-aqua-400 to-volt-400 shadow-[0_0_18px_rgba(52,226,245,0.8)]'
                : 'translate-x-[1.75rem] from-solar-400 to-plasma-400 shadow-[0_0_18px_rgba(255,199,61,0.8)]'">
            <!-- Bulan -->
            <svg v-if="isDark" class="h-4 w-4 text-void-950" viewBox="0 0 24 24" fill="currentColor">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
            </svg>
            <!-- Matahari -->
            <svg v-else class="h-4 w-4 text-void-950" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="4" fill="currentColor" stroke="none" />
                <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
            </svg>
        </span>

        <!-- Ikon latar sebagai penanda sisi -->
        <span class="pointer-events-none flex w-full items-center justify-between px-2 text-slate-400">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
            </svg>
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <circle cx="12" cy="12" r="5" />
            </svg>
        </span>
    </button>
</template>
