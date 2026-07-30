<script setup>
import { defineAsyncComponent } from "vue";
import { Link } from "@inertiajs/vue3";

import DeferVisible from "../Opsi3/DeferVisible.vue";
import { replay } from "../../directives/reveal";
import { usePageText } from "../../lib/page-text";
import { refreshScrollTriggers } from "../../lib/smooth-scroll";

/**
 * Section "Berita" (#berita) — enam berita terbaru dalam slider coverflow.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    items: { type: Array, default: () => [] },
});

const { text } = usePageText(() => props.content);

/**
 * Slider ini salah satu dari dua pemakai Swiper di beranda (satunya lagi
 * ActivityDeck) — bersama CSS-nya ±40 KB gzip, dulu sekitar empat perlima
 * berkas halaman beranda. Letaknya jauh di bawah layar pertama, jadi dimuat
 * terpisah dan baru diunduh ketika <DeferVisible> membuat isinya.
 */
const NeonCoverflow = defineAsyncComponent(
    () => import("../Opsi3/NeonCoverflow.vue"),
);
</script>

<template>
    <section
        id="berita"
        class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32"
    >
        <!-- Ornamen berlapis paraks: bergerak lebih lambat dari konten. -->
        <div
            v-parallax="{ y: 140, speed: 1.2 }"
            style="--orb-color: rgba(15, 195, 221, 0.28)"
            class="orb-glow pointer-events-none absolute -left-32 top-10 h-80 w-80"
        ></div>
        <div
            v-parallax="{ y: 120, speed: -0.8, rotate: 20 }"
            class="pointer-events-none absolute -right-24 top-40 h-64 w-64 rotate-[22.5deg] rounded-[4rem] border border-plasma-400/30"
        ></div>

        <div class="container-page relative">
            <div
                class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="max-w-xl">
                    <p
                        v-reveal="replay({ from: 'fade' })"
                        class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300"
                    >
                        {{ text("news_eyebrow", "Kabar Sekolah") }}
                    </p>
                    <h2
                        v-reveal="replay({ delay: 0.1 })"
                        class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                    >
                        {{ text("news_title", "Berita Terbaru") }}
                    </h2>
                    <p
                        v-reveal="replay({ delay: 0.2 })"
                        class="mt-4 text-base leading-relaxed text-slate-300/80"
                    >
                        {{
                            text(
                                "news_description",
                                "Momen, capaian, dan pengumuman terkini dari lingkungan sekolah.",
                            )
                        }}
                    </p>
                </div>

                <Link
                    v-reveal="replay({ from: 'left', delay: 0.2 })"
                    href="/berita"
                    class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200"
                >
                    {{ text("news_cta", "Lihat Semua Berita") }}
                    <span aria-hidden="true">&rarr;</span>
                </Link>
            </div>

            <div v-reveal="replay({ from: 'fade', delay: 0.25 })" class="mt-14">
                <DeferVisible min-height="32rem">
                    <NeonCoverflow
                        :items="props.items"
                        @vue:mounted="refreshScrollTriggers"
                    />
                </DeferVisible>
            </div>
        </div>
    </section>
</template>
