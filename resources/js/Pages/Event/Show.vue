<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import NeonNavbar from '../../Components/Opsi3/NeonNavbar.vue';
import NeonFooter from '../../Components/Opsi3/NeonFooter.vue';
import EventCard from '../../Components/Opsi3/EventCard.vue';
import EventCountdown from '../../Components/Opsi3/EventCountdown.vue';
import { newsAccent } from '../../lib/news-accent';
import { scrollToElement } from '../../lib/navigate';
import { useTheme } from '../../lib/theme';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    /** Teks halaman yang bisa diedit admin (dipakai footer). */
    content: { type: Object, default: () => ({}) },
    event: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

const { theme } = useTheme();

const replay = (options = {}) => ({ once: false, ...options });

const accent = computed(() => newsAccent(props.event.accent));

/** Ringkasan acara di kolom samping — hanya baris yang memang terisi. */
const details = computed(() =>
    [
        { icon: '🗓️', label: 'Tanggal', value: props.event.date },
        { icon: '⏱', label: 'Waktu', value: props.event.time },
        { icon: '📍', label: 'Lokasi', value: props.event.location },
        { icon: '👥', label: 'Peserta', value: props.event.audience },
        { icon: '🏳️', label: 'Penyelenggara', value: props.event.organizer },
    ].filter((row) => Boolean(row.value)),
);

/** Tautan berbagi dibentuk di klien agar selalu memakai URL yang sedang dibuka. */
const shareUrl = computed(() => (typeof window === 'undefined' ? '' : window.location.href));

const shareTargets = computed(() => [
    {
        label: 'WhatsApp',
        href: `https://wa.me/?text=${encodeURIComponent(`${props.event.title} — ${shareUrl.value}`)}`,
    },
    {
        label: 'Facebook',
        href: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`,
    },
    {
        label: 'Telegram',
        href: `https://t.me/share/url?url=${encodeURIComponent(shareUrl.value)}&text=${encodeURIComponent(props.event.title)}`,
    },
]);

/** Hanya blok berjudul yang masuk daftar isi ringkas. */
const outline = computed(() =>
    (props.event.body ?? [])
        .map((block, index) => ({ ...block, id: `bagian-${index}` }))
        .filter((block) => block.type === 'heading'),
);

