<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

import { usePageText } from "../../lib/page-text";
import { useSlideshow } from "../../lib/slideshow";
import {
    gsap,
    refreshScrollTriggers,
    ScrollTrigger,
} from "../../lib/smooth-scroll";

/**
 * Section hero beranda (#beranda) — foto sekolah yang bergantian, judul yang
 * "terbit" baris demi baris, dan dua tombol ajakan.
 *
 * Seluruh animasi masuk & paralaksnya tinggal di sini bersama elemen yang
 * dianimasikan, jadi halaman induk tidak perlu tahu apa pun soal GSAP.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    /** Foto hero tunggal versi lama — jadi cadangan bila belum ada slide. */
    heroImage: { type: Object, default: () => ({}) },
    /** Foto hero yang bergantian; tiap slide membawa profil singkatnya. */
    heroSlides: { type: Array, default: () => [] },
});

/**
 * Tombol CTA tidak menggulir sendiri: halaman yang memegang satu-satunya
 * `scrollTo`, supaya perilaku gulirnya seragam dengan section lain.
 */
const emit = defineEmits(["scroll"]);

const { text, raw } = usePageText(() => props.content);

const hero = ref(null);
const heroPhoto = ref(null);

/**
 * Slide hero. Server sudah mengurus cadangan (foto tunggal lama dipakai bila
 * belum ada slide), tapi section tetap punya jaring pengaman sendiri supaya
 * bisa dipakai lepas dari controller.
 */
const heroSlides = computed(() => {
    if (props.heroSlides.length) {
        return props.heroSlides;
    }

    return props.heroImage.src ? [props.heroImage] : [];
});

const { active: activeSlide } = useSlideshow(() => heroSlides.value.length, {
    interval: 6500,
});

/**
 * Indeks slide hero yang benar-benar dipasang di DOM.
 *
 * Sebelumnya SELURUH slide dirender sekaligus. Karena semuanya bertumpuk
 * `absolute inset-0` di dalam viewport — cuma dibedakan opacity — `loading="lazy"`
 * tidak menahan apa pun: bagi browser semuanya "terlihat", jadi keempatnya
 * diunduh dan didekode segera. Pada batas 1920px (App\Support\ImageOptimizer),
 * satu foto yang sudah didekode memakan 1920 × 1080 × 4 byte ≈ 8 MB memori
 * tekstur; empat slide ≈ 33 MB yang ditahan selamanya, plus empat dekode penuh
 * yang berebut main thread tepat saat halaman sedang hidrasi.
 *
 * Sekarang cukup tiga: yang sedang memudar keluar, yang aktif, dan yang
 * berikutnya. Crossfade tetap utuh (dua peserta transisi selalu ada), yang
 * berikutnya punya jatah satu siklus penuh (6,5 detik) untuk selesai diunduh
 * & didekode sebelum gilirannya tiba, dan puncak memorinya turun ke ~25%.
 */
const mountedSlides = computed(() => {
    const total = heroSlides.value.length;

    if (total <= 1) {
        return new Set(total ? [0] : []);
    }

    const index = activeSlide.value;

    return new Set([(index - 1 + total) % total, index, (index + 1) % total]);
});

/** Slide yang sedang tampil — dipakai untuk kredit foto. */
const currentSlide = computed(() => heroSlides.value[activeSlide.value] ?? {});

let ctx = null;

