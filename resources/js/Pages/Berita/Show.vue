<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import NeonNavbar from '../../Components/Opsi3/NeonNavbar.vue';
import NeonFooter from '../../Components/Opsi3/NeonFooter.vue';
import NewsCard from '../../Components/Opsi3/NewsCard.vue';
import { newsAccent } from '../../lib/news-accent';
import { scrollToElement } from '../../lib/navigate';
import { useTheme } from '../../lib/theme';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    /** Teks halaman yang bisa diedit admin (dipakai footer). */
    content: { type: Object, default: () => ({}) },
    article: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

const { theme } = useTheme();

const replay = (options = {}) => ({ once: false, ...options });

const accent = computed(() => newsAccent(props.article.accent));

/** Tautan berbagi dibentuk di klien agar selalu memakai URL yang sedang dibuka. */
const shareUrl = computed(() => (typeof window === 'undefined' ? '' : window.location.href));

const shareTargets = computed(() => [
    {
        label: 'WhatsApp',
        href: `https://wa.me/?text=${encodeURIComponent(`${props.article.title} — ${shareUrl.value}`)}`,
    },
    {
        label: 'Facebook',
        href: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`,
    },
    {
        label: 'Telegram',
        href: `https://t.me/share/url?url=${encodeURIComponent(shareUrl.value)}&text=${encodeURIComponent(props.article.title)}`,
    },
]);

/** Hanya blok berjudul yang masuk daftar isi ringkas. */
const outline = computed(() =>
    (props.article.body ?? [])
        .map((block, index) => ({ ...block, id: `bagian-${index}` }))
        .filter((block) => block.type === 'heading'),
);

const blocks = computed(() =>
    (props.article.body ?? []).map((block, index) => ({ ...block, id: `bagian-${index}` })),
);

/** Lenis memegang kendali scroll, jadi anchor bawaan browser dilewati. */
const goToBlock = (id) => {
    const target = document.getElementById(id);

    if (target) {
        scrollToElement(target);
    }
};
</script>

