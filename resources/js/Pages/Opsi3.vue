<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import NeonNavbar from '../Components/Opsi3/NeonNavbar.vue';
import HeroScene from '../Components/Opsi3/HeroScene.vue';
import HoloTilt from '../Components/Opsi3/HoloTilt.vue';
import NeonCoverflow from '../Components/Opsi3/NeonCoverflow.vue';
import EventCard from '../Components/Opsi3/EventCard.vue';
import EventCountdown from '../Components/Opsi3/EventCountdown.vue';
import ActivityDeck from '../Components/Opsi3/ActivityDeck.vue';
import NeonTimeline from '../Components/Opsi3/NeonTimeline.vue';
import GalleryCarousel from '../Components/Opsi3/GalleryCarousel.vue';
import GalleryLightbox from '../Components/Opsi3/GalleryLightbox.vue';
import NeonFooter from '../Components/Opsi3/NeonFooter.vue';
import { getSmoothScroll, gsap, ScrollTrigger } from '../lib/smooth-scroll';
import { newsAccent } from '../lib/news-accent';
import { useSlideshow } from '../lib/slideshow';
import { useTheme } from '../lib/theme';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    heroImage: { type: Object, default: () => ({}) },
    /** Foto hero yang bergantian; tiap slide membawa profil singkatnya. */
    heroSlides: { type: Array, default: () => [] },
    stats: { type: Array, default: () => [] },
    pillars: { type: Array, default: () => [] },
    news: { type: Array, default: () => [] },
    /** Agenda terdekat untuk section "Next Event". */
    events: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    achievements: { type: Array, default: () => [] },
    /** Foto galeri profil sekolah untuk carousel di bawah section Prestasi. */
    gallery: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

const { theme } = useTheme();

/**
 * Semua `v-reveal` di halaman ini memakai `once: false` supaya animasinya
 * terulang tiap kali section masuk viewport lagi — bukan hanya kunjungan
 * pertama. Helper ini menjaga opsi lain tetap ringkas ditulis.
 */
const replay = (options = {}) => ({ once: false, ...options });

/** Daftar menu datang dari server (SiteInfo); fallback bila halaman dipakai lepas. */
const navLinks = props.navLinks.length
    ? props.navLinks
    : [
        { label: 'Beranda', hash: '#beranda' },
        { label: 'Keunggulan', hash: '#keunggulan' },
        { label: 'Berita', hash: '#berita' },
        { label: 'Next Event', hash: '#event' },
        { label: 'Kegiatan', hash: '#kegiatan' },
        { label: 'Prestasi', hash: '#prestasi' },
        { label: 'Kontak', hash: '#kontak' },
    ];

const scrollTo = (hash) => {
    const target = document.querySelector(hash);

    if (!target) {
        return;
    }

    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset: -96, duration: 1.5 });
    } else {
        target.scrollIntoView({ behavior: 'smooth' });
    }
};

/** Kelas aksen per kartu keunggulan — dipetakan dari data controller. */
const pillarAccents = {
    mint: {
        chip: 'border-aqua-400/40 bg-aqua-400/12 text-aqua-200',
        orb: 'bg-aqua-500/40',
        glow: 'neon-aqua',
        glare: 'rgba(124, 243, 255, 0.3)',
        icon: 'from-aqua-400/30 to-aqua-600/10 text-aqua-200 shadow-[0_0_26px_-6px_rgba(52,226,245,0.9)]',
    },
    gold: {
        chip: 'border-solar-400/40 bg-solar-400/12 text-solar-300',
        orb: 'bg-solar-400/35',
        glow: 'neon-solar',
        glare: 'rgba(255, 199, 61, 0.28)',
        icon: 'from-solar-400/30 to-solar-500/10 text-solar-300 shadow-[0_0_26px_-6px_rgba(255,199,61,0.9)]',
    },
    sky: {
        chip: 'border-volt-400/40 bg-volt-400/12 text-volt-300',
        orb: 'bg-volt-500/35',
        glow: 'neon-volt',
        glare: 'rgba(169, 123, 255, 0.3)',
        icon: 'from-volt-400/30 to-volt-500/10 text-volt-300 shadow-[0_0_26px_-6px_rgba(169,123,255,0.9)]',
    },
    lilac: {
        chip: 'border-plasma-400/40 bg-plasma-400/12 text-plasma-300',
        orb: 'bg-plasma-500/35',
        glow: 'neon-plasma',
        glare: 'rgba(255, 94, 207, 0.3)',
        icon: 'from-plasma-400/30 to-plasma-500/10 text-plasma-300 shadow-[0_0_26px_-6px_rgba(255,94,207,0.9)]',
    },
};

const accentOf = (name) => pillarAccents[name] ?? pillarAccents.mint;

