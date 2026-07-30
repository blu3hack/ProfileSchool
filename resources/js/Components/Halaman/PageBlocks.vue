<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import GalleryLightbox from '../Opsi3/GalleryLightbox.vue';
import { replay } from '../../directives/reveal';

/**
 * Merender isi halaman kustom mode "Visual Builder" — satu blok, satu bentuk
 * tampilan. Daftar jenis bloknya didefinisikan di App\Support\PageBlocks.
 *
 * Lebar kolom mengikuti kebiasaan halaman berita: teks dibatasi ±65 karakter
 * per baris agar nyaman dibaca, sementara gambar/galeri/kartu boleh melebar.
 */
const props = defineProps({
    blocks: { type: Array, default: () => [] },
});

/** Galeri yang sedang dibuka di lightbox + indeks fotonya. */
const lightbox = ref({ index: null, images: [] });

const openLightbox = (block, index) => {
    lightbox.value = {
        index,
        images: block.items.map((item) => ({
            src: item.src,
            title: '',
            caption: item.caption ?? '',
            alt: item.caption ?? '',
        })),
    };
};

const lightboxIndex = computed({
    get: () => lightbox.value.index,
    set: (value) => (lightbox.value = { ...lightbox.value, index: value }),
});

const gridClass = (columns) => ({
    2: 'sm:grid-cols-2',
    3: 'sm:grid-cols-2 lg:grid-cols-3',
    4: 'sm:grid-cols-2 lg:grid-cols-4',
}[columns] ?? 'sm:grid-cols-2 lg:grid-cols-3');

const imageClass = (width) => ({
    normal: 'max-w-3xl',
    wide: 'max-w-5xl',
    full: 'max-w-none',
}[width] ?? 'max-w-3xl');

/** Tautan dalam situs dilewatkan Inertia (tanpa muat ulang penuh). */
const isInternal = (href) => typeof href === 'string' && href.startsWith('/');
</script>