<template>
    <Head :title="props.article.title" />

    <div :data-theme="theme" class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200">
        <NeonNavbar :school-name="props.schoolName" :links="props.navLinks" :content="props.content" active="#berita" />

        <main class="relative z-10">
            <!-- ======================= HERO ARTIKEL ======================= -->
            <article>
                <header class="relative isolate overflow-hidden pb-14 pt-36 sm:pt-44">
                    <!-- Gambar sampul jadi latar, diredam agar teks tetap terbaca. -->
                    <div class="absolute inset-0 -z-30 overflow-hidden">
                        <img v-if="props.article.image" :src="props.article.image" :alt="props.article.imageCaption"
                            fetchpriority="high" class="h-full w-full scale-105 object-cover object-center">
                    </div>
                    <div class="pointer-events-none absolute inset-0 -z-20">
                        <div class="hero-veil absolute inset-0"></div>
                        <div class="cyber-grid absolute inset-0 opacity-40"></div>
                        <div class="scanlines absolute inset-0 opacity-60"></div>
                    </div>

                    <div class="container-page relative">
                        <nav v-reveal="replay({ from: 'fade' })"
                            class="flex flex-wrap items-center gap-2 text-xs text-slate-400" aria-label="Remah roti">
                            <Link href="/" class="transition hover:text-aqua-300">Beranda</Link>
                            <span aria-hidden="true">/</span>
                            <Link href="/berita" class="transition hover:text-aqua-300">Berita</Link>
                            <span aria-hidden="true">/</span>
                            <span class="font-semibold" :class="accent.text">{{ props.article.category }}</span>
                        </nav>

                        <div class="mt-8 max-w-3xl">
                            <span v-reveal="replay({ from: 'fade', delay: 0.05 })"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-[11px] font-bold backdrop-blur"
                                :class="accent.badge">
                                <span aria-hidden="true">{{ props.article.icon }}</span>
                                {{ props.article.category }}
                            </span>

                            <h1 v-reveal="replay({ delay: 0.1 })"
                                class="mt-6 font-display text-3xl font-extrabold leading-[1.15] text-slate-50 sm:text-5xl">
                                {{ props.article.title }}
                            </h1>

                            <p v-reveal="replay({ delay: 0.18 })"
                                class="mt-6 text-base leading-relaxed text-slate-300/90 sm:text-lg">
                                {{ props.article.excerpt }}
                            </p>

                            <!-- Metadata: siapa menulis, kapan, berapa lama dibaca. -->
                            <dl v-reveal="replay({ from: 'fade', delay: 0.25 })"
                                class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <dt class="text-slate-500">Ditulis oleh</dt>
                                    <dd class="font-semibold text-slate-200">{{ props.article.author }}</dd>
                                </div>
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Tanggal terbit</dt>
                                    <dd>
                                        <time :datetime="props.article.publishedAt" class="font-semibold text-slate-200">
                                            {{ props.article.date }}
                                        </time>
                                    </dd>
                                </div>
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Perkiraan waktu baca</dt>
                                    <dd class="font-semibold" :class="accent.text">{{ props.article.readTime }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </header>

                <!-- ========================= ISI BERITA ========================= -->
                <div class="relative pb-24 pt-14">
                    <div class="container-page">
                        <div class="grid gap-12 lg:grid-cols-12">
                            <!-- Kolom isi -->
                            <div class="lg:col-span-8">
                                <figure v-if="props.article.image" v-reveal="replay({ from: 'zoom' })"
                                    class="holo-panel overflow-hidden rounded-[2rem]">
                                    <img :src="props.article.image" :alt="props.article.imageCaption" loading="lazy"
                                        class="h-64 w-full object-cover sm:h-96">
                                    <figcaption v-if="props.article.imageCaption"
                                        class="px-6 py-4 text-xs leading-relaxed text-slate-400">
                                        {{ props.article.imageCaption }}
                                    </figcaption>
                                </figure>

                                <div class="mt-12 space-y-7">
                                    <template v-for="block in blocks" :key="block.id">
                                        <h2 v-if="block.type === 'heading'" :id="block.id"
                                            v-reveal="replay({ from: 'fade' })"
                                            class="scroll-mt-28 pt-4 font-display text-xl font-bold text-slate-50 sm:text-2xl">
                                            <span class="mr-3 inline-block h-4 w-1 rounded-full align-middle"
                                                :class="accent.rule" aria-hidden="true"></span>
                                            {{ block.text }}
                                        </h2>

                                        <ul v-else-if="block.type === 'list'" v-reveal="replay({ from: 'fade' })"
                                            class="space-y-3">
                                            <li v-for="point in block.items" :key="point"
                                                class="flex gap-3 text-base leading-relaxed text-slate-300/85">
                                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full"
                                                    :class="accent.rule" aria-hidden="true"></span>
                                                <span>{{ point }}</span>
                                            </li>
                                        </ul>

                                        <figure v-else-if="block.type === 'quote'"
                                            v-reveal="replay({ from: 'left' })"
                                            class="holo-panel relative overflow-hidden rounded-[1.75rem] p-8">
                                            <div class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10">
                                            </div>
                                            <blockquote
                                                class="relative font-display text-lg leading-relaxed text-slate-100 sm:text-xl">
                                                &ldquo;{{ block.text }}&rdquo;
                                            </blockquote>
                                            <figcaption v-if="block.cite"
                                                class="relative mt-4 text-xs font-bold uppercase tracking-[0.18em]"
                                                :class="accent.text">
                                                {{ block.cite }}
                                            </figcaption>
                                        </figure>

                                        <p v-else v-reveal="replay({ from: 'fade' })"
                                            class="text-base leading-[1.9] text-slate-300/85">
                                            {{ block.text }}
                                        </p>
                                    </template>
                                </div>

                                <!-- Tag -->
                                <ul v-if="props.article.tags?.length" class="mt-12 flex flex-wrap gap-2">
                                    <li v-for="tag in props.article.tags" :key="tag"
                                        class="rounded-full border px-3 py-1.5 text-xs font-bold" :class="accent.chip">
                                        #{{ tag }}
                                    </li>
                                </ul>

                                <div class="mt-12 flex flex-col gap-4 border-t border-void-700 pt-8 sm:flex-row sm:items-center sm:justify-between">
                                    <Link href="/berita"
                                        class="inline-flex items-center gap-2 text-sm font-bold text-slate-200 transition hover:text-aqua-300">
                                        <span aria-hidden="true">&larr;</span>
                                        Kembali ke Daftar Berita
                                    </Link>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                            Bagikan
                                        </span>
                                        <a v-for="target in shareTargets" :key="target.label" :href="target.href"
                                            target="_blank" rel="noopener noreferrer"
                                            class="rounded-full border border-void-600 px-4 py-2 text-xs font-semibold text-slate-300 transition duration-300 hover:-translate-y-0.5 hover:border-aqua-400/60 hover:text-aqua-200">
                                            {{ target.label }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom samping: daftar isi + ajakan PPDB -->
                            <aside class="lg:col-span-4">
                                <div class="lg:sticky lg:top-28 lg:space-y-6">
                                    <nav v-if="outline.length" class="holo-panel rounded-[1.75rem] p-6"
                                        aria-label="Daftar isi">
                                        <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="accent.text">
                                            Daftar Isi
                                        </p>
                                        <ul class="mt-4 space-y-3">
                                            <li v-for="section in outline" :key="section.id">
                                                <a :href="`#${section.id}`"
                                                    class="block text-sm leading-relaxed text-slate-300 transition hover:translate-x-1 hover:text-aqua-200"
                                                    @click.prevent="goToBlock(section.id)">
                                                    {{ section.text }}
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>

                                    <div class="holo-panel relative mt-6 overflow-hidden rounded-[1.75rem] p-6 lg:mt-0">
                                        <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-aqua-500/25 blur-3xl">
                                        </div>
                                        <p class="relative font-display text-lg font-bold text-slate-50">
                                            Tertarik bergabung?
                                        </p>
                                        <p class="relative mt-3 text-sm leading-relaxed text-slate-300/80">
                                            PPDB tahun ajaran 2026/2027 sedang dibuka dengan kuota terbatas dan
                                            beasiswa prestasi.
                                        </p>
                                        <a href="https://wa.me/622287654321"
                                            class="relative mt-6 block rounded-full bg-linear-to-r from-aqua-400 to-volt-400 px-6 py-3 text-center text-sm font-bold text-void-950 shadow-[0_0_28px_-8px_rgba(52,226,245,0.9)] transition duration-300 hover:-translate-y-0.5">
                                            Konsultasi PPDB
                                        </a>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </article>

            <!-- ======================= BERITA TERKAIT ======================= -->
            <section v-if="props.related.length" class="relative overflow-hidden pb-28">
                <div v-parallax="{ y: 100, speed: -0.9 }"
                    class="pointer-events-none absolute -right-24 top-0 h-72 w-72 rounded-full bg-plasma-500/15 blur-3xl">
                </div>

                <div class="container-page relative">
                    <div class="flex flex-col gap-4 border-t border-void-700 pt-14 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">Baca Juga</p>
                            <h2 class="mt-3 font-display text-2xl font-extrabold text-slate-50 sm:text-3xl">
                                Berita Terkait
                            </h2>
                        </div>

                        <Link href="/berita"
                            class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200">
                            Lihat Semua Berita
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <NewsCard v-for="(item, index) in props.related" :key="item.slug"
                            v-reveal="replay({ from: 'up', delay: 0.08 * index })" :item="item" />
                    </div>
                </div>
            </section>
        </main>

        <NeonFooter :school-name="props.schoolName" :links="props.navLinks" :contacts="props.contacts"
            :socials="props.socials" :content="props.content" />
    </div>
</template>