/**
 * Pembaca teks halaman: `text('hero_badge', 'cadangan')`.
 * Nilai kosong (field dikosongkan admin) tetap jatuh ke teks cadangan
 * supaya layout tidak pernah tampil bolong.
 */
const text = (key, fallback = '') => props.content?.[key] || fallback;

/**
 * Pembaca nilai apa adanya — menghormati string kosong (tidak jatuh ke
 * teks cadangan). Dipakai untuk elemen yang boleh dihapus admin, mis.
 * baris ketiga judul hero.
 */
const raw = (key) => (props.content?.[key] ?? '').trim();

/** Kartu besar mengisi 3 dari 5 kolom — sumber ritme layout asimetris. */
const spanOf = (span) => (span === 'lg' ? 'md:col-span-3' : 'md:col-span-2');

/**
 * Agenda terdekat tampil besar; tiga sisanya jadi kartu pendamping.
 * Sisanya lagi (bila ada) cukup dilihat di halaman /event.
 */
const featuredEvent = computed(() => props.events[0] ?? null);
const otherEvents = computed(() => props.events.slice(1, 4));
const featuredEventAccent = computed(() => newsAccent(featuredEvent.value?.accent));

const hero = ref(null);
const heroPhoto = ref(null);

/** Indeks foto galeri yang sedang dibuka di popup; null = tertutup. */
const lightboxIndex = ref(null);
const openLightbox = (index) => (lightboxIndex.value = index);

/**
 * Slide hero. Server sudah mengurus cadangan (foto tunggal lama dipakai bila
 * belum ada slide), tapi halaman tetap punya jaring pengaman sendiri supaya
 * bisa dipakai lepas dari controller.
 */
const heroSlides = computed(() => {
    if (props.heroSlides.length) {
        return props.heroSlides;
    }

    return props.heroImage.src ? [props.heroImage] : [];
});

const { active: activeSlide, goTo: goToSlide, pause: pauseSlides, resume: resumeSlides } =
    useSlideshow(() => heroSlides.value.length, { interval: 6500 });

/** Profil yang sedang tampil — dipakai kartu melayang & kredit foto. */
const currentSlide = computed(() => heroSlides.value[activeSlide.value] ?? {});

/** Kartu profil hanya muncul bila slide memang punya teks. */
const hasSlideProfile = computed(() => Boolean(currentSlide.value.title || currentSlide.value.description));

let ctx = null;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    ctx = gsap.context(() => {
        // Animasi masuk hero: berurutan, tiap baris judul berputar dari kedalaman.
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        tl.from('[data-hero-badge]', { y: 24, opacity: 0, duration: 0.8 })
            .from('[data-hero-line]', {
                yPercent: 120,
                rotateX: -70,
                opacity: 0,
                duration: 1.2,
                stagger: 0.13,
                transformOrigin: '50% 100% -60px',
            }, '-=0.45')
            .from('[data-hero-text]', { y: 28, opacity: 0, duration: 0.9 }, '-=0.75')
            .from('[data-hero-cta] > *', { y: 24, opacity: 0, duration: 0.7, stagger: 0.12 }, '-=0.6')
            .from('[data-hero-stat]', { y: 40, z: -260, opacity: 0, duration: 0.9, stagger: 0.1 }, '-=0.45')
            .from('[data-hero-profile]', { x: 60, opacity: 0, duration: 0.9 }, '-=0.8');

        // Foto latar ikut bergerak lebih lambat + membesar → paralaks berlapis.
        if (heroPhoto.value) {
            gsap.to(heroPhoto.value, {
                yPercent: 18,
                scale: 1.18,
                ease: 'none',
                scrollTrigger: {
                    trigger: hero.value,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });
        }

        // Konten hero memudar & terdorong menjauh saat halaman digulir.
        gsap.to('[data-hero-content], [data-hero-profile]', {
            y: -110,
            opacity: 0.1,
            ease: 'none',
            scrollTrigger: {
                trigger: hero.value,
                start: 'top top',
                end: 'bottom top',
                scrub: true,
            },
        });

        // Intro hero diputar ulang tiap kali pengunjung kembali ke atas,
        // jadi animasinya tidak cuma terlihat pada muat pertama.
        ScrollTrigger.create({
            trigger: hero.value,
            start: 'top top',
            end: 'bottom 35%',
            onEnterBack: () => tl.restart(),
        });

        ScrollTrigger.refresh();
    });
});

onBeforeUnmount(() => ctx?.revert());
</script>

