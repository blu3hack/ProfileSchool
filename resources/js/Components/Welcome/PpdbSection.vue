<script setup>
import { replay } from "../../directives/reveal";
import { WHATSAPP_URL } from "../../lib/contact";
import { usePageText } from "../../lib/page-text";

/**
 * Section "PPDB" (#ppdb) — panel ajakan mendaftar, tujuan tombol CTA hero
 * dan tombol "Daftar" di navbar.
 */
const props = defineProps({
    /** Seluruh teks halaman; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
});

/** Tombol "Konsultasi Dulu" menggulir ke footer — halaman yang mengerjakannya. */
const emit = defineEmits(["scroll"]);

const { text } = usePageText(() => props.content);
</script>

<template>
    <section id="ppdb" class="relative scroll-mt-28 pb-28">
        <div class="container-page">
            <div
                v-reveal="replay({ from: 'zoom' })"
                class="scan-sweep relative overflow-hidden rounded-[2.5rem] border border-aqua-400/25 bg-linear-to-br from-void-800 via-void-900 to-void-950 px-8 py-16 text-center shadow-[0_0_80px_-30px_rgba(52,226,245,0.8)] sm:px-16"
            >
                <div
                    class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10"
                ></div>
                <div
                    class="cyber-grid pointer-events-none absolute inset-0 opacity-40"
                ></div>
                <!-- Pendar latar. Gradient, BUKAN `blur-3xl`: pembungkusnya
                     dianimasikan `zoom` (skala) oleh v-reveal, dan `filter: blur()`
                     harus di-raster ulang tiap kali skala induknya berubah —
                     satu hentakan penuh tiap section ini masuk layar. Karena
                     `replay()` memakai `once: false`, itu terjadi berulang kali.
                     Radial gradient cukup digambar sekali lalu diskalakan
                     compositor. Lihat `.orb-glow` di app.css. -->
                <div
                    class="orb-glow pointer-events-none absolute -left-24 -top-24 h-72 w-72"
                    style="--orb-color: rgba(15, 195, 221, 0.3)"
                ></div>
                <div
                    class="orb-glow pointer-events-none absolute -bottom-28 -right-20 h-80 w-80"
                    style="--orb-color: rgba(233, 48, 177, 0.24)"
                ></div>

                <div class="relative">
                    <span
                        class="holo-panel-lite inline-block rounded-full px-5 py-2 text-xs font-bold text-aqua-200"
                    >
                        {{ text("ppdb_badge", "PPDB Tahun Ajaran 2026/2027") }}
                    </span>

                    <h2
                        class="mt-7 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl"
                    >
                        {{ text("ppdb_title", "Mari Wujudkan Masa Depan") }}
                        <span class="block text-gradient-neon">
                            {{
                                text(
                                    "ppdb_title_highlight",
                                    "Terbaik untuk Ananda",
                                )
                            }}
                        </span>
                    </h2>

                    <p
                        class="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-slate-300/80 sm:text-base"
                    >
                        {{
                            text(
                                "ppdb_description",
                                "Pendaftaran gelombang II telah dibuka dengan kuota terbatas.",
                            )
                        }}
                    </p>

                    <div
                        class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <a
                            :href="text('ppdb_primary_href', WHATSAPP_URL)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-full rounded-full bg-aqua-500 bg-linear-to-r from-aqua-400 to-volt-400 px-8 py-4 text-sm font-bold text-void-950 shadow-[0_0_34px_-6px_rgba(52,226,245,0.9)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_48px_-4px_rgba(169,123,255,0.95)] sm:w-auto"
                        >
                            {{ text("ppdb_primary_label", "Daftar via WhatsApp") }}
                        </a>
                        <a
                            href="#kontak"
                            class="w-full rounded-full border border-aqua-400/30 px-8 py-4 text-sm font-bold text-slate-100 transition duration-300 hover:-translate-y-0.5 hover:border-aqua-400/70 hover:bg-aqua-400/10 sm:w-auto"
                            @click.prevent="emit('scroll', '#kontak')"
                        >
                            {{ text("ppdb_secondary_label", "Konsultasi Dulu") }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
