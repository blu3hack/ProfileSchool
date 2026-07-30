<script setup>
import NeonTimeline from "../Opsi3/NeonTimeline.vue";
import { replay } from "../../directives/reveal";
import { usePageText } from "../../lib/page-text";

/**
 * Section "Prestasi" (#prestasi) — jejak capaian siswa dalam bentuk linimasa.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
    items: { type: Array, default: () => [] },
});

const { text } = usePageText(() => props.content);
</script>

<template>
    <section
        id="prestasi"
        class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32"
    >
        <div
            v-parallax="{ y: 100, speed: -1 }"
            style="--orb-color: rgba(255, 199, 61, 0.18)"
            class="orb-glow pointer-events-none absolute right-0 top-24 h-96 w-96"
        ></div>

        <div class="container-page relative">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    v-reveal="replay({ from: 'fade' })"
                    class="text-xs font-bold uppercase tracking-[0.24em] text-solar-300"
                >
                    {{ text("achievements_eyebrow", "Pencapaian") }}
                </p>
                <h2
                    v-reveal="replay({ delay: 0.1 })"
                    class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                >
                    {{ text("achievements_title", "Jejak Prestasi") }}
                    <span class="text-gradient-neon">
                        {{
                            text(
                                "achievements_title_highlight",
                                "Siswa SD & SMP",
                            )
                        }}
                    </span>
                </h2>
                <p
                    v-reveal="replay({ delay: 0.2 })"
                    class="mt-4 text-base leading-relaxed text-slate-300/80"
                >
                    {{
                        text(
                            "achievements_description",
                            "Buah dari proses belajar yang konsisten.",
                        )
                    }}
                </p>
            </div>

            <div class="mt-20">
                <NeonTimeline :items="props.items" />
            </div>
        </div>
    </section>
</template>