onMounted(() => {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        return;
    }

    // Context-nya dibatasi elemen hero: seluruh selektor `[data-hero-*]` di
    // bawah dicari di dalam section ini saja, bukan di seluruh dokumen.
    ctx = gsap.context(() => {
        // Animasi masuk hero: berurutan, tiap baris judul berputar dari kedalaman.
        const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

        tl.from("[data-hero-badge]", { y: 24, opacity: 0, duration: 0.8 })
            .from(
                "[data-hero-line]",
                {
                    yPercent: 120,
                    rotateX: -70,
                    opacity: 0,
                    duration: 1.2,
                    stagger: 0.13,
                    transformOrigin: "50% 100% -60px",
                },
                "-=0.45",
            )
            .from(
                "[data-hero-text]",
                { y: 28, opacity: 0, duration: 0.9 },
                "-=0.75",
            )
            .fromTo(
                "[data-hero-cta] > *",
                { y: 24, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.7, stagger: 0.12 },
                "-=0.6",
            );

        // Foto latar ikut bergerak lebih lambat → paralaks berlapis.
        //
        // PENTING: hanya `yPercent` yang dianimasikan, TIDAK `scale`.
        // Foto hero memakai `filter` (lihat `.hero-photo` di app.css). Selama
        // elemen cuma digeser, hasil filter cukup di-raster sekali lalu digeser
        // oleh compositor. Begitu ikut diperbesar, ukuran rasternya berubah tiap
        // frame sehingga filter seluas layar dihitung ulang terus — inilah
        // sumber utama gulir tersendat di section hero. Pembesarannya kini
        // statis lewat kelas `scale-110` di templat.
        if (heroPhoto.value) {
            gsap.to(heroPhoto.value, {
                yPercent: 15,
                ease: "none",
                scrollTrigger: {
                    trigger: hero.value,
                    start: "top top",
                    end: "bottom top",
                    scrub: true,
                },
            });
        }

        // Konten hero memudar & terdorong menjauh saat halaman digulir.
        gsap.to("[data-hero-content]", {
            y: -110,
            opacity: 0.1,
            ease: "none",
            scrollTrigger: {
                trigger: hero.value,
                start: "top top",
                end: "bottom top",
                scrub: true,
            },
        });

        // Intro hero diputar ulang tiap kali pengunjung kembali ke atas,
        // jadi animasinya tidak cuma terlihat pada muat pertama.
        ScrollTrigger.create({
            trigger: hero.value,
            start: "top top",
            end: "bottom 35%",
            onEnterBack: () => tl.restart(),
        });

        // Digabung dengan permintaan refresh section lain di frame yang sama,
        // supaya seluruh halaman hanya sekali menghitung ulang posisi trigger.
        refreshScrollTriggers();
    }, hero.value);
});

onBeforeUnmount(() => ctx?.revert());
</script>

