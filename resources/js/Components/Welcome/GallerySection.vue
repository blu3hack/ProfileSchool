<script setup>
import { Link } from "@inertiajs/vue3";

import GalleryCarousel from "../Opsi3/GalleryCarousel.vue";
import { replay } from "../../directives/reveal";
import { usePageText } from "../../lib/page-text";

/**
 * Section "Galeri" (#galeri) — potret keseharian sekolah dalam carousel dua
 * baris. Seluruh section hilang sendiri selama belum ada satu foto pun.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    images: { type: Array, default: () => [] },
});

/**
 * Popupnya tidak tinggal di sini: satu <GalleryLightbox> milik halaman
 * dipakai bersama seluruh section yang menampilkan galeri, jadi section ini
 * cukup mengabarkan indeks foto yang diklik.
 */
const emit = defineEmits(["open"]);

const { text } = usePageText(() => props.content);
</script>

<template>
    <section
        v-if="props.images.length"
        id="galeri"
        class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32"
    >
        <div
            v-parallax="{ y: 90, speed: -0.9 }"
            style="--orb-color: rgba(233, 48, 177, 0.22)"
            class="orb-glow pointer-events-none absolute -left-24 top-24 h-80 w-80"
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
                        {{ text("gallery_eyebrow", "Galeri Sekolah") }}
                    </p>
                    <h2
                        v-reveal="replay({ delay: 0.1 })"
                        class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                    >
                        {{ text("gallery_title", "Potret Keseharian") }}
                        <span class="text-gradient-neon">
                            {{
                                text("gallery_title_highlight", "Profil Sekolah")
                            }}
                        </span>
                    </h2>
                    <p
                        v-reveal="replay({ delay: 0.2 })"
                        class="mt-4 text-base leading-relaxed text-slate-300/80"
                    >
                        {{
                            text(
                                "gallery_description",
                                "Sekilas suasana gedung, kegiatan, dan momen berharga di lingkungan sekolah. Klik foto untuk melihatnya lebih besar.",
                            )
                        }}
                    </p>
                </div>

                <Link
                    v-reveal="replay({ from: 'left', delay: 0.2 })"
                    href="/galeri"
                    class="holo-panel inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:text-aqua-200"
                >
                    {{ text("gallery_cta", "Lihat Semua") }}
                    <span aria-hidden="true">&rarr;</span>
                </Link>
            </div>

            <div v-reveal="replay({ from: 'fade', delay: 0.25 })" class="mt-14">
                <GalleryCarousel
                    :images="props.images"
                    @open="emit('open', $event)"
                />
            </div>
        </div>
    </section>
</template>
