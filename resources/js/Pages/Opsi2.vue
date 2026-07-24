<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import GlassNavbar from '../Components/Opsi2/GlassNavbar.vue';
import HeroCanvas from '../Components/Opsi2/HeroCanvas.vue';
import TiltCard from '../Components/Opsi2/TiltCard.vue';
import NewsCoverflow from '../Components/Opsi2/NewsCoverflow.vue';
import ActivityCards from '../Components/Opsi2/ActivityCards.vue';
import AchievementTimeline from '../Components/Opsi2/AchievementTimeline.vue';
import GlassFooter from '../Components/Opsi2/GlassFooter.vue';
import { getSmoothScroll, gsap, ScrollTrigger } from '../lib/smooth-scroll';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    stats: { type: Array, default: () => [] },
    pillars: { type: Array, default: () => [] },
    news: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    achievements: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

/** Daftar menu datang dari server (SiteInfo); fallback bila dipakai lepas. */
const navLinks = props.navLinks.length ? props.navLinks : [
    { label: 'Beranda', hash: '#beranda' },
    { label: 'Keunggulan', hash: '#keunggulan' },
    { label: 'Berita', hash: '#berita' },
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
    mint: { chip: 'bg-mint-500/12 text-mint-600', orb: 'bg-mint-300/50', glow: 'glow-mint' },
    gold: { chip: 'bg-gold-500/12 text-gold-600', orb: 'bg-gold-300/50', glow: 'glow-gold' },
    sky: { chip: 'bg-sky-soft-500/12 text-sky-soft-500', orb: 'bg-sky-soft-300/50', glow: 'glow-sky' },
    lilac: { chip: 'bg-lilac-500/12 text-lilac-500', orb: 'bg-lilac-300/50', glow: 'glow-lilac' },
};

const accentOf = (name) => pillarAccents[name] ?? pillarAccents.mint;

/** Kartu besar mengisi 3 dari 5 kolom — sumber ritme layout asimetris. */
const spanOf = (span) => (span === 'lg' ? 'md:col-span-3' : 'md:col-span-2');

const hero = ref(null);

let ctx = null;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    ctx = gsap.context(() => {
        // Animasi masuk hero: berurutan, saling menumpuk agar terasa mengalir.
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        tl.from('[data-hero-badge]', { y: 24, opacity: 0, duration: 0.8 })
            .from('[data-hero-line]', { yPercent: 110, opacity: 0, duration: 1.1, stagger: 0.12 }, '-=0.45')
            .from('[data-hero-text]', { y: 28, opacity: 0, duration: 0.9 }, '-=0.7')
            .from('[data-hero-cta] > *', { y: 24, opacity: 0, duration: 0.7, stagger: 0.12 }, '-=0.6')
            .from('[data-hero-stat]', { y: 34, opacity: 0, scale: 0.94, duration: 0.8, stagger: 0.1 }, '-=0.45')
            .from('[data-hero-float]', { scale: 0.7, opacity: 0, duration: 0.9, stagger: 0.15 }, '-=0.7');

        // Konten hero memudar & menyusut saat halaman digulir — kesan berlapis.
        gsap.to('[data-hero-content]', {
            y: -90,
            opacity: 0.15,
            ease: 'none',
            scrollTrigger: {
                trigger: hero.value,
                start: 'top top',
                end: 'bottom top',
                scrub: true,
            },
        });

        ScrollTrigger.refresh();
    });
});

onBeforeUnmount(() => ctx?.revert());
</script>

