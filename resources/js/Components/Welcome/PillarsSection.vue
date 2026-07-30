<script setup>
import HoloTilt from "../Opsi3/HoloTilt.vue";
import { replay } from "../../directives/reveal";
import { usePageText } from "../../lib/page-text";

/**
 * Section "Keunggulan" (#keunggulan) — empat pilar sekolah dalam grid
 * asimetris: kartu besar (3 kolom) dan kecil (2 kolom) bergantian.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    items: { type: Array, default: () => [] },
});

const { text } = usePageText(() => props.content);

/**
 * Kelas aksen per kartu keunggulan — dipetakan dari data controller.
 *
 * Sengaja tidak memakai `newsAccent`: pilar punya kebutuhan sendiri
 * (`orb` untuk pendar sudut dan `icon` untuk kotak ikon) yang tidak dipakai
 * kartu berita mana pun.
 */
const pillarAccents = {
    mint: {
        chip: "border-aqua-400/40 bg-aqua-400/12 text-aqua-200",
        orb: "rgba(15, 195, 221, 0.42)",
        glow: "neon-aqua",
        glare: "rgba(124, 243, 255, 0.3)",
        icon: "from-aqua-400/30 to-aqua-600/10 text-aqua-200 shadow-[0_0_26px_-6px_rgba(52,226,245,0.9)]",
    },
    gold: {
        chip: "border-solar-400/40 bg-solar-400/12 text-solar-300",
        orb: "rgba(255, 199, 61, 0.38)",
        glow: "neon-solar",
        glare: "rgba(255, 199, 61, 0.28)",
        icon: "from-solar-400/30 to-solar-500/10 text-solar-300 shadow-[0_0_26px_-6px_rgba(255,199,61,0.9)]",
    },
    sky: {
        chip: "border-volt-400/40 bg-volt-400/12 text-volt-300",
        orb: "rgba(139, 77, 255, 0.38)",
        glow: "neon-volt",
        glare: "rgba(169, 123, 255, 0.3)",
        icon: "from-volt-400/30 to-volt-500/10 text-volt-300 shadow-[0_0_26px_-6px_rgba(169,123,255,0.9)]",
    },
    lilac: {
        chip: "border-plasma-400/40 bg-plasma-400/12 text-plasma-300",
        orb: "rgba(233, 48, 177, 0.38)",
        glow: "neon-plasma",
        glare: "rgba(255, 94, 207, 0.3)",
        icon: "from-plasma-400/30 to-plasma-500/10 text-plasma-300 shadow-[0_0_26px_-6px_rgba(255,94,207,0.9)]",
    },
};

const accentOf = (name) => pillarAccents[name] ?? pillarAccents.mint;

/** Kartu besar mengisi 3 dari 5 kolom — sumber ritme layout asimetris. */
const spanOf = (span) => (span === "lg" ? "md:col-span-3" : "md:col-span-2");
</script>

