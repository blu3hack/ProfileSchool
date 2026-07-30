<script setup>
import { defineAsyncComponent } from "vue";

import DeferVisible from "../Opsi3/DeferVisible.vue";
import { replay } from "../../directives/reveal";
import { usePageText } from "../../lib/page-text";
import { refreshScrollTriggers } from "../../lib/smooth-scroll";

/**
 * Section "Kegiatan" (#kegiatan) — deskripsi di kiri, tumpukan kartu 3D
 * keseharian siswa di kanan.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    items: { type: Array, default: () => [] },
});

const { text } = usePageText(() => props.content);

/**
 * Pemakai Swiper kedua di beranda (satunya lagi NeonCoverflow di section
 * berita). Sama seperti di sana: berkasnya berat, letaknya jauh di bawah layar
 * pertama, jadi baru diunduh ketika <DeferVisible> membuat isinya.
 */
const ActivityDeck = defineAsyncComponent(
    () => import("../Opsi3/ActivityDeck.vue"),
);
</script>

<template>
    <section
        id="kegiatan"
        class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32"
    >
        <div class="container-page">
            <div class="grid items-center gap-14 lg:grid-cols-12">
                <!-- Deskripsi kiri -->
                <div class="lg:col-span-5">
                    <p
                        v-reveal="replay({ from: 'fade' })"
                        class="text-xs font-bold uppercase tracking-[0.24em] text-plasma-300"
                    >
                        {{ text("activities_eyebrow", "Keseharian Siswa") }}
                    </p>
                    <h2
                        v-reveal="replay({ delay: 0.1 })"
                        class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                    >
                        {{ text("activities_title", "Kegiatan yang") }}
                        <span class="text-gradient-neon">
                            {{
                                text(
                                    "activities_title_highlight",
                                    "Menumbuhkan",
                                )
                            }}
                        </span>
                    </h2>
                    <p
                        v-reveal="replay({ delay: 0.2 })"
                        class="mt-5 text-base leading-relaxed text-slate-300/80"
                    >
                        {{
                            text(
                                "activities_description",
                                "Setiap kegiatan dirancang menyeimbangkan ruhiyah, nalar, dan kebugaran ananda.",
                            )
                        }}
                    </p>
                </div>

                <!-- Kartu 3D kanan -->
                <div
                    v-reveal="replay({ from: 'zoom', delay: 0.2 })"
                    class="flex justify-center lg:col-span-7 lg:justify-end"
                >
                    <div v-parallax="{ y: 60, speed: 0.6 }" class="relative">
                        <!-- Pendar latar. Sengaja gradient, bukan `blur-3xl`: elemen
                             ini ikut bergerak mengikuti gulir, dan mengaburkan ulang
                             bidang sebesar ini tiap frame jauh lebih mahal daripada
                             menggambar gradasi yang sudah lembut sejak awal. -->
                        <div
                            class="pointer-events-none absolute -inset-10 rounded-[3rem]"
                            style="
                                background:
                                    radial-gradient(
                                        60% 60% at 28% 22%,
                                        rgba(15, 195, 221, 0.3),
                                        transparent 70%
                                    ),
                                    radial-gradient(
                                        60% 60% at 78% 80%,
                                        rgba(233, 48, 177, 0.24),
                                        transparent 70%
                                    ),
                                    radial-gradient(
                                        70% 70% at 50% 50%,
                                        rgba(139, 77, 255, 0.22),
                                        transparent 72%
                                    );
                            "
                        ></div>
                        <DeferVisible min-height="25rem">
                            <ActivityDeck
                                :items="props.items"
                                @vue:mounted="refreshScrollTriggers"
                            />
                        </DeferVisible>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
