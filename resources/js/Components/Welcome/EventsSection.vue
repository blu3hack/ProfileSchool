<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

import EventCard from "../Opsi3/EventCard.vue";
import EventCountdown from "../Opsi3/EventCountdown.vue";
import { replay } from "../../directives/reveal";
import { newsAccent } from "../../lib/news-accent";
import { usePageText } from "../../lib/page-text";

/**
 * Section "Next Event" (#event) — agenda terdekat tampil besar dengan hitung
 * mundur, tiga sisanya jadi kartu pendamping. Sisanya lagi (bila ada) cukup
 * dilihat di halaman /event.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    items: { type: Array, default: () => [] },
});

const { text } = usePageText(() => props.content);

const featuredEvent = computed(() => props.items[0] ?? null);
const otherEvents = computed(() => props.items.slice(1, 4));
const featuredEventAccent = computed(() =>
    newsAccent(featuredEvent.value?.accent),
);
</script>

<template>
    <section
        id="event"
        class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32"
    >
        <div
            v-parallax="{ y: 120, speed: -1 }"
            style="--orb-color: rgba(139, 77, 255, 0.28)"
            class="orb-glow pointer-events-none absolute -right-32 top-16 h-80 w-80"
        ></div>
        <div
            v-parallax="{ y: 90, speed: 0.8, rotate: -18 }"
            class="pointer-events-none absolute -left-20 bottom-24 h-56 w-56 rotate-[22.5deg] rounded-[4rem] border border-aqua-400/25"
        ></div>

        <div class="container-page relative">
            <div
                class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="max-w-xl">
                    <p
                        v-reveal="replay({ from: 'fade' })"
                        class="text-xs font-bold uppercase tracking-[0.24em] text-volt-300"
                    >
                        {{ text("events_eyebrow", "Agenda Terdekat") }}
                    </p>
                    <h2
                        v-reveal="replay({ delay: 0.1 })"
                        class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                    >
                        {{ text("events_title", "Next Event") }}
                        <span class="text-gradient-neon">
                            {{ text("events_title_highlight", "Sekolah") }}
                        </span>
                    </h2>
                    <p
                        v-reveal="replay({ delay: 0.2 })"
                        class="mt-4 text-base leading-relaxed text-slate-300/80"
                    >
                        {{
                            text(
                                "events_description",
                                "Kegiatan yang akan segera dilaksanakan. Catat tanggalnya dan bergabunglah bersama kami.",
                            )
                        }}
                    </p>
                </div>

                <Link
                    v-reveal="replay({ from: 'left', delay: 0.2 })"
                    href="/event"
                    class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-volt-200"
                >
                    {{ text("events_cta", "Lihat Semua Next Event") }}
                    <span aria-hidden="true">&rarr;</span>
                </Link>
            </div>

            <template v-if="featuredEvent">
                <!-- Acara terdekat: panel besar dengan hitung mundur utama. -->
                <div
                    v-reveal="replay({ from: 'zoom', delay: 0.25 })"
                    class="mt-14"
                >
                    <Link :href="featuredEvent.href" class="group block">
                        <article
                            class="holo-panel holo-edge relative grid overflow-hidden rounded-[2.5rem] lg:grid-cols-2"
                            :class="featuredEventAccent.glow"
                        >
                            <div
                                class="relative h-56 overflow-hidden bg-linear-to-br lg:h-full lg:min-h-[24rem]"
                                :class="featuredEventAccent.media"
                            >
                                <img
                                    v-if="featuredEvent.image"
                                    :src="featuredEvent.image"
                                    :alt="featuredEvent.title"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-105 group-hover:opacity-100"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-6xl"
                                >
                                    {{ featuredEvent.icon }}
                                </div>
                                <div
                                    class="pattern-lattice-neon absolute inset-0 opacity-20"
                                ></div>
                                <div
                                    class="scanlines absolute inset-0 opacity-50"
                                ></div>
                            </div>

                            <div
                                class="relative flex flex-col justify-center p-8 sm:p-10"
                            >
                                <div class="flex flex-wrap items-center gap-3">
                                    <span
                                        class="rounded-full border px-3 py-1 text-[11px] font-bold"
                                        :class="featuredEventAccent.badge"
                                    >
                                        {{ featuredEvent.category }}
                                    </span>
                                    <span
                                        class="rounded-full border border-void-600 px-3 py-1 text-[11px] font-bold text-slate-300"
                                    >
                                        Paling Dekat
                                    </span>
                                </div>

                                <h3
                                    class="mt-5 font-display text-2xl font-extrabold leading-snug text-slate-50 sm:text-3xl"
                                >
                                    {{ featuredEvent.title }}
                                </h3>

                                <dl
                                    class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-300/85"
                                >
                                    <div class="flex items-center gap-2">
                                        <dt class="sr-only">Jadwal</dt>
                                        <dd>
                                            <span aria-hidden="true">🗓️</span>
                                            <time
                                                :datetime="
                                                    featuredEvent.startsAt
                                                "
                                                class="ml-1.5"
                                            >
                                                {{ featuredEvent.date }}
                                            </time>
                                        </dd>
                                    </div>
                                    <div
                                        v-if="featuredEvent.time"
                                        class="flex items-center gap-2"
                                    >
                                        <dt class="sr-only">Waktu</dt>
                                        <dd>
                                            <span aria-hidden="true">⏱</span>
                                            {{ featuredEvent.time }}
                                        </dd>
                                    </div>
                                    <div
                                        v-if="featuredEvent.location"
                                        class="flex items-center gap-2"
                                    >
                                        <dt class="sr-only">Lokasi</dt>
                                        <dd>
                                            <span aria-hidden="true">📍</span>
                                            {{ featuredEvent.location }}
                                        </dd>
                                    </div>
                                </dl>

                                <p
                                    v-if="featuredEvent.excerpt"
                                    class="mt-4 text-sm leading-relaxed text-slate-300/75 sm:text-base"
                                >
                                    {{ featuredEvent.excerpt }}
                                </p>

                                <EventCountdown
                                    class="mt-7"
                                    :starts-at="featuredEvent.startsAt"
                                    :ends-at="featuredEvent.endsAt"
                                    :accent="featuredEvent.accent"
                                />

                                <span
                                    class="mt-7 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                                    :class="featuredEventAccent.link"
                                >
                                    Lihat Detail Acara
                                    <span aria-hidden="true">&rarr;</span>
                                </span>
                            </div>
                        </article>
                    </Link>
                </div>

                <!-- Agenda berikutnya -->
                <div
                    v-if="otherEvents.length"
                    class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <EventCard
                        v-for="(event, index) in otherEvents"
                        :key="event.slug"
                        v-reveal="replay({ from: 'up', delay: 0.08 * index })"
                        :item="event"
                    />
                </div>
            </template>

            <!-- Belum ada agenda: tetap beri kabar, jangan biarkan kosong. -->
            <div
                v-else
                v-reveal="replay({ from: 'fade', delay: 0.25 })"
                class="holo-panel mt-14 rounded-[2rem] px-8 py-16 text-center"
            >
                <p class="text-4xl" aria-hidden="true">📅</p>
                <p
                    class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-slate-400"
                >
                    {{
                        text(
                            "events_empty",
                            "Belum ada agenda yang dijadwalkan. Pantau terus halaman ini untuk kegiatan berikutnya.",
                        )
                    }}
                </p>
            </div>
        </div>
    </section>
</template>
