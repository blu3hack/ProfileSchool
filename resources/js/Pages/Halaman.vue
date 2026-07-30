<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import NeonNavbar from '../Components/Opsi3/NeonNavbar.vue';
import NeonFooter from '../Components/Opsi3/NeonFooter.vue';
import PageBlocks from '../Components/Halaman/PageBlocks.vue';
import { replay } from '../directives/reveal';
import { useTheme } from '../lib/theme';

/**
 * Halaman kustom buatan admin — satu komponen untuk semua alamat yang dibuat
 * lewat menu "Halaman Kustom", mis. /datasiswa.
 *
 * Isinya datang dalam dua bentuk yang saling menggantikan:
 *  - mode `builder` → `page.blocks`, dirender PageBlocks;
 *  - mode `html`    → `page.html`, HTML yang sudah disaring server.
 *
 * `v-html` di bawah aman karena SETIAP jalur masuknya melewati
 * App\Support\HtmlSanitizer: sekali saat admin menyimpan, sekali lagi saat
 * halaman dirender (CustomPage::safeHtml).
 */
const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    /** Teks halaman yang bisa diedit admin — dipakai navbar & footer. */
    content: { type: Object, default: () => ({}) },
    page: { type: Object, required: true },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
    /** Halaman belum diterbitkan; hanya admin yang bisa membukanya. */
    draft: { type: Boolean, default: false },
});

const { theme } = useTheme();

const meta = computed(() => props.page.meta ?? {});
const hasHero = computed(() => Boolean(props.page.heroImage));
</script>

<template>
    <!-- Hanya judul tab yang diurus di sini. Seluruh tag pratinjau (Open Graph,
         Twitter Card, canonical, robots) dicetak server di <head> lewat
         App\Support\PageMeta — perayap WhatsApp/Facebook tidak menjalankan
         JavaScript, jadi tag yang dipasang dari sini tidak pernah terlihat
         olehnya. -->
    <Head :title="meta.title || props.page.title" />

    <div :data-theme="theme" class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200">
        <!-- `active` diisi alamat halaman ini: nilainya tidak akan cocok dengan
             hash menu mana pun, jadi tidak ada menu yang tersorot keliru —
             sekaligus mematikan scroll-spy navbar, yang di sini tak ada
             section-nya untuk diamati. -->
        <NeonNavbar :school-name="props.schoolName" :links="props.navLinks" :content="props.content"
            :active="`/${props.page.slug}`" />

        <main class="relative z-10">
            <!-- ========================= KEPALA HALAMAN ========================= -->
            <header class="relative isolate overflow-hidden pb-14 pt-36 sm:pt-44">
                <div v-if="hasHero" class="absolute inset-0 -z-30 overflow-hidden">
                    <img :src="props.page.heroImage" :srcset="props.page.heroSrcset || undefined" sizes="100vw"
                        :alt="props.page.title" fetchpriority="high"
                        class="h-full w-full scale-105 object-cover object-center">
                </div>
                <div class="pointer-events-none absolute inset-0 -z-20">
                    <div v-if="hasHero" class="hero-veil absolute inset-0"></div>
                    <div class="cyber-grid absolute inset-0 opacity-40"></div>
                    <div class="scanlines absolute inset-0 opacity-60"></div>
                </div>

                <div class="container-page relative">
                    <nav v-reveal="replay({ from: 'fade' })"
                        class="flex flex-wrap items-center gap-2 text-xs text-slate-400" aria-label="Remah roti">
                        <Link href="/" class="transition hover:text-aqua-300">Beranda</Link>
                        <span aria-hidden="true">/</span>
                        <span class="font-semibold text-aqua-300">{{ props.page.title }}</span>
                    </nav>

                    <!-- Pengingat untuk admin: yang sedang dilihat belum tampil
                         bagi pengunjung. -->
                    <div v-if="props.draft" v-reveal="replay({ from: 'fade', delay: 0.04 })"
                        class="mt-6 inline-flex items-center gap-2 rounded-full border border-solar-400/50 bg-solar-400/10 px-4 py-1.5 text-[11px] font-bold text-solar-300">
                        <span aria-hidden="true">👁️</span>
                        Pratinjau draf — halaman ini belum diterbitkan
                    </div>

                    <div class="mt-8 max-w-3xl">
                        <span v-if="props.page.eyebrow" v-reveal="replay({ from: 'fade', delay: 0.05 })"
                            class="inline-flex items-center gap-2 rounded-full border border-aqua-400/40 bg-aqua-400/10 px-4 py-1.5 text-[11px] font-bold text-aqua-200 backdrop-blur">
                            {{ props.page.eyebrow }}
                        </span>

                        <h1 v-reveal="replay({ delay: 0.1 })"
                            class="mt-6 font-display text-3xl font-extrabold leading-[1.15] text-slate-50 sm:text-5xl">
                            {{ props.page.title }}
                        </h1>

                        <p v-if="props.page.summary" v-reveal="replay({ delay: 0.18 })"
                            class="mt-6 text-base leading-relaxed text-slate-300/90 sm:text-lg">
                            {{ props.page.summary }}
                        </p>
                    </div>
                </div>
            </header>

            <!-- ============================ ISI HALAMAN ============================ -->
            <div class="relative pb-24 pt-6">
                <div class="container-page">
                    <PageBlocks v-if="props.page.mode === 'builder'" :blocks="props.page.blocks" />

                    <!-- eslint-disable-next-line vue/no-v-html — sudah disanitasi server -->
                    <div v-else-if="props.page.html" class="page-prose page-html" v-html="props.page.html"></div>

                    <p v-else class="holo-panel rounded-3xl px-6 py-12 text-center text-sm text-slate-400">
                        Halaman ini belum berisi apa pun.
                    </p>
                </div>
            </div>
        </main>

        <NeonFooter :school-name="props.schoolName" :links="props.navLinks" :contacts="props.contacts"
            :socials="props.socials" :content="props.content" />
    </div>
</template>
