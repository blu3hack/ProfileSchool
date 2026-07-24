<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import { goToSection } from '../../lib/navigate';
import ThemeToggle from './ThemeToggle.vue';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    links: { type: Array, default: () => [] },
    /** Teks & gambar situs; dipakai untuk logo dan sub-judul yang bisa diedit admin. */
    content: { type: Object, default: () => ({}) },
    /**
     * Kunci menu yang disorot. Diisi halaman yang tidak punya section
     * (mis. halaman berita) supaya scroll-spy tidak salah menyorot.
     */
    active: { type: String, default: '' },
});

/** Logo unggahan admin (URL siap pakai). Kosong → jatuh ke inisial otomatis. */
const logo = computed(() => props.content?.nav_logo || '');
const subtitle = computed(() => props.content?.school_subtitle || 'SD & SMP Islam Terpadu');
/** Tombol CTA "Daftar" — teks & target bisa diedit admin di Konten Halaman. */
const ctaLabel = computed(() => (props.content?.nav_cta_label ?? 'Daftar PPDB').trim());
const ctaHref = computed(() => (props.content?.nav_cta_href || '#ppdb').trim());
/** Target berupa #section digulir mulus; selain itu diperlakukan tautan biasa. */
const ctaIsSection = computed(() => ctaHref.value.startsWith('#'));
/** Inisial nama sekolah untuk placeholder saat logo belum diunggah. */
const initial = computed(() => (props.schoolName || 'A').trim().charAt(0).toUpperCase());

const scrolled = ref(false);
const menuOpen = ref(false);
const activeHash = ref(props.active || props.links[0]?.hash || '');
/** 0 … 1 — mengisi garis progres tipis di dasar navbar. */
const progress = ref(0);

/** Perhitungan sesungguhnya — hanya dijalankan sekali per frame. */
const measure = () => {
    scrolled.value = window.scrollY > 40;

    const max = document.documentElement.scrollHeight - window.innerHeight;
    progress.value = max > 0 ? Math.min(1, window.scrollY / max) : 0;

    // Halaman dengan sorotan tetap tidak perlu scroll-spy.
    if (props.active) {
        return;
    }

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

/**
 * Event scroll bisa terpicu berkali-kali per frame (apalagi dengan Lenis).
 * `measure()` membaca layout (scrollHeight, getBoundingClientRect) sehingga
 * memanggilnya tiap event memicu layout-thrash. rAF menjamin maksimal satu
 * kali per frame — sinkron dengan paint, tanpa membebani scroll.
 */
let scrollTick = false;

const onScroll = () => {
    if (scrollTick) {
        return;
    }

    scrollTick = true;
    requestAnimationFrame(() => {
        measure();
        scrollTick = false;
    });
};

const closeMenu = () => (menuOpen.value = false);

/** Tutup menu saat layar melebar ke ukuran desktop (menu inline sudah tampil). */
const onResize = () => {
    if (window.innerWidth >= 1024) {
        closeMenu();
    }
};

/** Tombol Escape menutup menu mobile. */
const onKeydown = (event) => {
    if (event.key === 'Escape') {
        closeMenu();
    }
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onResize);
    window.removeEventListener('keydown', onKeydown);
});

/**
 * Lenis memegang kendali scroll, jadi anchor bawaan browser dilewati.
 * Di halaman berita section-nya tidak ada — helper akan kembali ke landing.
 */
const goTo = (hash) => {
    menuOpen.value = false;
    goToSection(hash);
};

/** Klik tombol CTA: gulir bila target #section, atau biarkan tautan biasa jalan. */
const goToCta = (event) => {
    menuOpen.value = false;

    if (ctaIsSection.value) {
        event.preventDefault();
        goTo(ctaHref.value);
    }
};
</script>