<template>
    <Head title="Beranda — Opsi 2" />

    <div class="aurora-bg min-h-screen overflow-x-clip font-sans text-sage-900">
        <GlassNavbar :school-name="props.schoolName" :links="navLinks" />

        <main class="relative">
            <!-- ============================ HERO ============================ -->
            <!-- `isolate` wajib: tanpa stacking context sendiri, kanvas -z-10
                 akan tertimpa latar aurora milik pembungkus halaman. -->
            <section id="beranda" ref="hero" class="relative isolate flex min-h-screen items-center overflow-hidden">
                <div class="absolute inset-0 -z-10">
                    <HeroCanvas />
                </div>

                <!-- Gradasi lembut agar teks tetap terbaca di atas kanvas 3D. -->
                <div
                    class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-b from-canvas-50/60 via-canvas-50/20 to-canvas-50">
                </div>

                <div class="container-page relative pb-24 pt-36 sm:pt-40">
                    <!-- Layout asimetris: teks 7 kolom, panel melayang 5 kolom. -->
                    <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-8">
                        <div data-hero-content class="lg:col-span-7">
                            <div data-hero-badge class="flex">
                                <span
                                    class="glass-panel inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-sage-700 sm:text-sm">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-mint-400 opacity-75"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-mint-500"></span>
                                    </span>
                                    Terakreditasi A · PPDB 2026/2027 Dibuka
                                </span>
                            </div>

                            <h1
                                class="mt-8 font-display text-[2.6rem] font-extrabold leading-[1.05] tracking-tight sm:text-6xl lg:text-[4.2rem]">
                                <!-- Tiap baris dibungkus agar bisa "terbit" dari bawah masker. -->
                                <span class="block overflow-hidden py-1">
                                    <span data-hero-line class="block text-sage-900">Generasi Qur'ani</span>
                                </span>
                                <span class="block overflow-hidden py-1">
                                    <span data-hero-line class="text-gradient-lumen block">Berpikir Masa Depan</span>
                                </span>
                                <span class="block overflow-hidden py-1">
                                    <span data-hero-line class="block text-sage-900">
                                        Berhati
                                        <span class="relative inline-block">
                                            <span class="relative z-10">Tenang</span>
                                            <span
                                                class="absolute inset-x-0 bottom-1 z-0 h-3 rounded-full bg-gold-300/60 blur-[2px]"></span>
                                        </span>
                                    </span>
                                </span>
                            </h1>

                            <p data-hero-text class="mt-7 max-w-xl text-base leading-relaxed text-sage-700/90 sm:text-lg">
                                Sekolah Islam terpadu jenjang SD &amp; SMP yang memadukan tahfidz terstruktur, sains
                                modern, dan pembinaan akhlak — dalam lingkungan belajar yang teduh, aman, dan
                                menyenangkan bagi ananda.
                            </p>

                            <div data-hero-cta class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                                <a href="#ppdb"
                                    class="group relative overflow-hidden rounded-full bg-linear-to-r from-mint-500 to-sage-600 px-8 py-4 text-center text-sm font-bold text-white shadow-xl shadow-mint-500/35 transition duration-300 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-mint-500/45"
                                    @click.prevent="scrollTo('#ppdb')">
                                    <span class="relative z-10">Daftar Sekarang</span>
                                    <span
                                        class="absolute inset-0 -translate-x-full bg-white/25 transition duration-500 group-hover:translate-x-0"></span>
                                </a>
                                <a href="#keunggulan"
                                    class="neo-surface rounded-full px-8 py-4 text-center text-sm font-bold text-sage-800 transition duration-300 hover:-translate-y-0.5 hover:text-mint-600"
                                    @click.prevent="scrollTo('#keunggulan')">
                                    Jelajahi Sekolah
                                </a>
                            </div>

                            <!-- Statistik singkat -->
                            <dl class="mt-14 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-4">
                                <div v-for="stat in props.stats" :key="stat.label" data-hero-stat
                                    class="glass-panel rounded-3xl px-4 py-4 transition duration-300 hover:-translate-y-1">
                                    <dt class="font-display text-2xl font-extrabold text-sage-800 sm:text-[1.7rem]">
                                        {{ stat.value }}
                                    </dt>
                                    <dd class="mt-1 text-xs font-semibold text-sage-600">{{ stat.label }}</dd>
                                    <dd class="text-[10px] font-medium uppercase tracking-wider text-mint-600">
                                        {{ stat.hint }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Panel & badge melayang — penyeimbang visual sisi kanan. -->
                        <div class="relative hidden h-[30rem] lg:col-span-5 lg:block">
                            <div data-hero-float
                                class="glass-panel float-slow absolute right-4 top-4 w-64 rounded-[1.75rem] p-5 glow-mint">
                                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-mint-600">
                                    Program Unggulan
                                </p>
                                <p class="mt-2 font-display text-lg font-bold text-sage-900">Tahfidz 10 Juz</p>
                                <p class="mt-2 text-xs leading-relaxed text-sage-700/80">
                                    Halaqah kelompok kecil dengan rasio 1 musyrif : 10 siswa.
                                </p>
                                <div class="neo-inset mt-4 h-2 overflow-hidden rounded-full">
                                    <div class="h-full w-4/5 rounded-full bg-linear-to-r from-mint-400 to-mint-600">
                                    </div>
                                </div>
                            </div>

                            <div data-hero-float
                                class="glass-panel float-slower absolute bottom-16 left-0 w-56 rounded-[1.75rem] p-5 glow-lilac">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-linear-to-br from-lilac-300 to-lilac-500 text-xl">
                                        🔬
                                    </span>
                                    <div>
                                        <p class="font-display text-sm font-bold text-sage-900">Kelas Robotik</p>
                                        <p class="text-[11px] text-sage-600">Finalis Nasional 2026</p>
                                    </div>
                                </div>
                            </div>

                            <div data-hero-float
                                class="glass-panel float-slow absolute bottom-0 right-10 w-52 rounded-[1.75rem] p-5 glow-gold">
                                <p class="font-display text-3xl font-extrabold text-gold-600">A</p>
                                <p class="mt-1 text-xs font-semibold text-sage-700">Akreditasi BAN-S/M</p>
                                <p class="mt-1 text-[11px] text-sage-500">Berlaku hingga 2029</p>
                            </div>

                            <!-- Cincin ornamen statis sebagai jangkar komposisi. -->
                            <div
                                class="pointer-events-none absolute left-1/2 top-1/2 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rotate-[22.5deg] rounded-[3rem] border border-mint-300/50">
                            </div>
                            <div
                                class="pointer-events-none absolute left-1/2 top-1/2 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-[3rem] border border-lilac-300/50">
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="pointer-events-none absolute bottom-8 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[10px] font-bold uppercase tracking-[0.3em] text-sage-500 lg:flex">
                    <span>Scroll</span>
                    <span class="h-12 w-px animate-pulse bg-linear-to-b from-mint-400 to-transparent"></span>
                </div>
            </section>

            <!-- ========================= KEUNGGULAN ========================= -->
            <section id="keunggulan" class="relative scroll-mt-28 py-24 sm:py-32">
                <div class="container-page">
                    <!-- Judul rata kiri + deskripsi rata kanan = ritme asimetris. -->
                    <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
                        <div class="lg:col-span-7">
                            <p v-reveal="{ from: 'fade' }"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-mint-600">
                                Keunggulan Kami
                            </p>
                            <h2 v-reveal="{ delay: 0.1 }"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-sage-900 sm:text-5xl">
                                Empat Pilar yang Menopang
                                <span class="text-gradient-lumen">Tumbuh Kembang Ananda</span>
                            </h2>
                        </div>
                        <p v-reveal="{ from: 'left', delay: 0.2 }"
                            class="text-base leading-relaxed text-sage-700/85 lg:col-span-5">
                            Setiap pilar dirancang agar ananda tumbuh seimbang — kuat ilmunya, lembut akhlaknya, dan
                            percaya diri menghadapi tantangan zaman.
                        </p>
                    </div>

                    <!-- Grid 5 kolom: kartu besar (3) + kecil (2) bergantian. -->
                    <div class="mt-16 grid gap-5 md:grid-cols-5">
                        <TiltCard v-for="(pillar, index) in props.pillars" :key="pillar.title"
                            v-reveal="{ from: index % 2 === 0 ? 'right' : 'left', delay: 0.08 * index }"
                            :class="spanOf(pillar.span)" :max="8">
                            <article
                                class="glass-panel relative flex h-full flex-col overflow-hidden rounded-[2rem] p-8 transition-shadow duration-500"
                                :class="accentOf(pillar.accent).glow">
                                <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full blur-3xl"
                                    :class="accentOf(pillar.accent).orb"></div>
                                <div class="pattern-lattice pointer-events-none absolute inset-0 opacity-40"></div>

                                <div class="tilt-layer relative">
                                    <span
                                        class="neo-surface flex h-16 w-16 items-center justify-center rounded-3xl text-3xl">
                                        {{ pillar.icon }}
                                    </span>
                                </div>

                                <h3 class="relative mt-6 font-display text-xl font-bold text-sage-900">
                                    {{ pillar.title }}
                                </h3>

                                <p class="relative mt-3 flex-1 text-sm leading-relaxed text-sage-700/85">
                                    {{ pillar.description }}
                                </p>

                                <ul class="relative mt-6 flex flex-wrap gap-2">
                                    <li v-for="point in pillar.points" :key="point"
                                        class="rounded-full px-3 py-1.5 text-xs font-bold"
                                        :class="accentOf(pillar.accent).chip">
                                        {{ point }}
                                    </li>
                                </ul>
                            </article>
                        </TiltCard>
                    </div>
                </div>
            </section>

            <!-- =========================== BERITA =========================== -->
            <section id="berita" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <!-- Ornamen berlapis paraks: bergerak lebih lambat dari konten. -->
                <div v-parallax="{ y: 140, speed: 1.2 }"
                    class="pointer-events-none absolute -left-32 top-10 h-80 w-80 rounded-full bg-mint-300/25 blur-3xl">
                </div>
                <div v-parallax="{ y: 120, speed: -0.8, rotate: 20 }"
                    class="pointer-events-none absolute -right-24 top-40 h-64 w-64 rotate-[22.5deg] rounded-[4rem] border border-lilac-300/40">
                </div>

                <div class="container-page relative">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-xl">
                            <p v-reveal="{ from: 'fade' }"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-mint-600">
                                Kabar Sekolah
                            </p>
                            <h2 v-reveal="{ delay: 0.1 }"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-sage-900 sm:text-5xl">
                                Berita Terbaru
                            </h2>
                            <p v-reveal="{ delay: 0.2 }" class="mt-4 text-base leading-relaxed text-sage-700/85">
                                Momen, capaian, dan pengumuman terkini dari lingkungan sekolah.
                            </p>
                        </div>

                        <Link v-reveal="{ from: 'left', delay: 0.2 }" href="/berita"
                            class="neo-surface inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-sage-700 transition duration-300 hover:-translate-y-0.5 hover:text-mint-600">
                            Lihat Semua Berita
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <div v-reveal="{ from: 'fade', delay: 0.25 }" class="mt-14">
                        <NewsCoverflow :items="props.news" />
                    </div>
                </div>
            </section>

            <!-- ========================== KEGIATAN ========================== -->
            <section id="kegiatan" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <div class="container-page">
                    <div class="grid items-center gap-14 lg:grid-cols-12">
                        <!-- Deskripsi kiri -->
                        <div class="lg:col-span-5">
                            <p v-reveal="{ from: 'fade' }"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-lilac-500">
                                Keseharian Siswa
                            </p>
                            <h2 v-reveal="{ delay: 0.1 }"
                                class="mt-4 font-display text-3xl font-extrabold leading-tight text-sage-900 sm:text-5xl">
                                Kegiatan yang
                                <span class="text-gradient-lumen">Menumbuhkan</span>
                            </h2>
                            <p v-reveal="{ delay: 0.2 }" class="mt-5 text-base leading-relaxed text-sage-700/85">
                                Dari halaqah tahfidz pagi hingga klub robotik sore hari — setiap kegiatan dirancang
                                menyeimbangkan ruhiyah, nalar, dan kebugaran ananda. Geser kartu untuk menjelajah.
                            </p>

                            <ul v-reveal="{ delay: 0.3, stagger: 0.1 }" class="mt-8 space-y-3">
                                <li v-for="activity in props.activities.slice(0, 3)" :key="activity.title"
                                    class="glass-panel flex items-center gap-3 rounded-2xl px-4 py-3">
                                    <span aria-hidden="true">{{ activity.icon }}</span>
                                    <span class="text-sm font-semibold text-sage-800">{{ activity.title }}</span>
                                    <span class="ml-auto text-[11px] font-medium text-sage-500">
                                        {{ activity.schedule }}
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <!-- Kartu 3D kanan -->
                        <div v-reveal="{ from: 'zoom', delay: 0.2 }"
                            class="flex justify-center lg:col-span-7 lg:justify-end">
                            <div v-parallax="{ y: 60, speed: 0.6 }" class="relative">
                                <div
                                    class="pointer-events-none absolute -inset-10 rounded-[3rem] bg-linear-to-br from-mint-200/50 via-lilac-200/40 to-gold-200/40 blur-3xl">
                                </div>
                                <ActivityCards :items="props.activities" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ========================== PRESTASI ========================== -->
            <section id="prestasi" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
                <div v-parallax="{ y: 100, speed: -1 }"
                    class="pointer-events-none absolute right-0 top-24 h-96 w-96 rounded-full bg-gold-300/20 blur-3xl">
                </div>

                <div class="container-page relative">
                    <div class="mx-auto max-w-2xl text-center">
                        <p v-reveal="{ from: 'fade' }"
                            class="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">
                            Pencapaian
                        </p>
                        <h2 v-reveal="{ delay: 0.1 }"
                            class="mt-4 font-display text-3xl font-extrabold leading-tight text-sage-900 sm:text-5xl">
                            Jejak Prestasi
                            <span class="text-gradient-lumen">Siswa SD &amp; SMP</span>
                        </h2>
                        <p v-reveal="{ delay: 0.2 }" class="mt-4 text-base leading-relaxed text-sage-700/85">
                            Buah dari proses belajar yang konsisten — akademik, olahraga, seni, hingga tahfidz
                            Al-Qur'an.
                        </p>
                    </div>

                    <div class="mt-20">
                        <AchievementTimeline :items="props.achievements" />
                    </div>
                </div>
            </section>

            <!-- ============================ PPDB ============================ -->
            <section id="ppdb" class="relative scroll-mt-28 pb-28">
                <div class="container-page">
                    <div v-reveal="{ from: 'zoom' }"
                        class="relative overflow-hidden rounded-[2.5rem] bg-linear-to-br from-sage-700 via-sage-800 to-sage-900 px-8 py-16 text-center sm:px-16">
                        <div class="pattern-lattice pointer-events-none absolute inset-0 opacity-10"></div>
                        <div
                            class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-mint-400/25 blur-3xl">
                        </div>
                        <div
                            class="pointer-events-none absolute -bottom-28 -right-20 h-80 w-80 rounded-full bg-lilac-400/20 blur-3xl">
                        </div>

                        <div class="relative">
                            <span
                                class="glass-panel-dark inline-block rounded-full px-5 py-2 text-xs font-bold text-mint-300">
                                PPDB Tahun Ajaran 2026/2027
                            </span>

                            <h2
                                class="mt-7 font-display text-3xl font-extrabold leading-tight text-cream-50 sm:text-5xl">
                                Mari Wujudkan Masa Depan
                                <span class="block text-mint-300">Terbaik untuk Ananda</span>
                            </h2>

                            <p class="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-cream-200/85 sm:text-base">
                                Pendaftaran gelombang II telah dibuka dengan kuota terbatas. Tersedia beasiswa prestasi
                                akademik dan tahfidz bagi calon siswa terpilih.
                            </p>

                            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <a href="https://wa.me/622287654321"
                                    class="w-full rounded-full bg-linear-to-r from-mint-400 to-mint-500 px-8 py-4 text-sm font-bold text-sage-900 shadow-xl shadow-black/20 transition duration-300 hover:-translate-y-0.5 sm:w-auto">
                                    Daftar via WhatsApp
                                </a>
                                <a href="#kontak"
                                    class="w-full rounded-full border border-cream-200/30 px-8 py-4 text-sm font-bold text-cream-50 transition duration-300 hover:-translate-y-0.5 hover:bg-white/10 sm:w-auto"
                                    @click.prevent="scrollTo('#kontak')">
                                    Konsultasi Dulu
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <GlassFooter :school-name="props.schoolName" :links="navLinks" :contacts="props.contacts"
            :socials="props.socials" />
    </div>
</template>
