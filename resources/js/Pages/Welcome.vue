<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";

import GalleryLightbox from "../Components/Opsi3/GalleryLightbox.vue";
import NeonFooter from "../Components/Opsi3/NeonFooter.vue";
import NeonNavbar from "../Components/Opsi3/NeonNavbar.vue";
import AchievementsSection from "../Components/Welcome/AchievementsSection.vue";
import ActivitiesSection from "../Components/Welcome/ActivitiesSection.vue";
import EventsSection from "../Components/Welcome/EventsSection.vue";
import GallerySection from "../Components/Welcome/GallerySection.vue";
import HeroSection from "../Components/Welcome/HeroSection.vue";
import NewsSection from "../Components/Welcome/NewsSection.vue";
import PillarsSection from "../Components/Welcome/PillarsSection.vue";
import PpdbSection from "../Components/Welcome/PpdbSection.vue";
import { getSmoothScroll } from "../lib/smooth-scroll";
import { useTheme } from "../lib/theme";

/**
 * Beranda — konsep "Neo Cyber Madrasah".
 *
 * Berkas ini sengaja tipis: tugasnya cuma menerima payload dari
 * Opsi3Controller, membagikannya ke section yang membutuhkan, lalu menampung
 * dua hal yang memang milik halaman (bukan milik satu section) — gulir antar
 * section dan popup foto galeri. Isi tiap section tinggal di
 * resources/js/Components/Welcome/.
 */
const props = defineProps({
    schoolName: { type: String, default: "Alazka Islamic School" },
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

/** Daftar menu datang dari server (SiteInfo); fallback bila halaman dipakai lepas. */
const navLinks = props.navLinks.length
    ? props.navLinks
    : [
          { label: "Beranda", hash: "#beranda" },
          { label: "Keunggulan", hash: "#keunggulan" },
          { label: "Berita", hash: "#berita" },
          { label: "Next Event", hash: "#event" },
          { label: "Kegiatan", hash: "#kegiatan" },
          { label: "Prestasi", hash: "#prestasi" },
          { label: "Kontak", hash: "#kontak" },
      ];

/**
 * Satu-satunya pelaksana gulir antar section. Section yang punya tombol menuju
 * section lain (hero, PPDB) cukup memancarkan `@scroll="'#tujuan'"` — dengan
 * begitu offset & durasi gulirnya seragam di seluruh halaman.
 */
const scrollTo = (hash) => {
    const target = document.querySelector(hash);

    if (!target) {
        return;
    }

    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset: -96, duration: 1.5 });
    } else {
        target.scrollIntoView({ behavior: "smooth" });
    }
};

/** Indeks foto galeri yang sedang dibuka di popup; null = tertutup. */
const lightboxIndex = ref(null);
const openLightbox = (index) => (lightboxIndex.value = index);
</script>

<template>
    <!-- Tanpa `title`: tab beranda cukup menampilkan nama sekolah saja. -->
    <Head />

    <!-- `data-theme` inilah sakelar visual halaman: CSS menimpa variabel warna
         berdasarkan nilainya, jadi seluruh utilitas Tailwind ikut berganti.
         Sumber utamanya kini <html> (dipasang server + lib/theme.js) supaya
         <body> dan elemen ber-teleport ikut bertema; salinan di sini menjaga
         isi halaman tetap benar seandainya atribut di <html> tidak terpasang. -->
    <div
        :data-theme="theme"
        class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200"
    >
        <NeonNavbar
            :school-name="props.schoolName"
            :links="navLinks"
            :content="props.content"
        />

        <main class="relative z-10">
            <HeroSection
                :content="props.content"
                :hero-image="props.heroImage"
                :hero-slides="props.heroSlides"
                @scroll="scrollTo"
            />

            <PillarsSection :content="props.content" :items="props.pillars" />

            <NewsSection :content="props.content" :items="props.news" />

            <EventsSection :content="props.content" :items="props.events" />

            <ActivitiesSection
                :content="props.content"
                :items="props.activities"
            />

            <AchievementsSection
                :content="props.content"
                :items="props.achievements"
            />

            <!-- Hilang sendiri selama admin belum mengunggah satu foto pun. -->
            <GallerySection
                :content="props.content"
                :images="props.gallery"
                @open="openLightbox"
            />

            <PpdbSection :content="props.content" @scroll="scrollTo" />
        </main>

        <NeonFooter
            :school-name="props.schoolName"
            :links="navLinks"
            :contacts="props.contacts"
            :socials="props.socials"
            :content="props.content"
        />

        <!-- Popup foto galeri — dibagikan seluruh section yang menampilkan galeri. -->
        <GalleryLightbox
            v-model:index="lightboxIndex"
            :images="props.gallery"
        />
    </div>
</template>
