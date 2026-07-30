<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import NeonNavbar from '../../Components/Opsi3/NeonNavbar.vue';
import NeonFooter from '../../Components/Opsi3/NeonFooter.vue';
import NewsCard from '../../Components/Opsi3/NewsCard.vue';
import HoloTilt from '../../Components/Opsi3/HoloTilt.vue';
import { newsAccent } from '../../lib/news-accent';
import { useTheme } from '../../lib/theme';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    /** Teks halaman yang bisa diedit admin (dipakai footer). */
    content: { type: Object, default: () => ({}) },
    /** Berita terbaru — ditampilkan besar di puncak halaman. Null bila belum ada berita. */
    featured: { type: Object, default: null },
    /** Sisa berita, sudah terurut dari yang terbaru. */
    articles: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

const { theme } = useTheme();

const replay = (options = {}) => ({ once: false, ...options });

/** Filter & pencarian berjalan di sisi klien — jumlah beritanya masih kecil. */
const activeCategory = ref('Semua');
const query = ref('');

/** Semua berita termasuk yang ditampilkan sebagai sorotan. */
const allArticles = computed(() =>
    props.featured?.slug ? [props.featured, ...props.articles] : props.articles,
);

const filters = computed(() => [
    { label: 'Semua', count: allArticles.value.length },
    ...props.categories,
]);

const matches = (article, keyword) =>
    [article.title, article.excerpt, article.category, ...(article.tags ?? [])]
        .join(' ')
        .toLowerCase()
        .includes(keyword);

const filtered = computed(() => {
    const keyword = query.value.trim().toLowerCase();

    return allArticles.value.filter((article) => {
        const byCategory =
            activeCategory.value === 'Semua' || article.category === activeCategory.value;

        return byCategory && (keyword === '' || matches(article, keyword));
    });
});

/**
 * Sorotan hanya tampil pada tampilan awal (tanpa filter/pencarian), supaya
 * hasil filter tidak pernah menyembunyikan artikel di kartu besar.
 */
const showFeatured = computed(
    () => activeCategory.value === 'Semua' && query.value.trim() === '' && Boolean(props.featured?.slug),
);

const gridItems = computed(() =>
    showFeatured.value ? filtered.value.filter((a) => a.slug !== props.featured.slug) : filtered.value,
);

const featuredAccent = computed(() => newsAccent(props.featured?.accent));

const resetFilter = () => {
    activeCategory.value = 'Semua';
    query.value = '';
};
</script>

