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

/**
 * Tinggi maksimum gulir, di-cache.
 *
 * `scrollHeight` dan `innerHeight` adalah pembacaan layout: memanggilnya di
 * tengah gulir memaksa browser menghitung ulang tata letak (forced synchronous
 * layout) — dulu terjadi di SETIAP frame. Nilainya hanya berubah saat ukuran
 * jendela atau tinggi dokumen berubah, jadi cukup dihitung di saat-saat itu.
 */
let maxScroll = 0;

const measureBounds = () => {
    maxScroll = document.documentElement.scrollHeight - window.innerHeight;
};

/** Perhitungan per frame — kini murni membaca `scrollY`, tanpa menyentuh layout. */
const measure = () => {
    scrolled.value = window.scrollY > 40;
    progress.value = maxScroll > 0 ? Math.min(1, window.scrollY / maxScroll) : 0;
};

/**
 * Scroll-spy lewat IntersectionObserver.
 *
 * Sebelumnya tiap frame menjalankan `document.querySelector` untuk tiap menu
 * lalu `getBoundingClientRect()` pada tiap section — tujuh pencarian DOM dan
 * tujuh pembacaan layout, 60 kali per detik selama pengguna menggulir. Kini
 * browser yang mengabarkan section mana yang memotong garis 35% viewport, dan
 * pekerjaan itu tidak lagi membebani main thread.
 */
let observer = null;
/** Urutan section mengikuti menu — dipakai memilih yang teratas bila beberapa terlihat. */
let observedSections = [];
const visibleSections = new Set();

const syncActiveHash = () => {
    const current = observedSections.find((section) => visibleSections.has(section.el));

    if (current) {
        activeHash.value = current.hash;
    }
};

const startSpy = () => {
    // Halaman dengan sorotan tetap (mis. /berita) tidak punya section — lewati.
    if (props.active) {
        return;
    }

    // Hanya target `#section` yang boleh masuk `querySelector`. Menu bisa
    // menunjuk ke alamat halaman (mis. `/datasiswa` milik Halaman Kustom), dan
    // itu bukan selektor CSS yang sah — melewatkannya ke sini membuat peramban
    // melempar SyntaxError dan seluruh scroll-spy navbar gagal dipasang.
    observedSections = props.links
        .filter((link) => typeof link.hash === 'string' && link.hash.startsWith('#'))
        .map((link) => ({ hash: link.hash, el: document.querySelector(link.hash) }))
        .filter((section) => section.el);

    if (!observedSections.length) {
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    visibleSections.add(entry.target);
                } else {
                    visibleSections.delete(entry.target);
                }
            }

            syncActiveHash();
        },
        // Viewport dipersempit jadi satu garis tipis di ketinggian 35%.
        // Section yang memotong garis itulah yang sedang dibaca pengunjung.
        { rootMargin: '-35% 0px -65% 0px' },
    );

    observedSections.forEach((section) => observer.observe(section.el));
};

/**
 * Event scroll bisa terpicu berkali-kali per frame (apalagi dengan Lenis),
 * jadi rAF menjamin maksimal satu kali per frame — sinkron dengan paint.
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
    measureBounds();
    measure();

    if (window.innerWidth >= 1024) {
        closeMenu();
    }
};

/** Tombol Escape menutup menu mobile. */
const onKeydown = (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    closeMenu();
};

/**
 * Tinggi dokumen masih tumbuh setelah mount (gambar lazy selesai dimuat,
 * carousel selesai menata slide). Pengamat ini menjaga `maxScroll` tetap benar
 * tanpa perlu mengukurnya lagi di tengah gulir.
 */
let bodyObserver = null;

onMounted(() => {
    measureBounds();
    measure();
    startSpy();

    bodyObserver = new ResizeObserver(measureBounds);
    bodyObserver.observe(document.body);

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    bodyObserver?.disconnect();
    visibleSections.clear();

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
                     inisial berbingkai neon (belah ketupat berputar saat hover).
                     Gambar unggahan tampil tanpa kotak/bingkai apa pun — hanya
                     logonya sendiri, dengan pendar neon mengikuti bentuk gambar
                     (`drop-shadow`, bukan `shadow`, supaya mengikuti area
                     transparan PNG dan bukan kotak pembungkusnya). -->
                <a href="#beranda" class="group relative flex min-w-0 items-center gap-2.5 sm:gap-3"
                    @click.prevent="goTo('#beranda')">
                    <span v-if="logo" class="relative flex h-10 w-10 shrink-0 items-center justify-center">
                        <img :src="logo" :alt="`Logo ${props.schoolName}`"
                            class="h-full w-full object-contain drop-shadow-[0_0_10px_rgba(52,226,245,0.45)] transition duration-500 group-hover:scale-105 group-hover:drop-shadow-[0_0_16px_rgba(52,226,245,0.75)]">
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