<template>
    <header class="pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-4 sm:pt-5">
        <!-- Lapisan gelap di belakang menu mobile: sentuh di luar menu untuk menutup. -->
        <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0"
            leave-active-class="transition duration-200" leave-to-class="opacity-0">
            <div v-show="menuOpen" class="pointer-events-auto fixed inset-0 bg-void-950/60 backdrop-blur-sm lg:hidden"
                aria-hidden="true" @click="closeMenu"></div>
        </Transition>

        <div class="pointer-events-auto relative mx-auto max-w-6xl transition-all duration-500"
            :class="scrolled ? 'translate-y-0' : 'translate-y-1'">
            <nav class="holo-panel relative flex h-16 items-center justify-between gap-4 overflow-hidden rounded-[1.75rem] px-4 transition-all duration-500 sm:px-5"
                :class="scrolled ? 'neon-aqua' : 'shadow-none'" aria-label="Navigasi utama">
                <div class="scanlines pointer-events-none absolute inset-0 opacity-40"></div>

                <!-- Logo: gambar unggahan admin bila ada, kalau tidak jatuh ke
                     inisial berbingkai neon (belah ketupat berputar saat hover). -->
                <a href="#beranda" class="group relative flex min-w-0 items-center gap-2.5 sm:gap-3"
                    @click.prevent="goTo('#beranda')">
                    <span v-if="logo"
                        class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-void-800/50 ring-1 ring-aqua-400/30 shadow-[0_0_22px_rgba(52,226,245,0.4)] transition duration-500 group-hover:ring-aqua-400/60">
                        <img :src="logo" :alt="`Logo ${props.schoolName}`" class="h-full w-full object-contain">
                    </span>
                    <span v-else
                        class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-aqua-400 via-volt-400 to-plasma-400 font-display text-base font-bold text-void-950 shadow-[0_0_22px_rgba(52,226,245,0.55)] transition duration-500 group-hover:rotate-[22.5deg]">
                        {{ initial }}
                        <span
                            class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-white/50 transition duration-500 group-hover:-rotate-[22.5deg]"></span>
                    </span>
                    <span class="min-w-0 leading-tight">
                        <span class="block truncate font-display text-sm font-bold text-aqua-200 sm:text-base">
                            {{ props.schoolName }}
                        </span>
                        <span
                            class="block truncate text-[10px] font-medium uppercase tracking-[0.18em] text-aqua-400/80">
                            {{ subtitle }}
                        </span>
                    </span>
                </a>

                <!-- Menu desktop -->
                <ul class="relative hidden shrink-0 items-center gap-0.5 lg:flex xl:gap-1">
                    <li v-for="link in props.links" :key="link.hash">
                        <a :href="link.hash"
                            class="relative block rounded-full px-3 py-2 text-sm font-semibold transition duration-300 xl:px-4"
                            :class="activeHash === link.hash
                                ? 'text-aqua-200'
                                : 'text-slate-300/70 hover:text-aqua-300'"
                            @click.prevent="goTo(link.hash)">
                            <span
                                v-if="activeHash === link.hash"
                                class="absolute inset-0 -z-10 rounded-full border border-aqua-400/40 bg-aqua-400/10 shadow-[0_0_18px_rgba(52,226,245,0.35)_inset]"></span>
                            {{ link.label }}
                        </a>
                    </li>
                </ul>

                <div class="relative flex shrink-0 items-center gap-2">
                    <ThemeToggle />

                    <a v-if="ctaLabel" :href="ctaHref"
                        :target="ctaIsSection ? null : '_blank'"
                        :rel="ctaIsSection ? null : 'noopener'"
                        class="group relative hidden overflow-hidden rounded-full bg-aqua-500 bg-linear-to-r from-aqua-400 to-volt-400 px-5 py-2.5 text-sm font-bold text-void-950 shadow-[0_0_24px_rgba(52,226,245,0.45)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_36px_rgba(169,123,255,0.6)] sm:inline-block"
                        @click="goToCta">
                        <span class="relative z-10">{{ ctaLabel }}</span>
                        <span
                            class="absolute inset-0 -translate-x-full bg-white/30 transition duration-500 group-hover:translate-x-0"></span>
                    </a>

                    <button type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl border border-aqua-400/30 bg-void-800/60 text-aqua-300 transition hover:border-aqua-400/70 hover:text-aqua-200 lg:hidden"
                        :aria-expanded="menuOpen" aria-controls="menu-opsi3" aria-label="Buka menu navigasi"
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

                <!-- Garis progres baca di dasar navbar. -->
                <span class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-aqua-400 via-volt-400 to-plasma-400 transition-transform duration-150"
                    :style="{ transform: `scaleX(${progress})`, transformOrigin: 'left center' }"></span>
            </nav>

            <!-- Menu mobile -->
            <Transition enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-y-3 opacity-0 scale-95"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="-translate-y-3 opacity-0 scale-95">
                <div v-show="menuOpen" id="menu-opsi3"
                    class="holo-panel mt-3 max-h-[calc(100dvh-5.5rem)] overflow-y-auto overscroll-contain rounded-3xl p-3 lg:hidden">
                    <ul class="flex flex-col gap-1">
                        <li v-for="link in props.links" :key="link.hash">
                            <a :href="link.hash"
                                class="block rounded-2xl px-4 py-3 text-sm font-semibold text-slate-200 transition hover:bg-aqua-400/10 hover:text-aqua-200"
                                @click.prevent="goTo(link.hash)">
                                {{ link.label }}
                            </a>
                        </li>
                        <li v-if="ctaLabel">
                            <a :href="ctaHref"
                                :target="ctaIsSection ? null : '_blank'"
                                :rel="ctaIsSection ? null : 'noopener'"
                                class="mt-1 block rounded-2xl bg-aqua-500 bg-linear-to-r from-aqua-400 to-volt-400 px-4 py-3 text-center text-sm font-bold text-void-950"
                                @click="goToCta">
                                {{ ctaLabel }}
                            </a>
                        </li>
                    </ul>
                </div>
            </Transition>
        </div>
    </header>
</template>
