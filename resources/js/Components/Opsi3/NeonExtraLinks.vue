<script setup>
import { linkKind } from '../../lib/links';
import { newsAccent } from '../../lib/news-accent';
import SmartLink from './SmartLink.vue';

/**
 * Section "Menu & Layanan Lainnya" — etalase seluruh menu tambahan bikinan
 * admin, tepat di atas footer.
 *
 * Isinya sumber yang sama persis dengan dropdown "Lainnya" di navbar
 * (`custom_links`): dropdown untuk yang sudah tahu mau ke mana, section ini
 * untuk yang sedang menyusuri halaman sampai bawah. Menambah satu menu di
 * panel admin otomatis menambah keduanya.
 */
const props = defineProps({
    items: { type: Array, default: () => [] },
    /** Teks pembungkus section; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
});

const text = (key, fallback = '') => props.content?.[key] || fallback;

/** Animasi masuk diulang tiap section kembali terlihat — seragam dengan halaman. */
const replay = (options = {}) => ({ once: false, ...options });

const accentOf = (name) => newsAccent(name);

/** Tautan keluar situs diberi tanda panah serong sebagai peringatan tab baru. */
const isExternal = (href) => linkKind(href) === 'external';
</script>

<template>
    <section id="menu-tambahan" class="relative scroll-mt-28 overflow-hidden py-24 sm:py-32">
        <div v-parallax="{ y: 110, speed: -0.9 }" style="--orb-color: rgba(139, 77, 255, 0.24)"
            class="orb-glow pointer-events-none absolute -left-28 top-16 h-80 w-80"></div>
        <div v-parallax="{ y: 90, speed: 0.7, rotate: -16 }"
            class="pointer-events-none absolute -right-16 bottom-20 h-56 w-56 rotate-[22.5deg] rounded-[4rem] border border-aqua-400/20">
        </div>

        <div class="container-page relative">
            <div class="mx-auto max-w-2xl text-center">
                <p v-reveal="replay({ from: 'fade' })"
                    class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">
                    {{ text('extras_eyebrow', 'Akses Cepat') }}
                </p>
                <h2 v-reveal="replay({ delay: 0.1 })"
                    class="mt-4 font-display text-3xl font-extrabold leading-tight text-slate-50 sm:text-5xl">
                    {{ text('extras_title', 'Menu &') }}
                    <span class="text-gradient-neon">{{ text('extras_title_highlight', 'Layanan Lainnya') }}</span>
                </h2>
                <p v-reveal="replay({ delay: 0.2 })" class="mt-4 text-base leading-relaxed text-slate-300/80">
                    {{ text('extras_description', 'Tautan penting lain seputar layanan sekolah.') }}
                </p>
            </div>

            <div class="mt-16 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- `id` per kartu = tujuan gulir dari dropdown navbar
                     (lihat anchorOf di NeonNavbar). `scroll-mt-28` menjaga
                     kartunya tidak berhenti tepat di balik navbar. -->
                <SmartLink v-for="(item, index) in props.items" :key="item.id ?? item.href"
                    :id="item.id ? `menu-tambahan-${item.id}` : null"
                    v-reveal="replay({ from: 'up', delay: 0.06 * index })" :href="item.href"
                    class="holo-panel holo-edge group relative flex h-full scroll-mt-28 flex-col overflow-hidden rounded-[2rem] transition duration-500 hover:-translate-y-1"
                    :class="accentOf(item.accent).glow">
                    <div class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10"></div>
                    <div class="scanlines pointer-events-none absolute inset-0 opacity-30"></div>

                    <!-- Gambar kartu opsional; bila kosong, ikon emoji di bawah
                         yang jadi penanda visualnya. -->
                    <div v-if="item.image" class="relative aspect-video w-full overflow-hidden">
                        <img :src="item.image" :alt="item.label" loading="lazy" decoding="async"
                            class="h-full w-full object-cover object-center transition duration-700 group-hover:scale-105">
                        <div
                            class="pointer-events-none absolute inset-0 bg-linear-to-t from-void-950 via-void-950/40 to-transparent">
                        </div>
                    </div>

                    <div class="relative flex flex-1 flex-col p-7">
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-2xl border text-xl transition duration-500 group-hover:scale-110"
                            :class="accentOf(item.accent).chip">
                            {{ item.icon }}
                        </span>

                        <h3 class="mt-5 font-display text-lg font-bold text-slate-50 transition duration-300"
                            :class="accentOf(item.accent).hoverTitle">
                            {{ item.label }}
                        </h3>

                        <p v-if="item.description" class="mt-2.5 flex-1 text-sm leading-relaxed text-slate-300/80">
                            {{ item.description }}
                        </p>

                        <span
                            class="mt-6 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                            :class="accentOf(item.accent).link">
                            Buka
                            <span aria-hidden="true">{{ isExternal(item.href) ? '↗' : '→' }}</span>
                        </span>
                    </div>
                </SmartLink>
            </div>
        </div>
    </section>
</template>
