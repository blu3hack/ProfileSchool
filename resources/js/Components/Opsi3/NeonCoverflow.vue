<script setup>
import { Link } from '@inertiajs/vue3';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { A11y, Autoplay, Keyboard, Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import HoloTilt from './HoloTilt.vue';
import { newsAccent } from '../../lib/news-accent';
import { useAutoplayInView } from '../../lib/swiper-autoplay';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const modules = [A11y, Autoplay, Keyboard, Navigation, Pagination];

/** Peta aksen → kelas neon dipakai bersama kartu berita di halaman lain. */
const accentOf = (name) => newsAccent(name);

/**
 * KENAPA BUKAN EffectCoverflow LAGI.
 *
 * Dulu slider ini memakai `effect="coverflow"` (rotate 46°, depth 260px) dan
 * kartunya praktis tidak bisa diklik. Penyebabnya rantai 3D yang dibangun efek
 * itu: `.swiper` diberi `perspective: 1200px`, `.swiper-wrapper` dan tiap
 * `.swiper-slide` diberi `transform-style: preserve-3d`, lalu tiap slide
 * ditulisi `translate3d(...) rotateY(...)` di setiap frame.
 *
 * Kartunya TERGAMBAR di tempat yang benar, tapi hit-test-nya tidak ikut masuk
 * ke dalam subtree 3D itu. Diperiksa dengan `document.elementFromPoint()` tepat
 * di tengah tiap kartu, yang terambil selalu `.swiper-wrapper` — bukan <a>-nya
 * — termasuk pada slide aktif yang transform-nya identitas. Jadi ini bukan soal
 * slide yang saling menimpa atau area klik yang bergeser: seluruh isi slide
 * memang tidak terjangkau pointer.
 *
 * Kemiringan khas coverflow-nya tetap ada, tapi digambar sendiri di blok
 * <style> dengan FUNGSI `perspective()` di dalam `transform` masing-masing
 * slide — bukan PROPERTI `perspective` di elemen leluhur. Bedanya menentukan:
 * properti itu (berpasangan dengan `preserve-3d`) membuat satu ruang 3D yang
 * dihuni bersama semua slide, dan ruang itulah yang tidak bisa ditembus
 * hit-test. Fungsi `perspective()` berlaku hanya untuk transform elemen itu
 * sendiri; slide-nya tetap rata terhadap induknya, jadi kliknya dihitung lewat
 * jalur biasa.
 *
 * Kalau suatu saat ada yang ingin menghidupkan `effect="coverflow"` lagi: klik
 * kartu WAJIB diuji ulang di browser sungguhan lebih dulu. Tidak ada tes yang
 * menangkap kegagalan ini, dan tampilannya tetap terlihat normal saat rusak.
 */
const { onSwiper } = useAutoplayInView();

const breakpoints = {
    640: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
};

const hasLoop = props.items.length > 3;

/** Tujuan kartu: halaman detail berita. */
const linkOf = (item) => item.href ?? (item.slug ? `/berita/${item.slug}` : '/berita');

/**
 * Jarak geser (px) yang masih dianggap ketukan, bukan usapan.
 *
 * Swiper sudah punya penjaga serupa (`preventClicks`), tapi di sini justru
 * penjaga itulah yang membuat kartu terasa mati. Alurnya: begitu pointer
 * bergeser lebih dari `threshold` (bawaan 5px) sambil ditekan, Swiper menandai
 * `allowClick = false`, lalu membatalkan event click lewat `preventDefault()`
 * DI FASE CAPTURE pada elemen `.swiper`. Untuk <a> biasa itu tepat — navigasi
 * bawaan browser ikut batal. Tapi <Link> Inertia memeriksa `defaultPrevented`
 * sebelum bekerja, jadi klik yang sudah dibatalkan Swiper tidak dipulihkan
 * siapa pun: browser diam, Inertia diam, kartu seolah bukan tautan.
 *
 * Lima piksel itu sangat mudah terlampaui — jari yang bergeser sedikit saat
 * mengetuk, atau kursor yang masih bergerak saat mengklik (dan tilt kartu
 * justru mengundang kursor bergerak). Itulah kenapa kartu hanya sesekali mau
 * terbuka. Jadi `prevent-clicks` dimatikan dan penjaganya dipindah ke sini
 * dengan ambang yang lebih longgar.
 */
const DRAG_SLOP = 10;

/**
 * Posisi pointer saat tombol ditekan pada sebuah slide. `null` berarti belum
 * ada tekanan yang menunggu klik.
 */
let pressedAt = null;

const onSlidePointerDown = (event) => {
    pressedAt = { x: event.clientX, y: event.clientY };
};

/**
 * Dipasang di fase capture pada slide, jadi sempat berjalan sebelum <Link>
 * Inertia menangani klik yang sama.
 */
const onSlideClick = (event) => {
    const from = pressedAt;

    pressedAt = null;

    // Enter di papan tombol juga memicu click, tapi tanpa koordinat pointer
    // (`detail` 0) — jangan sampai dibaca sebagai usapan sejauh ratusan piksel.
    if (event.detail === 0 || !from) {
        return;
    }

    if (Math.hypot(event.clientX - from.x, event.clientY - from.y) > DRAG_SLOP) {
        event.preventDefault();
    }
};
</script>

<template>
    <Swiper :modules="modules" :slides-per-view="1.15" :space-between="24" :breakpoints="breakpoints"
        :centered-slides="true" :loop="hasLoop" grab-cursor
        :keyboard="{ enabled: true }"
        :autoplay="{ delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }"
        :pagination="{ clickable: true }" navigation
        :prevent-clicks="false" :prevent-clicks-propagation="false"
        class="neon-swiper pb-20!" @swiper="onSwiper">
        <SwiperSlide v-for="item in props.items" :key="item.title" class="h-auto!"
            @pointerdown="onSlidePointerDown" @click.capture="onSlideClick">
            <HoloTilt class="h-full" :max="10" :lift="26" :glare-color="accentOf(item.accent).glare">
                <!-- Seluruh kartu jadi tautan: klik di mana pun membuka detail berita. -->
                <Link :href="linkOf(item)"
                    class="holo-panel holo-edge group flex h-full flex-col overflow-hidden rounded-[1.75rem] transition-shadow duration-500"
                    :class="accentOf(item.accent).glow" :aria-label="`Baca berita: ${item.title}`">
                    <!-- Placeholder media: ganti dengan <img> ketika aset tersedia. -->
                    <div class="relative flex h-44 items-center justify-center overflow-hidden bg-linear-to-br"
                        :class="accentOf(item.accent).media">
                        <div class="pattern-lattice-neon absolute inset-0 opacity-25"></div>
                        <div class="scanlines absolute inset-0 opacity-60"></div>

                        <img v-if="item.image" :src="item.image" :srcset="item.srcset"
                            sizes="(min-width: 640px) 24rem, 80vw"
                            :alt="item.title" loading="lazy" decoding="async"
                            class="relative h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105 group-hover:opacity-100">
                        <span v-else
                            class="depth-2 relative text-5xl drop-shadow-[0_0_18px_rgba(52,226,245,0.6)] transition duration-500 group-hover:-translate-y-1">
                            {{ item.icon }}
                        </span>

                        <span
                            class="absolute left-4 top-4 rounded-full border px-3 py-1 text-[11px] font-bold tracking-wide backdrop-blur"
                            :class="accentOf(item.accent).badge">
                            {{ item.category }}
                        </span>

                        <!-- Garis pemisah bercahaya di bawah area media. -->
                        <span class="hairline-neon absolute inset-x-0 bottom-0"></span>
                    </div>

                    <div class="relative flex flex-1 flex-col p-6">
                        <time class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ item.date }}
                        </time>

                        <h3 class="mt-2 font-display text-lg font-bold leading-snug text-slate-50">
                            {{ item.title }}
                        </h3>

                        <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-300/75">{{ item.excerpt }}</p>

                        <span
                            class="mt-5 inline-flex items-center gap-2 text-sm font-bold transition-all duration-300 group-hover:gap-3"
                            :class="accentOf(item.accent).link">
                            Baca Selengkapnya
                            <span aria-hidden="true">&rarr;</span>
                        </span>
                    </div>
                </Link>
            </HoloTilt>
        </SwiperSlide>
    </Swiper>