const blocks = computed(() =>
    (props.event.body ?? []).map((block, index) => ({ ...block, id: `bagian-${index}` })),
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
    <Head :title="props.event.title" />

    <div :data-theme="theme" class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200">
        <NeonNavbar :school-name="props.schoolName" :links="props.navLinks" :content="props.content" active="#event" />

        <main class="relative z-10">
            <!-- ======================== HERO ACARA ======================== -->
            <article>
                <header class="relative isolate overflow-hidden pb-14 pt-36 sm:pt-44">
                    <!-- Gambar acara jadi latar, diredam agar teks tetap terbaca. -->
                    <div class="absolute inset-0 -z-30 overflow-hidden">
                        <img v-if="props.event.image" :src="props.event.image" :alt="props.event.imageCaption"
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
                            <Link href="/event" class="transition hover:text-aqua-300">Next Event</Link>
                            <span aria-hidden="true">/</span>
                            <span class="font-semibold" :class="accent.text">{{ props.event.category }}</span>
                        </nav>

                        <div class="mt-8 max-w-3xl">
                            <span v-reveal="replay({ from: 'fade', delay: 0.05 })"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-[11px] font-bold backdrop-blur"
                                :class="accent.badge">
                                <span aria-hidden="true">{{ props.event.icon }}</span>
                                {{ props.event.category }}
                            </span>

                            <h1 v-reveal="replay({ delay: 0.1 })"
                                class="mt-6 font-display text-3xl font-extrabold leading-[1.15] text-slate-50 sm:text-5xl">
                                {{ props.event.title }}
                            </h1>

                            <p v-if="props.event.excerpt" v-reveal="replay({ delay: 0.18 })"
                                class="mt-6 text-base leading-relaxed text-slate-300/90 sm:text-lg">
                                {{ props.event.excerpt }}
                            </p>

                            <!-- Jadwal ringkas: kapan, jam berapa, di mana. -->
                            <dl v-reveal="replay({ from: 'fade', delay: 0.25 })"
                                class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Tanggal</dt>
                                    <dd class="font-semibold text-slate-100">
                                        <span aria-hidden="true">🗓️</span>
                                        <time :datetime="props.event.startsAt" class="ml-1.5">
                                            {{ props.event.date }}
                                        </time>
                                    </dd>
                                </div>
                                <div v-if="props.event.time" class="flex items-center gap-2">
                                    <dt class="sr-only">Waktu</dt>
                                    <dd class="font-semibold" :class="accent.text">
                                        <span aria-hidden="true">⏱</span> {{ props.event.time }}
                                    </dd>
                                </div>
                                <div v-if="props.event.location" class="flex items-center gap-2">
                                    <dt class="sr-only">Lokasi</dt>
                                    <dd class="font-semibold text-slate-100">
                                        <span aria-hidden="true">📍</span> {{ props.event.location }}
                                    </dd>
                                </div>
                            </dl>

                            <!-- Hitung mundur utama halaman ini. -->
                            <div v-reveal="replay({ from: 'zoom', delay: 0.3 })"
                                class="holo-panel mt-9 inline-block rounded-[2rem] p-5 sm:p-6">
                                <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">
                                    Hitung Mundur Acara
                                </p>
                                <EventCountdown :starts-at="props.event.startsAt" :ends-at="props.event.endsAt"
                                    :accent="props.event.accent" />
                            </div>

                            <div v-if="props.event.registrationUrl" v-reveal="replay({ from: 'fade', delay: 0.35 })"
                                class="mt-8">
                                <a :href="props.event.registrationUrl" target="_blank" rel="noopener noreferrer"
                                    class="inline-block rounded-full bg-linear-to-r from-aqua-400 to-volt-400 px-8 py-4 text-sm font-bold text-void-950 shadow-[0_0_34px_-6px_rgba(52,226,245,0.9)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_48px_-4px_rgba(169,123,255,0.95)]">
                                    {{ props.event.registrationLabel }}
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- ========================= ISI ACARA ========================= -->
                <div class="relative pb-24 pt-14">
                    <div class="container-page">
                        <div class="grid gap-12 lg:grid-cols-12">
                            <!-- Kolom isi -->
                            <div class="lg:col-span-8">
                                <figure v-if="props.event.image" v-reveal="replay({ from: 'zoom' })"
                                    class="holo-panel overflow-hidden rounded-[2rem]">
                                    <img :src="props.event.image" :alt="props.event.imageCaption" loading="lazy"
                                        class="h-64 w-full object-cover sm:h-96">
                                    <figcaption v-if="props.event.imageCaption"
                                        class="px-6 py-4 text-xs leading-relaxed text-slate-400">
                                        {{ props.event.imageCaption }}
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

                                        <figure v-else-if="block.type === 'quote'" v-reveal="replay({ from: 'left' })"
                                            class="holo-panel relative overflow-hidden rounded-[1.75rem] p-8">
                                            <div
                                                class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10">
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

                                <!-- Susunan acara -->
                                <section v-if="props.event.rundown?.length" class="mt-14">
                                    <h2 class="font-display text-xl font-bold text-slate-50 sm:text-2xl">
                                        <span class="mr-3 inline-block h-4 w-1 rounded-full align-middle"
                                            :class="accent.rule" aria-hidden="true"></span>
                                        Susunan Acara
                                    </h2>

                                    <ol class="mt-6 space-y-3">
                                        <li v-for="(row, index) in props.event.rundown" :key="index"
                                            v-reveal="replay({ from: 'left', delay: 0.05 * index })"
                                            class="holo-panel flex flex-col gap-1 rounded-[1.25rem] px-5 py-4 transition duration-300 hover:translate-x-1 sm:flex-row sm:items-baseline sm:gap-5">
                                            <span
                                                class="shrink-0 font-display text-sm font-bold tabular-nums sm:w-28"
                                                :class="accent.text">
                                                {{ row.time || '—' }}
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold text-slate-100">
                                                    {{ row.title }}
                                                </span>
                                                <span v-if="row.description"
                                                    class="mt-1 block text-xs leading-relaxed text-slate-400">
                                                    {{ row.description }}
                                                </span>
                                            </span>
                                        </li>
                                    </ol>
                                </section>

                                <!-- Galeri pendukung -->
                                <section v-if="props.event.gallery?.length" class="mt-14">
                                    <h2 class="font-display text-xl font-bold text-slate-50 sm:text-2xl">
                                        <span class="mr-3 inline-block h-4 w-1 rounded-full align-middle"
                                            :class="accent.rule" aria-hidden="true"></span>
                                        Galeri
                                    </h2>

                                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                        <figure v-for="(photo, index) in props.event.gallery" :key="index"
                                            v-reveal="replay({ from: 'up', delay: 0.06 * index })"
                                            class="holo-panel overflow-hidden rounded-[1.5rem]">
                                            <img :src="photo.src" :alt="photo.caption ?? props.event.title"
                                                loading="lazy" class="h-48 w-full object-cover">
                                            <figcaption v-if="photo.caption"
                                                class="px-5 py-3 text-xs leading-relaxed text-slate-400">
                                                {{ photo.caption }}
                                            </figcaption>
                                        </figure>
                                    </div>
                                </section>

                                <!-- Tag -->
                                <ul v-if="props.event.tags?.length" class="mt-12 flex flex-wrap gap-2">
                                    <li v-for="tag in props.event.tags" :key="tag"
                                        class="rounded-full border px-3 py-1.5 text-xs font-bold" :class="accent.chip">
                                        #{{ tag }}
                                    </li>
                                </ul>

                                <div
                                    class="mt-12 flex flex-col gap-4 border-t border-void-700 pt-8 sm:flex-row sm:items-center sm:justify-between">
                                    <Link href="/event"
                                        class="inline-flex items-center gap-2 text-sm font-bold text-slate-200 transition hover:text-aqua-300">
                                        <span aria-hidden="true">&larr;</span>
                                        Kembali ke Daftar Agenda
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

                            <!-- Kolom samping: info acara, daftar isi, ajakan daftar -->
                            <aside class="lg:col-span-4">
                                <div class="lg:sticky lg:top-28 lg:space-y-6">
                                    <div class="holo-panel rounded-[1.75rem] p-6">
                                        <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="accent.text">
                                            Informasi Acara
                                        </p>
                                        <dl class="mt-4 space-y-4">
                                            <div v-for="row in details" :key="row.label" class="flex gap-3">
                                                <span aria-hidden="true">{{ row.icon }}</span>
                                                <span class="min-w-0">
                                                    <dt class="text-[11px] uppercase tracking-[0.16em] text-slate-500">
                                                        {{ row.label }}
                                                    </dt>
                                                    <dd class="mt-0.5 text-sm font-semibold leading-relaxed text-slate-100">
                                                        {{ row.value }}
                                                    </dd>
                                                </span>
                                            </div>
                                        </dl>

                                        <a v-if="props.event.registrationUrl" :href="props.event.registrationUrl"
                                            target="_blank" rel="noopener noreferrer"
                                            class="mt-6 block rounded-full bg-linear-to-r from-aqua-400 to-volt-400 px-6 py-3 text-center text-sm font-bold text-void-950 shadow-[0_0_28px_-8px_rgba(52,226,245,0.9)] transition duration-300 hover:-translate-y-0.5">
                                            {{ props.event.registrationLabel }}
                                        </a>
                                    </div>

                                    <nav v-if="outline.length" class="holo-panel mt-6 rounded-[1.75rem] p-6 lg:mt-0"
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
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </article>

            <!-- ======================== AGENDA LAINNYA ======================== -->
            <section v-if="props.related.length" class="relative overflow-hidden pb-28">
                <div v-parallax="{ y: 100, speed: -0.9 }" style="--orb-color: rgba(139, 77, 255, 0.22)"
                    class="orb-glow pointer-events-none absolute -right-24 top-0 h-72 w-72">
                </div>

                <div class="container-page relative">
                    <div
                        class="flex flex-col gap-4 border-t border-void-700 pt-14 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-volt-300">Jangan Terlewat</p>
                            <h2 class="mt-3 font-display text-2xl font-extrabold text-slate-50 sm:text-3xl">
                                Agenda Lainnya
                            </h2>
                        </div>

                        <Link href="/event"
                            class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-volt-200">
                            Lihat Semua Next Event
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <EventCard v-for="(item, index) in props.related" :key="item.slug"
                            v-reveal="replay({ from: 'up', delay: 0.08 * index })" :item="item" />
                    </div>
                </div>
            </section>
        </main>

        <NeonFooter :school-name="props.schoolName" :links="props.navLinks" :contacts="props.contacts"
            :socials="props.socials" :content="props.content" />
    </div>
</template>