<template>
    <Head title="Berita Sekolah" />

    <div :data-theme="theme" class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200">
        <NeonNavbar :school-name="props.schoolName" :links="props.navLinks"
            :content="props.content" active="#berita" />

        <main class="relative z-10">
            <!-- =========================== HEADER =========================== -->
            <section class="relative isolate overflow-hidden pb-16 pt-36 sm:pt-44">
                <div class="pointer-events-none absolute inset-0 -z-10">
                    <div class="cyber-grid absolute inset-0 opacity-40"></div>
                    <div class="scanlines absolute inset-0 opacity-50"></div>
                </div>
                <div v-parallax="{ y: 120, speed: 1.1 }" style="--orb-color: rgba(15, 195, 221, 0.28)"
                    class="orb-glow pointer-events-none absolute -left-32 top-10 -z-10 h-80 w-80">
                </div>
                <div v-parallax="{ y: 100, speed: -0.8, rotate: 18 }"
                    class="pointer-events-none absolute -right-24 top-28 -z-10 h-64 w-64 rotate-[22.5deg] rounded-[4rem] border border-plasma-400/30">
                </div>

                <div class="container-page relative">
                    <!-- Remah roti: jalan pulang yang selalu terlihat. -->
                    <nav v-reveal="replay({ from: 'fade' })" class="flex items-center gap-2 text-xs text-slate-400"
                        aria-label="Remah roti">
                        <Link href="/" class="transition hover:text-aqua-300">Beranda</Link>
                        <span aria-hidden="true">/</span>
                        <span class="font-semibold text-aqua-300">Berita</span>
                    </nav>

                    <div class="mt-6 grid gap-8 lg:grid-cols-12 lg:items-end">
                        <div class="lg:col-span-7">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">
                                Kabar Sekolah
                            </p>
                            <h1 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-4xl font-extrabold leading-tight text-slate-50 sm:text-6xl">
                                Seluruh
                                <span class="text-gradient-neon">Berita &amp; Kegiatan</span>
                            </h1>
                        </div>
                        <p v-reveal="replay({ from: 'left', delay: 0.2 })"
                            class="text-base leading-relaxed text-slate-300/80 lg:col-span-5">
                            Arsip lengkap pengumuman, liputan kegiatan, dan capaian siswa Alazka Islamic School.
                            Gunakan pencarian atau saring berdasarkan kategori untuk menemukan kabar yang Anda cari.
                        </p>
                    </div>

                    <!-- ===================== FILTER & CARI ===================== -->
                    <div v-reveal="replay({ from: 'fade', delay: 0.25 })"
                        class="holo-panel mt-12 flex flex-col gap-4 rounded-[2rem] p-4 lg:flex-row lg:items-center lg:justify-between">
                        <ul class="flex flex-wrap gap-2">
                            <li v-for="filter in filters" :key="filter.label">
                                <button type="button"
                                    class="rounded-full border px-4 py-2 text-xs font-bold transition duration-300"
                                    :class="activeCategory === filter.label
                                        ? 'border-aqua-400/60 bg-aqua-400/15 text-aqua-200 shadow-[0_0_20px_-6px_rgba(52,226,245,0.9)]'
                                        : 'border-void-600 text-slate-300 hover:border-aqua-400/40 hover:text-aqua-200'"
                                    :aria-pressed="activeCategory === filter.label"
                                    @click="activeCategory = filter.label">
                                    {{ filter.label }}
                                    <span class="ml-1 text-[10px] text-slate-400">{{ filter.count }}</span>
                                </button>
                            </li>
                        </ul>

                        <label class="relative w-full lg:max-w-xs">
                            <span class="sr-only">Cari berita</span>
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"
                                aria-hidden="true">🔍</span>
                            <input v-model="query" type="search" placeholder="Cari judul, kategori, atau tag…"
                                class="w-full rounded-full border border-void-600 bg-void-900/60 py-2.5 pl-11 pr-4 text-sm text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-aqua-400/60 focus:shadow-[0_0_22px_-8px_rgba(52,226,245,0.9)]">
                        </label>
                    </div>
                </div>
            </section>

            <!-- ========================== SOROTAN ========================== -->
            <section v-if="showFeatured" class="relative pb-8">
                <div class="container-page">
                    <HoloTilt v-reveal="replay({ from: 'zoom' })" :max="6" :lift="18" radius="2.5rem"
                        :glare-color="featuredAccent.glare">
                        <Link :href="props.featured.href" class="group block">
                            <article
                                class="holo-panel holo-edge relative grid overflow-hidden rounded-[2.5rem] lg:grid-cols-2"
                                :class="featuredAccent.glow">
                                <div class="relative h-56 overflow-hidden bg-linear-to-br lg:h-full lg:min-h-[22rem]"
                                    :class="featuredAccent.media">
                                    <img v-if="props.featured.image" :src="props.featured.image"
                                        :alt="props.featured.title"
                                        class="h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-105 group-hover:opacity-100">
                                    <div class="pattern-lattice-neon absolute inset-0 opacity-20"></div>
                                    <div class="scanlines absolute inset-0 opacity-50"></div>
                                </div>

                                <div class="relative flex flex-col justify-center p-8 sm:p-10">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full border px-3 py-1 text-[11px] font-bold"
                                            :class="featuredAccent.badge">
                                            {{ props.featured.category }}
                                        </span>
                                        <span
                                            class="rounded-full border border-void-600 px-3 py-1 text-[11px] font-bold text-slate-300">
                                            Terbaru
                                        </span>
                                    </div>

                                    <time :datetime="props.featured.publishedAt"
                                        class="mt-5 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                        {{ props.featured.date }} · {{ props.featured.readTime }}
                                    </time>

                                    <h2 class="mt-3 font-display text-2xl font-extrabold leading-snug text-slate-50 sm:text-3xl">
                                        {{ props.featured.title }}
                                    </h2>

                                    <p class="mt-4 text-sm leading-relaxed text-slate-300/80 sm:text-base">
                                        {{ props.featured.excerpt }}
                                    </p>

                                    <span
                                        class="mt-7 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                                        :class="featuredAccent.link">
                                        Baca Selengkapnya
                                        <span aria-hidden="true">&rarr;</span>
                                    </span>
                                </div>
                            </article>
                        </Link>
                    </HoloTilt>
                </div>
            </section>

            <!-- ======================= DAFTAR BERITA ======================= -->
            <section class="relative pb-28 pt-10">
                <div class="container-page">
                    <div class="flex items-baseline justify-between gap-4">
                        <h2 class="font-display text-xl font-bold text-slate-50">
                            {{ activeCategory === 'Semua' ? 'Berita Lainnya' : activeCategory }}
                        </h2>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ filtered.length }} berita
                        </p>
                    </div>

                    <div v-if="gridItems.length" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <NewsCard v-for="(article, index) in gridItems" :key="article.slug"
                            v-reveal="replay({ from: 'up', delay: 0.06 * (index % 3) })" :item="article" />
                    </div>

                    <!-- Keadaan kosong: beri jalan keluar, bukan sekadar pesan. -->
                    <div v-else class="holo-panel mt-8 rounded-[2rem] px-8 py-16 text-center">
                        <p class="text-4xl" aria-hidden="true">🔍</p>
                        <p class="mt-4 font-display text-lg font-bold text-slate-100">
                            Tidak ada berita yang cocok
                        </p>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-400">
                            Coba kata kunci lain, atau tampilkan kembali seluruh berita.
                        </p>
                        <button type="button"
                            class="mt-7 rounded-full border border-aqua-400/40 px-6 py-3 text-sm font-bold text-aqua-200 transition duration-300 hover:-translate-y-0.5 hover:border-aqua-400/80 hover:bg-aqua-400/10"
                            @click="resetFilter">
                            Tampilkan Semua Berita
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <NeonFooter :school-name="props.schoolName" :links="props.navLinks" :contacts="props.contacts"
            :socials="props.socials" :content="props.content" />
    </div>
</template>