</template>

<style scoped>
/* Kartu adalah tautan, jadi kursornya harus tangan.
 *
 * `grab-cursor` menulis `cursor: grab` sebagai gaya inline di `.swiper-wrapper`,
 * dan Blink TIDAK punya aturan UA `cursor` untuk <a> — kursor tangan di atas
 * tautan muncul hanya ketika nilai terhitungnya masih `auto`. Karena `cursor`
 * diwarisi, `grab` dari wrapper ikut turun sampai ke dalam kartu dan menutupi
 * satu-satunya petunjuk bahwa kartu itu bisa diklik. Afordansi usap tetap ada
 * di area sekitar kartu. */
.neon-swiper :deep(.swiper-slide a) {
    cursor: pointer;
}

/* Kemiringan coverflow, digambar sendiri.
 *
 * Semua slide memakai satu rumus transform yang sama; yang berubah antar-state
 * cuma tiga custom property di bawahnya. `perspective()` ditulis sebagai FUNGSI
 * di dalam `transform`, bukan sebagai properti di leluhur — itu yang menjaga
 * kartunya tetap bisa diklik (alasan lengkapnya di <script>).
 *
 * Urutan fungsinya penting: `translateX` sengaja diletakkan SEBELUM `rotateY`
 * supaya pergeserannya terjadi di ruang layar setelah kartu miring — kalau
 * ditaruh sesudahnya, ia akan menggeser sepanjang sumbu yang ikut terputar dan
 * kartunya malah maju-mundur, bukan bergeser ke samping.
 *
 * `transform` aman disentuh dari CSS: efek bawaan Swiper ('slide') hanya
 * menulis transform pada `.swiper-wrapper`, tidak pernah pada slide. */