<template>
    <!-- `isolate` wajib: tanpa stacking context sendiri, lapisan foto &
         kanvas -z-* akan tertimpa nebula milik pembungkus halaman. -->
    <section
        id="beranda"
        ref="hero"
        class="relative isolate flex min-h-screen items-center overflow-hidden"
    >
        <!-- Lapis 1 — foto sekolah yang bergantian (crossfade).
             Semua slide ditumpuk; hanya yang aktif yang opasitasnya 1,
             jadi pergantiannya benar-benar saling melebur, bukan
             hilang-lalu-muncul. Paralaks dipasang di pembungkusnya
             supaya seluruh tumpukan bergerak bersama. -->
        <div class="absolute inset-0 -z-30 overflow-hidden">
            <div ref="heroPhoto" class="relative h-full w-full scale-110">
                <!-- Hanya slide sebelumnya/aktif/berikutnya yang dipasang —
                     lihat catatan pada `mountedSlides`. -->
                <template v-for="(slide, index) in heroSlides" :key="slide.src">
                    <img
                        v-if="mountedSlides.has(index)"
                        :src="slide.src"
                        :alt="slide.alt"
                        :srcset="slide.srcset"
                        sizes="100vw"
                        :fetchpriority="index === 0 ? 'high' : 'auto'"
                        :loading="index === 0 ? 'eager' : 'lazy'"
                        :decoding="index === 0 ? 'sync' : 'async'"
                        :aria-hidden="index === activeSlide ? 'false' : 'true'"
                        class="hero-photo hero-slide absolute inset-0 h-full w-full object-cover object-center"
                        :class="
                            index === activeSlide ? 'opacity-100' : 'opacity-0'
                        "
                    />
                </template>
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

        <div class="container-page relative pb-24 pt-36 sm:pt-40">
            <div data-hero-content class="max-w-3xl">
                <div data-hero-badge class="flex">
                    <span
                        class="holo-panel-lite inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-aqua-200 sm:text-sm"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-aqua-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex h-2 w-2 rounded-full bg-aqua-300"
                            ></span>
                        </span>
                        {{
                            text(
                                "hero_badge",
                                "Terakreditasi A · PPDB 2026/2027 Dibuka",
                            )
                        }}
                    </span>
                </div>

                <h1
                    class="mt-8 font-display text-[2.6rem] font-extrabold leading-[1.05] tracking-tight sm:text-6xl lg:text-[4.2rem]"
                    style="perspective: 900px"
                >
                    <!-- Tiap baris dibungkus agar bisa "terbit" dari bawah masker. -->
                    <span class="block overflow-hidden py-1">
                        <span
                            data-hero-line
                            class="block text-slate-50 text-glow-aqua"
                        >
                            {{ text("hero_title_1", "Generasi Qur'ani") }}
                        </span>
                    </span>
                    <span class="block overflow-hidden py-1">
                        <span data-hero-line class="text-gradient-neon block">
                            {{ text("hero_title_2", "Berpikir Masa Depan") }}
                        </span>
                    </span>
                    <!-- Baris ketiga boleh dikosongkan admin: bila teks & kata sorot
                         sama-sama kosong, seluruh baris tidak dirender. -->
                    <span
                        v-if="raw('hero_title_3') || raw('hero_title_highlight')"
                        class="block overflow-hidden py-1"
                    >
                        <span data-hero-line class="block text-slate-50">
                            {{ raw("hero_title_3") }}
                            <span
                                v-if="raw('hero_title_highlight')"
                                class="relative inline-block"
                            >
                                <span class="relative z-10">{{
                                    raw("hero_title_highlight")
                                }}</span>
                                <span
                                    class="absolute inset-x-0 bottom-1 z-0 h-3 rounded-full bg-plasma-400/70 blur-[3px]"
                                ></span>
                            </span>
                        </span>
                    </span>
                </h1>

                <p
                    data-hero-text
                    class="mt-7 max-w-xl text-base leading-relaxed text-slate-300/90 sm:text-lg"
                >
                    {{
                        text(
                            "hero_description",
                            "Sekolah Islam terpadu jenjang SD & SMP yang memadukan tahfidz terstruktur, sains modern, dan pembinaan akhlak.",
                        )
                    }}
                </p>

                <div
                    data-hero-cta
                    class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center"
                >
                    <a
                        href="#ppdb"
                        class="group relative overflow-hidden rounded-full bg-[color:var(--cta-via)] bg-linear-to-r from-[color:var(--cta-from)] via-[color:var(--cta-via)] to-[color:var(--cta-to)] px-8 py-4 text-center text-sm font-bold text-[color:var(--cta-ink)] shadow-[0_0_34px_-6px_var(--cta-glow)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_48px_-4px_var(--cta-glow-hover)]"
                        @click.prevent="emit('scroll', '#ppdb')"
                    >
                        <span class="relative z-10">{{
                            text("hero_cta_primary", "Daftar Sekarang")
                        }}</span>
                        <span
                            class="absolute inset-0 -translate-x-full bg-white/40 transition duration-500 group-hover:translate-x-0"
                        ></span>
                    </a>
                    <a
                        href="#keunggulan"
                        class="holo-panel-lite rounded-full px-8 py-4 text-center text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200"
                        @click.prevent="emit('scroll', '#keunggulan')"
                    >
                        {{ text("hero_cta_secondary", "Jelajahi Sekolah") }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Kredit foto placeholder — hapus bila memakai foto sendiri. -->
        <p
            v-if="currentSlide.credit"
            class="pointer-events-none absolute bottom-4 right-5 text-[10px] uppercase tracking-[0.2em] text-slate-500"
        >
            {{ currentSlide.credit }}
        </p>

        <div
            class="pointer-events-none absolute bottom-8 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[10px] font-bold uppercase tracking-[0.3em] text-aqua-300/70 lg:flex"
        >
            <span>Scroll</span>
            <span
                class="h-12 w-px animate-pulse bg-linear-to-b from-aqua-400 to-transparent"
            ></span>
        </div>
    </section>
</template>

<style scoped>
/* Crossfade foto hero: durasi panjang & ease lembut agar pergantiannya
   terasa mengalir, bukan berkedip. */
/* `will-change: opacity` SENGAJA TIDAK dipasang di sini.
   Dulu ada, dan efeknya: setiap slide dipromosikan jadi lapisan compositor
   tersendiri sejak dimuat dan ditahan begitu SELAMANYA — 1920×1080×4 byte ≈ 8 MB
   per slide, padahal transisinya cuma berjalan 1,4 detik tiap 6,5 detik. Browser
   sudah otomatis mempromosikan elemen selama transisi `opacity` berlangsung,
   lalu melepas lapisannya begitu selesai. Petunjuk permanen di sini hanya
   menahan memori GPU tanpa menambah kemulusan apa pun. */
.hero-slide {
    transition: opacity 1.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (prefers-reduced-motion: reduce) {
    .hero-slide {
        transition-duration: 0.01ms;
    }
}
</style>