<template>
    <section id="keunggulan" class="relative scroll-mt-28 py-24 sm:py-32">
        <div class="container-page">
            <!-- Judul rata kiri + deskripsi rata kanan = ritme asimetris. -->
            <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-7">
                    <p
                        v-reveal="replay({ from: 'fade' })"
                        class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300"
                    >
                        {{ text("pillars_eyebrow", "Keunggulan Kami") }}
                    </p>
                    <h2
                        v-reveal="replay({ delay: 0.1 })"
                        class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                    >
                        {{ text("pillars_title", "Empat Pilar yang Menopang") }}
                        <span class="text-gradient-neon">
                            {{
                                text(
                                    "pillars_title_highlight",
                                    "Tumbuh Kembang Ananda",
                                )
                            }}
                        </span>
                    </h2>
                </div>
                <p
                    v-reveal="replay({ from: 'left', delay: 0.2 })"
                    class="text-base leading-relaxed text-slate-300/80 lg:col-span-5"
                >
                    {{
                        text(
                            "pillars_description",
                            "Setiap pilar dirancang agar ananda tumbuh seimbang.",
                        )
                    }}
                </p>
            </div>

            <!-- Grid 5 kolom: kartu besar (3) + kecil (2) bergantian. -->
            <div class="mt-16 grid gap-5 md:grid-cols-5">
                <HoloTilt
                    v-for="(pillar, index) in props.items"
                    :key="pillar.title"
                    v-reveal="
                        replay({
                            from: index % 2 === 0 ? 'right' : 'left',
                            delay: 0.08 * index,
                        })
                    "
                    :class="spanOf(pillar.span)"
                    :max="9"
                    radius="2rem"
                    :glare-color="accentOf(pillar.accent).glare"
                >
                    <article
                        class="holo-panel holo-edge group/card relative flex h-full flex-col overflow-hidden rounded-[2rem] transition-shadow duration-500"
                        :class="accentOf(pillar.accent).glow"
                    >
                        <!-- Gradient, bukan `blur-3xl`: kartu ini dibungkus
                             HoloTilt yang menskalakan seluruh isinya saat
                             hover, dan `filter: blur()` di dalam elemen ber-skala
                             harus di-raster ulang tiap frame. -->
                        <div
                            class="orb-glow pointer-events-none absolute -right-16 -top-16 h-48 w-48"
                            :style="{
                                '--orb-color': accentOf(pillar.accent).orb,
                            }"
                        ></div>
                        <div
                            class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-15"
                        ></div>
                        <div
                            class="scanlines pointer-events-none absolute inset-0 opacity-40"
                        ></div>

                        <!-- Foto pendukung pilar — tampil hanya bila admin mengunggahnya.
                             Rasio tetap menjaga tinggi seragam antar kartu di semua layar;
                             gradasi bawah melebur foto ke panel gelap agar teks tetap terbaca. -->
                        <div
                            v-if="pillar.image"
                            class="relative aspect-16/10 w-full overflow-hidden sm:aspect-video"
                        >
                            <img
                                :src="pillar.image"
                                :alt="pillar.title"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-cover object-center transition duration-700 group-hover/card:scale-105"
                            />
                            <div
                                class="pointer-events-none absolute inset-0 bg-linear-to-t from-void-950 via-void-950/45 to-transparent"
                            ></div>
                            <div
                                class="scanlines pointer-events-none absolute inset-0 opacity-50"
                            ></div>
                            <!-- Ikon mengambang di sudut foto sebagai penanda pilar. -->
                            <span
                                class="depth-3 absolute bottom-3 left-6 flex h-14 w-14 items-center justify-center rounded-3xl border border-white/10 bg-linear-to-br text-2xl backdrop-blur-sm"
                                :class="accentOf(pillar.accent).icon"
                            >
                                {{ pillar.icon }}
                            </span>
                        </div>

                        <div
                            class="relative flex flex-1 flex-col p-8"
                            :class="{ 'pt-6': pillar.image }"
                        >
                            <!-- Ikon versi tanpa foto (tetap seperti sebelumnya). -->
                            <div v-if="!pillar.image" class="depth-3 relative">
                                <span
                                    class="flex h-16 w-16 items-center justify-center rounded-3xl border border-white/10 bg-linear-to-br text-3xl"
                                    :class="accentOf(pillar.accent).icon"
                                >
                                    {{ pillar.icon }}
                                </span>
                            </div>

                            <h3
                                class="depth-1 relative font-display text-xl font-bold text-slate-50"
                                :class="pillar.image ? 'mt-0' : 'mt-6'"
                            >
                                {{ pillar.title }}
                            </h3>

                            <p
                                class="relative mt-3 flex-1 text-sm leading-relaxed text-slate-300/80"
                            >
                                {{ pillar.description }}
                            </p>

                            <ul class="depth-1 relative mt-6 flex flex-wrap gap-2">
                                <li
                                    v-for="point in pillar.points"
                                    :key="point"
                                    class="rounded-full border px-3 py-1.5 text-xs font-bold"
                                    :class="accentOf(pillar.accent).chip"
                                >
                                    {{ point }}
                                </li>
                            </ul>
                        </div>
                    </article>
                </HoloTilt>
            </div>
        </div>
    </section>
</template>