.neon-swiper :deep(.swiper-slide) {
    /* Miring ke arah mana (derajat), digeser sejauh apa ke tengah, dan sekecil
       apa. Hanya ketiga angka ini yang perlu disetel untuk mengubah rasa efek. */
    --tilt: 0deg;
    --shift: 0%;
    --shrink: 0.86;

    opacity: 0.3;
    transform: perspective(1200px) translateX(var(--shift)) rotateY(var(--tilt)) scale(var(--shrink));
    filter: saturate(0.6);
    transition: opacity 0.5s ease, transform 0.5s ease, filter 0.5s ease;
}

/* Dua tetangga miring saling berhadapan ke arah kartu tengah — sudut sama
 * besar, arah berlawanan, jadi simetris kiri-kanan.
 *
 * Keduanya tetap terang penuh, seperti dulu di coverflow. Itu bukan detail
 * sepele: saat beritanya hanya tiga, ketiga kartu selalu terlihat sekaligus dan
 * tidak ada yang bisa digeser, jadi meredupkan tetangga berarti dua dari tiga
 * kartu tampak pudar selamanya.
 *
 * `--shift` menarik keduanya sedikit ke tengah karena kartu yang miring
 * menyempit secara optik (lebar proyeksinya ~cos 34° ≈ 0,83); tanpa itu celah
 * antar-kartu jadi menganga. */
.neon-swiper :deep(.swiper-slide-prev),
.neon-swiper :deep(.swiper-slide-next) {
    --shrink: 0.94;

    opacity: 1;
    filter: saturate(1);
}

.neon-swiper :deep(.swiper-slide-prev) {
    --tilt: 34deg;
    --shift: 5%;
}

.neon-swiper :deep(.swiper-slide-next) {
    --tilt: -34deg;
    --shift: -5%;
}

/* Kartu tengah menghadap lurus ke pembaca. */
.neon-swiper :deep(.swiper-slide-active) {
    --tilt: 0deg;
    --shift: 0%;
    --shrink: 1;

    opacity: 1;
    filter: saturate(1);
}

.neon-swiper :deep(.swiper-button-next),
.neon-swiper :deep(.swiper-button-prev) {
    --swiper-navigation-size: 20px;

    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    color: var(--color-aqua-300);
    background: var(--panel-bg);
    backdrop-filter: blur(14px);
    border: 1px solid var(--panel-border);
    box-shadow: 0 0 24px -6px rgba(52, 226, 245, 0.6);
    transition: transform 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

.neon-swiper :deep(.swiper-button-next:hover),
.neon-swiper :deep(.swiper-button-prev:hover) {
    transform: scale(1.08);
    color: var(--color-aqua-500);
    box-shadow: 0 0 36px -4px rgba(169, 123, 255, 0.8);
}

.neon-swiper :deep(.swiper-pagination-bullet) {
    width: 20px;
    height: 3px;
    border-radius: 9999px;
    background: var(--color-aqua-400);
    opacity: 0.35;
    transition: width 0.3s ease, opacity 0.3s ease, box-shadow 0.3s ease;
}

.neon-swiper :deep(.swiper-pagination-bullet-active) {
    width: 44px;
    opacity: 1;
    box-shadow: 0 0 14px rgba(52, 226, 245, 0.9);
}
</style>