<template>
    <Head title="Beranda — Opsi 3" />

    <!-- `data-theme` inilah sakelar visual halaman: CSS menimpa variabel warna
         berdasarkan nilainya, jadi seluruh utilitas Tailwind ikut berganti. -->
    <div :data-theme="theme" class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200">
        <NeonNavbar :school-name="props.schoolName" :links="navLinks" :content="props.content" />

        <main class="relative z-10">
            <!-- ============================ HERO ============================ -->
            <!-- `isolate` wajib: tanpa stacking context sendiri, lapisan foto &
                 kanvas -z-* akan tertimpa nebula milik pembungkus halaman. -->
            <section id="beranda" ref="hero" class="relative isolate flex min-h-screen items-center overflow-hidden">
                <!-- Lapis 1 — foto sekolah yang bergantian (crossfade).
                     Semua slide ditumpuk; hanya yang aktif yang opasitasnya 1,
                     jadi pergantiannya benar-benar saling melebur, bukan
                     hilang-lalu-muncul. Paralaks dipasang di pembungkusnya
                     supaya seluruh tumpukan bergerak bersama. -->
                <div class="absolute inset-0 -z-30 overflow-hidden">
                    <div ref="heroPhoto" class="relative h-full w-full scale-105">
                        <img v-for="(slide, index) in heroSlides" :key="slide.src" :src="slide.src" :alt="slide.alt"
                            :fetchpriority="index === 0 ? 'high' : 'auto'" :loading="index === 0 ? 'eager' : 'lazy'"
                            :aria-hidden="index === activeSlide ? 'false' : 'true'"
                            class="hero-photo hero-slide absolute inset-0 h-full w-full object-cover object-center"
                            :class="index === activeSlide ? 'opacity-100' : 'opacity-0'">
                    </div>
                </div>

                <!-- Lapis 2 — overlay gradasi futuristik agar foto menyatu tema.
                     Kepekatannya sengaja rendah supaya gedung sekolah tetap jelas. -->
                <div class="pointer-events-none absolute inset-0 -z-20">
                    <div class="hero-veil absolute inset-0"></div>
                    <!-- Tekstur kisi & garis pindai -->
                    <div class="cyber-grid absolute inset-0 opacity-40"></div>
                    <div class="scanlines absolute inset-0 opacity-60"></div>
                </div>

                <!-- Lapis 3 — scene 3D transparan di atas foto. -->
                <div class="pointer-events-none absolute inset-0 -z-10">
                    <!-- `key` memaksa scene dibangun ulang saat tema berganti:
                         warna kisi masuk lewat args GridHelper yang tidak reaktif. -->
                    <HeroScene :key="theme" :theme="theme" />
                </div>

                <div class="container-page stage-3d relative pb-24 pt-36 sm:pt-40">
                    <!-- Layout asimetris: teks 7 kolom, panel melayang 5 kolom. -->
                    <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-8">
                        <div data-hero-content class="lg:col-span-7">
                            <div data-hero-badge class="flex">
                                <span
                                    class="holo-panel-lite inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-aqua-200 sm:text-sm">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-aqua-400 opacity-75"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-aqua-300"></span>
                                    </span>
                                    {{ text('hero_badge', 'Terakreditasi A · PPDB 2026/2027 Dibuka') }}
                                </span>
                            </div>

                            <h1
                                class="mt-8 font-display text-[2.6rem] font-extrabold leading-[1.05] tracking-tight sm:text-6xl lg:text-[4.2rem]"
                                style="perspective: 900px">
                                <!-- Tiap baris dibungkus agar bisa "terbit" dari bawah masker. -->
                                <span class="block overflow-hidden py-1">
                                    <span data-hero-line class="block text-slate-50 text-glow-aqua">
                                        {{ text('hero_title_1', "Generasi Qur'ani") }}
                                    </span>
                                </span>
                                <span class="block overflow-hidden py-1">
                                    <span data-hero-line class="text-gradient-neon block">
                                        {{ text('hero_title_2', 'Berpikir Masa Depan') }}
                                    </span>
                                </span>
                                <!-- Baris ketiga boleh dikosongkan admin: bila teks & kata sorot
                                     sama-sama kosong, seluruh baris tidak dirender. -->
                                <span v-if="raw('hero_title_3') || raw('hero_title_highlight')"
                                    class="block overflow-hidden py-1">
                                    <span data-hero-line class="block text-slate-50">
                                        {{ raw('hero_title_3') }}
                                        <span v-if="raw('hero_title_highlight')" class="relative inline-block">
                                            <span class="relative z-10">{{ raw('hero_title_highlight') }}</span>
                                            <span
                                                class="absolute inset-x-0 bottom-1 z-0 h-3 rounded-full bg-plasma-400/70 blur-[3px]"></span>
                                        </span>
                                    </span>
                                </span>
                            </h1>

                            <p data-hero-text class="mt-7 max-w-xl text-base leading-relaxed text-slate-300/90 sm:text-lg">
                                {{ text('hero_description', 'Sekolah Islam terpadu jenjang SD & SMP yang memadukan tahfidz terstruktur, sains modern, dan pembinaan akhlak.') }}
                            </p>

                            <div data-hero-cta class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                                <a href="#ppdb"
                                    class="group relative overflow-hidden rounded-full bg-aqua-500 bg-linear-to-r from-aqua-400 via-aqua-500 to-volt-400 px-8 py-4 text-center text-sm font-bold text-void-950 shadow-[0_0_34px_-6px_rgba(52,226,245,0.9)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_48px_-4px_rgba(169,123,255,0.95)]"
                                    @click.prevent="scrollTo('#ppdb')">
                                    <span class="relative z-10">{{ text('hero_cta_primary', 'Daftar Sekarang') }}</span>
                                    <span
                                        class="absolute inset-0 -translate-x-full bg-white/40 transition duration-500 group-hover:translate-x-0"></span>
                                </a>
                                <a href="#keunggulan"
                                    class="holo-panel-lite rounded-full px-8 py-4 text-center text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200"
                                    @click.prevent="scrollTo('#keunggulan')">
                                    {{ text('hero_cta_secondary', 'Jelajahi Sekolah') }}
                                </a>
                            </div>

                            <!-- Statistik singkat -->
                            <dl class="mt-14 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-4">
                                <div v-for="stat in props.stats" :key="stat.label" data-hero-stat
                                    class="holo-panel-lite hud-corner rounded-3xl px-4 py-4 transition duration-300 hover:-translate-y-1 hover:border-aqua-400/50">
                                    <dt class="font-display text-2xl font-extrabold text-slate-50 sm:text-[1.7rem]">
                                        {{ stat.value }}
                                    </dt>
                                    <dd class="mt-1 text-xs font-semibold text-slate-300">{{ stat.label }}</dd>
                                    <dd class="text-[10px] font-medium uppercase tracking-wider text-aqua-300">
                                        {{ stat.hint }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Kolom kanan — profil foto yang sedang tampil.
                             Ikut berganti bersama fotonya, dengan fade yang
                             sama supaya keduanya terbaca sebagai satu unit. -->
                        <div v-if="hasSlideProfile" data-hero-profile class="lg:col-span-5"
                            @mouseenter="pauseSlides" @mouseleave="resumeSlides" @focusin="pauseSlides"
                            @focusout="resumeSlides">
                            <div class="holo-panel hud-corner relative overflow-hidden rounded-[2rem] p-7 lg:ml-auto lg:max-w-sm">
                                <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-aqua-500/25 blur-3xl"></div>
                                <div class="scanlines pointer-events-none absolute inset-0 opacity-40"></div>

                                <!-- `mode="out-in"` menahan teks lama sampai benar-benar
                                     pudar, jadi tidak ada dua profil yang bertumpuk. -->
                                <Transition name="hero-fade" mode="out-in">
                                    <div :key="activeSlide" class="relative" aria-live="polite">
                                        <p v-if="currentSlide.eyebrow"
                                            class="text-[11px] font-bold uppercase tracking-[0.24em] text-aqua-300">
                                            {{ currentSlide.eyebrow }}
                                        </p>
                                        <h2 v-if="currentSlide.title"
                                            class="mt-3 font-display text-xl font-bold leading-snug text-slate-50 sm:text-2xl">
                                            {{ currentSlide.title }}
                                        </h2>
                                        <p v-if="currentSlide.description"
                                            class="mt-3 text-sm leading-relaxed text-slate-300/85">
                                            {{ currentSlide.description }}
                                        </p>
                                    </div>
                                </Transition>

                                <!-- Indikator sekaligus navigasi manual. -->
                                <div v-if="heroSlides.length > 1" class="relative mt-7 flex items-center gap-2.5">
                                    <button v-for="(slide, index) in heroSlides" :key="slide.src" type="button"
                                        class="h-1.5 rounded-full transition-all duration-500 hover:bg-aqua-300"
                                        :class="index === activeSlide
                                            ? 'w-8 bg-aqua-400 shadow-[0_0_14px_-2px_rgba(52,226,245,0.9)]'
                                            : 'w-3 bg-white/25'"
                                        :aria-label="`Tampilkan foto ${index + 1}: ${slide.title || slide.alt || ''}`"
                                        :aria-current="index === activeSlide" @click="goToSlide(index)"></button>
                                    <span class="ml-auto text-[11px] font-semibold tabular-nums text-slate-400">
                                        {{ String(activeSlide + 1).padStart(2, '0') }} / {{ String(heroSlides.length).padStart(2, '0') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kredit foto placeholder — hapus bila memakai foto sendiri. -->
                <p v-if="currentSlide.credit"
                    class="pointer-events-none absolute bottom-4 right-5 text-[10px] uppercase tracking-[0.2em] text-slate-500">
                    {{ currentSlide.credit }}
                </p>

                <div
                    class="pointer-events-none absolute bottom-8 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[10px] font-bold uppercase tracking-[0.3em] text-aqua-300/70 lg:flex">
                    <span>Scroll</span>
                    <span class="h-12 w-px animate-pulse bg-linear-to-b from-aqua-400 to-transparent"></span>
                </div>
            </section>

            <!-- ========================= KEUNGGULAN ========================= -->
            <section id="keunggulan" class="relative scroll-mt-28 py-24 sm:py-32">
                <div class="container-page">
                    <!-- Judul rata kiri + deskripsi rata kanan = ritme asimetris. -->
                    <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
                        <div class="lg:col-span-7">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">
                                {{ text('pillars_eyebrow', 'Keunggulan Kami') }}
                            </p>
                            <h2 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                                {{ text('pillars_title', 'Empat Pilar yang Menopang') }}
                                <span class="text-gradient-neon">
                                    {{ text('pillars_title_highlight', 'Tumbuh Kembang Ananda') }}
                                </span>
                            </h2>
                        </div>
                        <p v-reveal="replay({ from: 'left', delay: 0.2 })"
                            class="text-base leading-relaxed text-slate-300/80 lg:col-span-5">
                            {{ text('pillars_description', 'Setiap pilar dirancang agar ananda tumbuh seimbang.') }}
                        </p>
                    </div>

                    <!-- Grid 5 kolom: kartu besar (3) + kecil (2) bergantian. -->
                    <div class="mt-16 grid gap-5 md:grid-cols-5">
                        <HoloTilt v-for="(pillar, index) in props.pillars" :key="pillar.title"
                            v-reveal="replay({ from: index % 2 === 0 ? 'right' : 'left', delay: 0.08 * index })"
                            :class="spanOf(pillar.span)" :max="9" radius="2rem"
                            :glare-color="accentOf(pillar.accent).glare">
                            <article
                                class="holo-panel holo-edge relative flex h-full flex-col overflow-hidden rounded-[2rem] p-8 transition-shadow duration-500"
                                :class="accentOf(pillar.accent).glow">
                                <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full blur-3xl"
                                    :class="accentOf(pillar.accent).orb"></div>
                                <div class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-15"></div>
                                <div class="scanlines pointer-events-none absolute inset-0 opacity-40"></div>

                                <div class="depth-3 relative">
                                    <span
                                        class="flex h-16 w-16 items-center justify-center rounded-3xl border border-white/10 bg-linear-to-br text-3xl"
                                        :class="accentOf(pillar.accent).icon">
                                        {{ pillar.icon }}
                                    </span>
                                </div>

                                <h3 class="depth-1 relative mt-6 font-display text-xl font-bold text-slate-50">
                                    {{ pillar.title }}
                                </h3>

                                <p class="relative mt-3 flex-1 text-sm leading-relaxed text-slate-300/80">
                                    {{ pillar.description }}
                                </p>

                                <ul class="depth-1 relative mt-6 flex flex-wrap gap-2">
                                    <li v-for="point in pillar.points" :key="point"
                                        class="rounded-full border px-3 py-1.5 text-xs font-bold"
                                        :class="accentOf(pillar.accent).chip">
                                        {{ point }}
                                    </li>
                                </ul>
                            </article>
                        </HoloTilt>
                    </div>
                </div>
            </section>

            <!-- =========================== BERITA =========================== -->
            <section id="berita" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <!-- Ornamen berlapis paraks: bergerak lebih lambat dari konten. -->
                <div v-parallax="{ y: 140, speed: 1.2 }"
                    class="pointer-events-none absolute -left-32 top-10 h-80 w-80 rounded-full bg-aqua-500/20 blur-3xl">
                </div>
                <div v-parallax="{ y: 120, speed: -0.8, rotate: 20 }"
                    class="pointer-events-none absolute -right-24 top-40 h-64 w-64 rotate-[22.5deg] rounded-[4rem] border border-plasma-400/30">
                </div>

                <div class="container-page relative">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-xl">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">
                                {{ text('news_eyebrow', 'Kabar Sekolah') }}
                            </p>
                            <h2 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                                {{ text('news_title', 'Berita Terbaru') }}
                            </h2>
                            <p v-reveal="replay({ delay: 0.2 })" class="mt-4 text-base leading-relaxed text-slate-300/80">
                                {{ text('news_description', 'Momen, capaian, dan pengumuman terkini dari lingkungan sekolah.') }}
                            </p>
                        </div>

                        <Link v-reveal="replay({ from: 'left', delay: 0.2 })" href="/berita"
                            class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200">
                            {{ text('news_cta', 'Lihat Semua Berita') }}
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <div v-reveal="replay({ from: 'fade', delay: 0.25 })" class="mt-14">
                        <NeonCoverflow :items="props.news" />
                    </div>
                </div>
            </section>

            <!-- ========================= NEXT EVENT ========================= -->
            <section id="event" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <div v-parallax="{ y: 120, speed: -1 }"
                    class="pointer-events-none absolute -right-32 top-16 h-80 w-80 rounded-full bg-volt-500/20 blur-3xl">
                </div>
                <div v-parallax="{ y: 90, speed: 0.8, rotate: -18 }"
                    class="pointer-events-none absolute -left-20 bottom-24 h-56 w-56 rotate-[22.5deg] rounded-[4rem] border border-aqua-400/25">
                </div>

                <div class="container-page relative">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-xl">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-volt-300">
                                {{ text('events_eyebrow', 'Agenda Terdekat') }}
                            </p>
                            <h2 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                                {{ text('events_title', 'Next Event') }}
                                <span class="text-gradient-neon">
                                    {{ text('events_title_highlight', 'Sekolah') }}
                                </span>
                            </h2>
                            <p v-reveal="replay({ delay: 0.2 })" class="mt-4 text-base leading-relaxed text-slate-300/80">
                                {{ text('events_description', 'Kegiatan yang akan segera dilaksanakan. Catat tanggalnya dan bergabunglah bersama kami.') }}
                            </p>
                        </div>

                        <Link v-reveal="replay({ from: 'left', delay: 0.2 })" href="/event"
                            class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-volt-200">
                            {{ text('events_cta', 'Lihat Semua Next Event') }}
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <template v-if="featuredEvent">
                        <!-- Acara terdekat: panel besar dengan hitung mundur utama. -->
                        <div v-reveal="replay({ from: 'zoom', delay: 0.25 })" class="mt-14">
                            <Link :href="featuredEvent.href" class="group block">
                                <article
                                    class="holo-panel holo-edge relative grid overflow-hidden rounded-[2.5rem] lg:grid-cols-2"
                                    :class="featuredEventAccent.glow">
                                    <div class="relative h-56 overflow-hidden bg-linear-to-br lg:h-full lg:min-h-[24rem]"
                                        :class="featuredEventAccent.media">
                                        <img v-if="featuredEvent.image" :src="featuredEvent.image"
                                            :alt="featuredEvent.title" loading="lazy"
                                            class="h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-105 group-hover:opacity-100">
                                        <div v-else class="flex h-full w-full items-center justify-center text-6xl">
                                            {{ featuredEvent.icon }}
                                        </div>
                                        <div class="pattern-lattice-neon absolute inset-0 opacity-20"></div>
                                        <div class="scanlines absolute inset-0 opacity-50"></div>
                                    </div>

                                    <div class="relative flex flex-col justify-center p-8 sm:p-10">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="rounded-full border px-3 py-1 text-[11px] font-bold"
                                                :class="featuredEventAccent.badge">
                                                {{ featuredEvent.category }}
                                            </span>
                                            <span
                                                class="rounded-full border border-void-600 px-3 py-1 text-[11px] font-bold text-slate-300">
                                                Paling Dekat
                                            </span>
                                        </div>

                                        <h3
                                            class="mt-5 font-display text-2xl font-extrabold leading-snug text-slate-50 sm:text-3xl">
                                            {{ featuredEvent.title }}
                                        </h3>

                                        <dl class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-300/85">
                                            <div class="flex items-center gap-2">
                                                <dt class="sr-only">Jadwal</dt>
                                                <dd>
                                                    <span aria-hidden="true">🗓️</span>
                                                    <time :datetime="featuredEvent.startsAt" class="ml-1.5">
                                                        {{ featuredEvent.date }}
                                                    </time>
                                                </dd>
                                            </div>
                                            <div v-if="featuredEvent.time" class="flex items-center gap-2">
                                                <dt class="sr-only">Waktu</dt>
                                                <dd><span aria-hidden="true">⏱</span> {{ featuredEvent.time }}</dd>
                                            </div>
                                            <div v-if="featuredEvent.location" class="flex items-center gap-2">
                                                <dt class="sr-only">Lokasi</dt>
                                                <dd><span aria-hidden="true">📍</span> {{ featuredEvent.location }}</dd>
                                            </div>
                                        </dl>

                                        <p v-if="featuredEvent.excerpt"
                                            class="mt-4 text-sm leading-relaxed text-slate-300/75 sm:text-base">
                                            {{ featuredEvent.excerpt }}
                                        </p>

                                        <EventCountdown class="mt-7" :starts-at="featuredEvent.startsAt"
                                            :ends-at="featuredEvent.endsAt" :accent="featuredEvent.accent" />

                                        <span
                                            class="mt-7 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                                            :class="featuredEventAccent.link">
                                            Lihat Detail Acara
                                            <span aria-hidden="true">&rarr;</span>
                                        </span>
                                    </div>
                                </article>
                            </Link>
                        </div>

                        <!-- Agenda berikutnya -->
                        <div v-if="otherEvents.length" class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <EventCard v-for="(event, index) in otherEvents" :key="event.slug"
                                v-reveal="replay({ from: 'up', delay: 0.08 * index })" :item="event" />
                        </div>
                    </template>

                    <!-- Belum ada agenda: tetap beri kabar, jangan biarkan kosong. -->
                    <div v-else v-reveal="replay({ from: 'fade', delay: 0.25 })"
                        class="holo-panel mt-14 rounded-[2rem] px-8 py-16 text-center">
                        <p class="text-4xl" aria-hidden="true">📅</p>
                        <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                            {{ text('events_empty', 'Belum ada agenda yang dijadwalkan. Pantau terus halaman ini untuk kegiatan berikutnya.') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ========================== KEGIATAN ========================== -->
            <section id="kegiatan" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <div class="container-page">
                    <div class="grid items-center gap-14 lg:grid-cols-12">
                        <!-- Deskripsi kiri -->
                        <div class="lg:col-span-5">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-plasma-300">
                                {{ text('activities_eyebrow', 'Keseharian Siswa') }}
                            </p>
                            <h2 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                                {{ text('activities_title', 'Kegiatan yang') }}
                                <span class="text-gradient-neon">
                                    {{ text('activities_title_highlight', 'Menumbuhkan') }}
                                </span>
                            </h2>
                            <p v-reveal="replay({ delay: 0.2 })" class="mt-5 text-base leading-relaxed text-slate-300/80">
                                {{ text('activities_description', 'Setiap kegiatan dirancang menyeimbangkan ruhiyah, nalar, dan kebugaran ananda.') }}
                            </p>

                            <ul v-reveal="replay({ delay: 0.3, stagger: 0.1 })" class="mt-8 space-y-3">
                                <li v-for="activity in props.activities.slice(0, 3)" :key="activity.title"
                                    class="holo-panel flex items-center gap-3 rounded-2xl px-4 py-3 transition duration-300 hover:translate-x-1 hover:border-aqua-400/40">
                                    <span aria-hidden="true">{{ activity.icon }}</span>
                                    <span class="text-sm font-semibold text-slate-100">{{ activity.title }}</span>
                                    <span class="ml-auto text-[11px] font-medium text-slate-400">
                                        {{ activity.schedule }}
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <!-- Kartu 3D kanan -->
                        <div v-reveal="replay({ from: 'zoom', delay: 0.2 })"
                            class="flex justify-center lg:col-span-7 lg:justify-end">
                            <div v-parallax="{ y: 60, speed: 0.6 }" class="relative">
                                <div
                                    class="pointer-events-none absolute -inset-10 rounded-[3rem] bg-linear-to-br from-aqua-500/30 via-volt-500/25 to-plasma-500/25 blur-3xl">
                                </div>
                                <ActivityDeck :items="props.activities" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ========================== PRESTASI ========================== -->
            <section id="prestasi" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <div v-parallax="{ y: 100, speed: -1 }"
                    class="pointer-events-none absolute right-0 top-24 h-96 w-96 rounded-full bg-solar-400/12 blur-3xl">
                </div>

                <div class="container-page relative">
                    <div class="mx-auto max-w-2xl text-center">
                        <p v-reveal="replay({ from: 'fade' })"
                            class="text-xs font-bold uppercase tracking-[0.24em] text-solar-300">
                            {{ text('achievements_eyebrow', 'Pencapaian') }}
                        </p>
                        <h2 v-reveal="replay({ delay: 0.1 })"
                            class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                            {{ text('achievements_title', 'Jejak Prestasi') }}
                            <span class="text-gradient-neon">
                                {{ text('achievements_title_highlight', 'Siswa SD & SMP') }}
                            </span>
                        </h2>
                        <p v-reveal="replay({ delay: 0.2 })" class="mt-4 text-base leading-relaxed text-slate-300/80">
                            {{ text('achievements_description', 'Buah dari proses belajar yang konsisten.') }}
                        </p>
                    </div>

                    <div class="mt-20">
                        <NeonTimeline :items="props.achievements" />
                    </div>
                </div>
            </section>

            <!-- =========================== GALERI =========================== -->
            <section v-if="props.gallery.length" id="galeri" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <div v-parallax="{ y: 90, speed: -0.9 }"
                    class="pointer-events-none absolute -left-24 top-24 h-80 w-80 rounded-full bg-plasma-500/15 blur-3xl">
                </div>

                <div class="container-page relative">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-xl">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">
                                {{ text('gallery_eyebrow', 'Galeri Sekolah') }}
                            </p>
                            <h2 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                                {{ text('gallery_title', 'Potret Keseharian') }}
                                <span class="text-gradient-neon">
                                    {{ text('gallery_title_highlight', 'Profil Sekolah') }}
                                </span>
                            </h2>
                            <p v-reveal="replay({ delay: 0.2 })" class="mt-4 text-base leading-relaxed text-slate-300/80">
                                {{ text('gallery_description', 'Sekilas suasana gedung, kegiatan, dan momen berharga di lingkungan sekolah. Klik foto untuk melihatnya lebih besar.') }}
                            </p>
                        </div>

                        <Link v-reveal="replay({ from: 'left', delay: 0.2 })" href="/galeri"
                            class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200">
                            {{ text('gallery_cta', 'Lihat Semua') }}
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <div v-reveal="replay({ from: 'fade', delay: 0.25 })" class="mt-14">
                        <GalleryCarousel :images="props.gallery" @open="openLightbox" />
                    </div>
                </div>
            </section>

            <!-- ============================ PPDB ============================ -->
            <section id="ppdb" class="relative scroll-mt-28 pb-28">
                <div class="container-page">
                    <div v-reveal="replay({ from: 'zoom' })"
                        class="scan-sweep relative overflow-hidden rounded-[2.5rem] border border-aqua-400/25 bg-linear-to-br from-void-800 via-void-900 to-void-950 px-8 py-16 text-center shadow-[0_0_80px_-30px_rgba(52,226,245,0.8)] sm:px-16">
                        <div class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10"></div>
                        <div class="cyber-grid pointer-events-none absolute inset-0 opacity-40"></div>
                        <div
                            class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-aqua-500/25 blur-3xl">
                        </div>
                        <div
                            class="pointer-events-none absolute -bottom-28 -right-20 h-80 w-80 rounded-full bg-plasma-500/20 blur-3xl">
                        </div>

                        <div class="relative">
                            <span
                                class="holo-panel-lite inline-block rounded-full px-5 py-2 text-xs font-bold text-aqua-200">
                                {{ text('ppdb_badge', 'PPDB Tahun Ajaran 2026/2027') }}
                            </span>

                            <h2
                                class="mt-7 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                                {{ text('ppdb_title', 'Mari Wujudkan Masa Depan') }}
                                <span class="block text-gradient-neon">
                                    {{ text('ppdb_title_highlight', 'Terbaik untuk Ananda') }}
                                </span>
                            </h2>

                            <p class="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-slate-300/80 sm:text-base">
                                {{ text('ppdb_description', 'Pendaftaran gelombang II telah dibuka dengan kuota terbatas.') }}
                            </p>

                            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <a :href="text('ppdb_primary_href', 'https://wa.me/622287654321')"
                                    class="w-full rounded-full bg-aqua-500 bg-linear-to-r from-aqua-400 to-volt-400 px-8 py-4 text-sm font-bold text-void-950 shadow-[0_0_34px_-6px_rgba(52,226,245,0.9)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_48px_-4px_rgba(169,123,255,0.95)] sm:w-auto">
                                    {{ text('ppdb_primary_label', 'Daftar via WhatsApp') }}
                                </a>
                                <a href="#kontak"
                                    class="w-full rounded-full border border-aqua-400/30 px-8 py-4 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:border-aqua-400/70 hover:bg-aqua-400/10 sm:w-auto"
                                    @click.prevent="scrollTo('#kontak')">
                                    {{ text('ppdb_secondary_label', 'Konsultasi Dulu') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <NeonFooter :school-name="props.schoolName" :links="navLinks" :contacts="props.contacts"
            :socials="props.socials" :content="props.content" />

        <!-- Popup foto galeri — dibagikan seluruh section yang menampilkan galeri. -->
        <GalleryLightbox v-model:index="lightboxIndex" :images="props.gallery" />
    </div>
</template>

<style scoped>
/* Crossfade foto hero: durasi panjang & ease lembut agar pergantiannya
   terasa mengalir, bukan berkedip. */
.hero-slide {
    transition: opacity 1.4s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: opacity;
}

/* Fade kartu profil — sedikit lebih cepat dari fotonya, dengan geser halus
   supaya teks baru terasa "masuk", bukan sekadar berganti. */
.hero-fade-enter-active,
.hero-fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.hero-fade-enter-from {
    opacity: 0;
    transform: translateY(12px);
}

.hero-fade-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}

@media (prefers-reduced-motion: reduce) {

    .hero-slide,
    .hero-fade-enter-active,
    .hero-fade-leave-active {
        transition-duration: 0.01ms;
    }

    .hero-fade-enter-from,
    .hero-fade-leave-to {
        transform: none;
    }
}
</style>
