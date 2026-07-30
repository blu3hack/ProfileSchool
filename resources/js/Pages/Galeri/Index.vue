<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import NeonNavbar from '../../Components/Opsi3/NeonNavbar.vue';
import NeonFooter from '../../Components/Opsi3/NeonFooter.vue';
import GalleryLightbox from '../../Components/Opsi3/GalleryLightbox.vue';
import { useTheme } from '../../lib/theme';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    navLinks: { type: Array, default: () => [] },
    /** Teks halaman yang bisa diedit admin (dipakai footer). */
    content: { type: Object, default: () => ({}) },
    /** Seluruh foto galeri aktif, terurut sesuai pengaturan admin. */
    images: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

const { theme } = useTheme();

const replay = (options = {}) => ({ once: false, ...options });

/** Indeks foto yang sedang dibuka di popup; null = tertutup. */
const lightboxIndex = ref(null);
const openLightbox = (index) => (lightboxIndex.value = index);
</script>

<template>
    <Head title="Galeri Sekolah" />

    <div :data-theme="theme" class="void-bg min-h-screen overflow-x-clip font-sans text-slate-200">
        <NeonNavbar :school-name="props.schoolName" :links="props.navLinks"
            :content="props.content" active="#galeri" />

        <main class="relative z-10">
            <!-- =========================== HEADER =========================== -->
            <section class="relative isolate overflow-hidden pb-12 pt-36 sm:pt-44">
                <div class="pointer-events-none absolute inset-0 -z-10">
                    <div class="cyber-grid absolute inset-0 opacity-40"></div>
                    <div class="scanlines absolute inset-0 opacity-50"></div>
                </div>
                <div v-parallax="{ y: 120, speed: 1.1 }" style="--orb-color: rgba(15, 195, 221, 0.28)"
                    class="orb-glow pointer-events-none absolute -left-32 top-10 -z-10 h-80 w-80">
                </div>
                <div v-parallax="{ y: 100, speed: -0.8, rotate: 18 }"
                    class="pointer-events-none absolute -right-24 top-28 -z-10 h-64 w-64 rotate-[22.5deg] rounded-[4rem] border border-plasma-400/30">
                </div>

                <div class="container-page relative">
                    <!-- Remah roti: jalan pulang yang selalu terlihat. -->
                    <nav v-reveal="replay({ from: 'fade' })" class="flex items-center gap-2 text-xs text-slate-400"
                        aria-label="Remah roti">
                        <Link href="/" class="transition hover:text-aqua-300">Beranda</Link>
                        <span aria-hidden="true">/</span>
                        <span class="font-semibold text-aqua-300">Galeri</span>
                    </nav>

                    <div class="mt-6 grid gap-8 lg:grid-cols-12 lg:items-end">
                        <div class="lg:col-span-7">
                            <p v-reveal="replay({ from: 'fade' })"
                                class="text-xs font-bold uppercase tracking-[0.24em] text-aqua-300">
                                Galeri Sekolah
                            </p>
                            <h1 v-reveal="replay({ delay: 0.1 })"
                                class="mt-4 font-display text-4xl font-extrabold leading-tight text-slate-50 sm:text-6xl">
                                Potret Keseharian
                                <span class="text-gradient-neon">Profil Sekolah</span>
                            </h1>
                        </div>
                        <p v-reveal="replay({ from: 'left', delay: 0.2 })"
                            class="text-base leading-relaxed text-slate-300/80 lg:col-span-5">
                            Kumpulan foto gedung, fasilitas, kegiatan, dan momen berharga di lingkungan
                            {{ props.schoolName }}. Klik salah satu foto untuk melihatnya lebih besar beserta
                            keterangannya.
                        </p>
                    </div>
                </div>
            </section>

            <!-- ========================= GRID GALERI ========================= -->
            <section class="relative pb-28 pt-6">
                <div class="container-page">
                    <div v-if="props.images.length"
                        class="columns-1 gap-5 sm:columns-2 lg:columns-3 [&>*]:mb-5">
                        <button v-for="(image, index) in props.images" :key="index" type="button"
                            v-reveal="replay({ from: 'up', delay: 0.05 * (index % 3) })"
                            class="group relative block w-full break-inside-avoid overflow-hidden rounded-[1.5rem] border border-white/10 bg-void-900 transition duration-300 hover:border-aqua-400/50 hover:shadow-[0_0_40px_-12px_rgba(52,226,245,0.7)]"
                            :aria-label="`Buka foto: ${image.title}`" @click="openLightbox(index)">
                            <img :src="image.src" :srcset="image.srcset"
                                sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                                :alt="image.alt || image.title" loading="lazy" decoding="async"
                                class="w-full object-cover opacity-90 transition duration-500 group-hover:scale-105 group-hover:opacity-100">
                            <div class="scanlines pointer-events-none absolute inset-0 opacity-25"></div>
                            <!-- Judul & aksi muncul dari bawah saat hover. -->
                            <div class="absolute inset-x-0 bottom-0 translate-y-2 bg-linear-to-t from-void-950/95 via-void-950/40 to-transparent px-5 pb-4 pt-10 opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                                <p class="line-clamp-1 font-display text-base font-bold text-slate-50">
                                    {{ image.title }}
                                </p>
                                <p v-if="image.caption" class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-300/80">
                                    {{ image.caption }}
                                </p>
                            </div>
                            <span class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-void-950/60 text-sm text-aqua-200 opacity-0 transition duration-300 group-hover:opacity-100"
                                aria-hidden="true">⤢</span>
                        </button>
                    </div>

                    <!-- Keadaan kosong: tetap beri kabar, jangan biarkan bolong. -->
                    <div v-else class="holo-panel rounded-[2rem] px-8 py-16 text-center">
                        <p class="text-4xl" aria-hidden="true">📷</p>
                        <p class="mt-4 font-display text-lg font-bold text-slate-100">
                            Belum ada foto di galeri
                        </p>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-400">
                            Foto akan tampil di sini setelah admin menambahkannya lewat panel.
                        </p>
                        <Link href="/"
                            class="mt-7 inline-block rounded-full border border-aqua-400/40 px-6 py-3 text-sm font-bold text-aqua-200 transition duration-300 hover:-translate-y-0.5 hover:border-aqua-400/80 hover:bg-aqua-400/10">
                            Kembali ke Beranda
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <NeonFooter :school-name="props.schoolName" :links="props.navLinks" :contacts="props.contacts"
            :socials="props.socials" :content="props.content" />

        <!-- Popup foto galeri. -->
        <GalleryLightbox v-model:index="lightboxIndex" :images="props.images" />
    </div>
</template>
