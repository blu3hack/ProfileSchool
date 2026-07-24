<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { getSmoothScroll } from '../../lib/smooth-scroll';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    links: { type: Array, default: () => [] },
});

const scrolled = ref(false);
const menuOpen = ref(false);
const activeHash = ref(props.links[0]?.hash ?? '');

const onScroll = () => {
    scrolled.value = window.scrollY > 40;

    // Tandai section yang sedang berada di sepertiga atas viewport.
    const marker = window.innerHeight * 0.35;

    for (const link of props.links) {
        const section = document.querySelector(link.hash);

        if (!section) {
            continue;
        }

        const { top, bottom } = section.getBoundingClientRect();

        if (top <= marker && bottom > marker) {
            activeHash.value = link.hash;
            break;
        }
    }
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

/** Lenis memegang kendali scroll, jadi anchor bawaan browser dilewati. */
const goTo = (hash) => {
    menuOpen.value = false;

    const target = document.querySelector(hash);

    if (!target) {
        return;
    }

    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset: -96, duration: 1.4 });
    } else {
        target.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <header class="pointer-events-none fixed inset-x-0 top-0 z-50 px-4 pt-4 sm:pt-5">
        <div class="pointer-events-auto mx-auto max-w-6xl transition-all duration-500"
            :class="scrolled ? 'translate-y-0' : 'translate-y-1'">
            <nav class="glass-panel flex h-16 items-center justify-between gap-4 rounded-[1.75rem] px-4 transition-all duration-500 sm:px-5"
                :class="scrolled ? 'shadow-[0_20px_50px_-30px_rgba(36,57,49,0.7)]' : 'shadow-none'"
                aria-label="Navigasi utama">
                <!-- Logo -->
                <a href="#beranda" class="group flex shrink-0 items-center gap-3" @click.prevent="goTo('#beranda')">
                    <span
                        class="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-linear-to-br from-mint-400 to-sage-600 font-display text-base font-bold text-white shadow-lg shadow-mint-500/30 transition duration-500 group-hover:rotate-[22.5deg]">
                        A
                        <span
                            class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-white/40 transition duration-500 group-hover:-rotate-[22.5deg]"></span>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display text-sm font-bold text-sage-800 sm:text-base">
                            {{ props.schoolName }}
                        </span>
                        <span class="block text-[10px] font-medium tracking-[0.16em] text-mint-600 uppercase">
                            SD &amp; SMP Islam Terpadu
                        </span>
                    </span>
                </a>

                <!-- Menu desktop -->
                <ul class="hidden items-center gap-1 lg:flex">
                    <li v-for="link in props.links" :key="link.hash">
                        <a :href="link.hash"
                            class="relative rounded-full px-4 py-2 text-sm font-semibold transition duration-300"
                            :class="activeHash === link.hash
                                ? 'neo-inset text-sage-900'
                                : 'text-sage-600 hover:text-mint-600'"
                            @click.prevent="goTo(link.hash)">
                            {{ link.label }}
                        </a>
                    </li>
                </ul>

                <div class="flex items-center gap-2">
                    <a href="#ppdb"
                        class="hidden rounded-full bg-linear-to-r from-mint-500 to-sage-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-mint-500/35 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-mint-500/45 sm:inline-block"
                        @click.prevent="goTo('#ppdb')">
                        Daftar PPDB
                    </a>

                    <button type="button"
                        class="neo-surface flex h-10 w-10 items-center justify-center rounded-2xl text-sage-700 transition hover:text-mint-600 lg:hidden"
                        :aria-expanded="menuOpen" aria-controls="menu-opsi2" aria-label="Buka menu navigasi"
                        @click="menuOpen = !menuOpen">
                        <svg v-if="!menuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round">
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
            </nav>

            <!-- Menu mobile -->
            <Transition enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-y-3 opacity-0 scale-95"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="-translate-y-3 opacity-0 scale-95">
                <div v-show="menuOpen" id="menu-opsi2" class="glass-panel mt-3 rounded-3xl p-3 lg:hidden">
                    <ul class="flex flex-col gap-1">
                        <li v-for="link in props.links" :key="link.hash">
                            <a :href="link.hash"
                                class="block rounded-2xl px-4 py-3 text-sm font-semibold text-sage-700 transition hover:bg-white/70 hover:text-mint-600"
                                @click.prevent="goTo(link.hash)">
                                {{ link.label }}
                            </a>
                        </li>
                        <li>
                            <a href="#ppdb"
                                class="mt-1 block rounded-2xl bg-linear-to-r from-mint-500 to-sage-600 px-4 py-3 text-center text-sm font-semibold text-white"
                                @click.prevent="goTo('#ppdb')">
                                Daftar PPDB
                            </a>
                        </li>
                    </ul>
                </div>
            </Transition>
        </div>
    </header>
</template>