<template>
    <div class="space-y-10">
        <template v-for="(block, index) in props.blocks" :key="index">
            <!-- ===================== TEKS KAYA / KODE HTML ===================== -->
            <!-- `v-html` aman: isinya sudah disaring App\Support\HtmlSanitizer
                 saat disimpan dan sekali lagi saat dirender. -->
            <div v-if="block.type === 'richtext' || block.type === 'html'" v-reveal="replay({ from: 'fade' })"
                class="page-prose" :class="block.type === 'html' ? 'page-html' : 'max-w-3xl'"
                v-html="block.html"></div>

            <!-- ============================ SUB JUDUL ============================ -->
            <component :is="block.level === 'h3' ? 'h3' : 'h2'" v-else-if="block.type === 'heading'"
                v-reveal="replay({ from: 'fade' })" class="max-w-3xl scroll-mt-28 pt-4 font-display font-bold text-slate-50"
                :class="block.level === 'h3' ? 'text-lg sm:text-xl' : 'text-xl sm:text-2xl'">
                <span class="mr-3 inline-block h-4 w-1 rounded-full bg-aqua-400 align-middle" aria-hidden="true"></span>
                {{ block.text }}
            </component>

            <!-- ============================= GAMBAR ============================= -->
            <figure v-else-if="block.type === 'image'" v-reveal="replay({ from: 'zoom' })"
                class="holo-panel overflow-hidden rounded-[2rem]" :class="imageClass(block.width)">
                <img :src="block.src" :srcset="block.srcset || undefined" :alt="block.alt || ''" loading="lazy"
                    class="w-full object-cover">
                <figcaption v-if="block.caption" class="px-6 py-4 text-xs leading-relaxed text-slate-400">
                    {{ block.caption }}
                </figcaption>
            </figure>

            <!-- ============================= GALERI ============================= -->
            <div v-else-if="block.type === 'gallery'" v-reveal="replay({ from: 'fade' })"
                class="grid gap-4" :class="gridClass(block.columns)">
                <button v-for="(item, itemIndex) in block.items" :key="itemIndex" type="button"
                    class="holo-panel group relative overflow-hidden rounded-3xl text-left transition duration-300 hover:-translate-y-1"
                    @click="openLightbox(block, itemIndex)">
                    <img :src="item.src" :srcset="item.srcset || undefined"
                        sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw" :alt="item.caption || ''"
                        loading="lazy" class="h-56 w-full object-cover transition duration-500 group-hover:scale-105">
                    <span v-if="item.caption"
                        class="block px-5 py-3 text-xs leading-relaxed text-slate-300/85">{{ item.caption }}</span>
                </button>
            </div>

            <!-- ============================ KUTIPAN ============================ -->
            <figure v-else-if="block.type === 'quote'" v-reveal="replay({ from: 'left' })"
                class="holo-panel relative max-w-3xl overflow-hidden rounded-[1.75rem] p-8">
                <div class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10"></div>
                <blockquote class="relative font-display text-lg leading-relaxed text-slate-100 sm:text-xl">
                    &ldquo;{{ block.text }}&rdquo;
                </blockquote>
                <figcaption v-if="block.cite"
                    class="relative mt-4 text-xs font-bold uppercase tracking-[0.18em] text-aqua-300">
                    {{ block.cite }}
                </figcaption>
            </figure>

            <!-- ======================== SEMATAN VIDEO/URL ======================== -->
            <figure v-else-if="block.type === 'embed'" v-reveal="replay({ from: 'fade' })" class="max-w-4xl">
                <div v-if="block.src" class="holo-panel overflow-hidden rounded-[1.75rem]">
                    <div class="aspect-video w-full">
                        <iframe :src="block.src" :title="block.title || 'Sematan'" loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen class="h-full w-full border-0"></iframe>
                    </div>
                </div>

                <!-- Tautan yang tidak bisa disematkan tetap berguna sebagai
                     tautan biasa, bukan kotak error. -->
                <a v-else-if="block.url" :href="block.url" target="_blank" rel="noopener noreferrer"
                    class="holo-panel flex items-center gap-3 rounded-3xl px-6 py-5 text-sm font-semibold text-slate-100 transition hover:text-aqua-200">
                    <span aria-hidden="true">🔗</span>
                    {{ block.title || block.url }}
                </a>

                <figcaption v-if="block.caption" class="mt-3 text-xs leading-relaxed text-slate-400">
                    {{ block.caption }}
                </figcaption>
            </figure>

            <!-- ========================= TOMBOL AJAKAN ========================= -->
            <div v-else-if="block.type === 'cta'" v-reveal="replay({ from: 'up' })"
                class="holo-panel relative max-w-3xl overflow-hidden rounded-[1.75rem] p-8">
                <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-aqua-500/25 blur-3xl">
                </div>
                <p v-if="block.title" class="relative font-display text-xl font-bold text-slate-50">
                    {{ block.title }}
                </p>
                <p v-if="block.description" class="relative mt-3 text-sm leading-relaxed text-slate-300/85">
                    {{ block.description }}
                </p>

                <div v-if="block.label && block.href" class="relative mt-6">
                    <Link v-if="isInternal(block.href)" :href="block.href"
                        class="inline-block rounded-full px-7 py-3 text-sm font-bold transition duration-300 hover:-translate-y-0.5"
                        :class="block.style === 'ghost'
                            ? 'border border-aqua-400/50 text-aqua-200 hover:bg-aqua-400/10'
                            : 'bg-[color:var(--cta-via)] bg-linear-to-r from-[color:var(--cta-from)] via-[color:var(--cta-via)] to-[color:var(--cta-to)] text-[color:var(--cta-ink)] shadow-[0_0_28px_-8px_var(--cta-glow)]'">
                        {{ block.label }}
                    </Link>
                    <a v-else :href="block.href" target="_blank" rel="noopener noreferrer"
                        class="inline-block rounded-full px-7 py-3 text-sm font-bold transition duration-300 hover:-translate-y-0.5"
                        :class="block.style === 'ghost'
                            ? 'border border-aqua-400/50 text-aqua-200 hover:bg-aqua-400/10'
                            : 'bg-[color:var(--cta-via)] bg-linear-to-r from-[color:var(--cta-from)] via-[color:var(--cta-via)] to-[color:var(--cta-to)] text-[color:var(--cta-ink)] shadow-[0_0_28px_-8px_var(--cta-glow)]'">
                        {{ block.label }}
                    </a>
                </div>
            </div>

            <!-- ========================== GRID / KARTU ========================== -->
            <div v-else-if="block.type === 'cards'" v-reveal="replay({ from: 'fade' })" class="grid gap-5"
                :class="gridClass(block.columns)">
                <component v-for="(item, itemIndex) in block.items" :key="itemIndex"
                    :is="item.href ? (isInternal(item.href) ? Link : 'a') : 'div'"
                    :href="item.href || undefined"
                    :target="item.href && !isInternal(item.href) ? '_blank' : undefined"
                    :rel="item.href && !isInternal(item.href) ? 'noopener noreferrer' : undefined"
                    class="holo-panel flex flex-col overflow-hidden rounded-3xl transition duration-300"
                    :class="item.href ? 'hover:-translate-y-1 hover:text-aqua-200' : ''">
                    <img v-if="item.image" :src="item.image" :alt="item.title || ''" loading="lazy"
                        class="h-40 w-full object-cover">
                    <div class="flex flex-1 flex-col p-6">
                        <span v-if="item.icon" class="text-2xl" aria-hidden="true">{{ item.icon }}</span>
                        <p v-if="item.title" class="mt-3 font-display font-bold text-slate-50">{{ item.title }}</p>
                        <p v-if="item.description" class="mt-2 text-sm leading-relaxed text-slate-300/80">
                            {{ item.description }}
                        </p>
                        <span v-if="item.href" class="mt-4 text-xs font-bold uppercase tracking-[0.18em] text-aqua-300">
                            Buka &rarr;
                        </span>
                    </div>
                </component>
            </div>
        </template>

        <GalleryLightbox v-model:index="lightboxIndex" :images="lightbox.images" />
    </div>
</template>
