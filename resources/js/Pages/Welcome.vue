<script setup>
import { Head } from '@inertiajs/vue3';

import SiteNavbar from '../Components/SiteNavbar.vue';
import HeroBackground from '../Components/HeroBackground.vue';
import NewsSlider from '../Components/NewsSlider.vue';
import AchievementSlider from '../Components/AchievementSlider.vue';
import { getSmoothScroll } from '../lib/smooth-scroll';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
});

const navLinks = [
    { label: 'Beranda', hash: '#beranda' },
    { label: 'Keunggulan', hash: '#keunggulan' },
    { label: 'Berita & Kegiatan', hash: '#berita' },
    { label: 'Prestasi', hash: '#prestasi' },
    { label: 'Kontak', hash: '#kontak' },
];

const scrollTo = (hash) => {
    const target = document.querySelector(hash);

    if (!target) {
        return;
    }

    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset: -80, duration: 1.4 });
    } else {
        target.scrollIntoView({ behavior: 'smooth' });
    }
};

const stats = [
    { value: '1.200+', label: 'Siswa Aktif' },
    { value: '85+', label: 'Guru & Tenaga Pendidik' },
    { value: '150+', label: 'Prestasi Diraih' },
    { value: '20', label: 'Tahun Mendidik' },
];

const advantages = [
    {
        icon: '📖',
        title: 'Kurikulum Tahfidz & Akademik',
        description:
            'Perpaduan kurikulum nasional dengan program tahfidz terstruktur. Target hafalan bertahap yang disesuaikan dengan jenjang dan kemampuan setiap ananda.',
        points: ['Target 5–10 juz', 'Halaqah kelompok kecil', 'Muraja\'ah harian terpantau'],
    },
    {
        icon: '🌱',
        title: 'Pembentukan Karakter Islami',
        description:
            'Pembiasaan adab dan akhlak mulia dalam keseharian sekolah, dibimbing wali kelas serta guru pendamping yang dekat dengan siswa.',
        points: ['Shalat berjamaah', 'Program adab harian', 'Pendampingan personal'],
    },
    {
        icon: '🏫',
        title: 'Fasilitas Modern & Ramah Anak',
        description:
            'Ruang belajar nyaman, laboratorium, perpustakaan, dan area bermain yang aman — dirancang mendukung tumbuh kembang anak dengan gembira.',
        points: ['Kelas ber-AC & proyektor', 'Lab sains & komputer', 'Area bermain aman'],
    },
    {
        icon: '👩‍🏫',
        title: 'Tenaga Pengajar Kompeten',
        description:
            'Guru bersertifikat dengan latar belakang sesuai bidang, ditambah pembinaan rutin agar metode mengajar selalu relevan dan menyenangkan.',
        points: ['Guru bersertifikasi', 'Rasio kelas ideal', 'Pelatihan berkala'],
    },
];

const news = [
    {
        category: 'Kegiatan',
        icon: '🕌',
        date: '18 Juli 2026',
        title: 'Pesantren Kilat Ramadhan: Menumbuhkan Cinta Al-Qur\'an',
        excerpt:
            'Siswa SD dan SMP mengikuti rangkaian tahfidz, kajian adab, dan buka puasa bersama selama sepuluh hari penuh keberkahan.',
        tone: 'bg-linear-to-br from-sage-300 to-sage-500',
    },
    {
        category: 'Berita',
        icon: '🏆',
        date: '10 Juli 2026',
        title: 'Tim Robotik SMP Melaju ke Final Tingkat Nasional',
        excerpt:
            'Setelah menyisihkan 64 sekolah pada babak regional, tim robotik kami memastikan tempat di babak final yang digelar di Jakarta.',
        tone: 'bg-linear-to-br from-gold-300 to-cream-400',
    },
    {
        category: 'Kegiatan',
        icon: '🌿',
        date: '2 Juli 2026',
        title: 'Outdoor Learning: Belajar Sains dari Alam',
        excerpt:
            'Siswa kelas 4–6 SD menjelajahi kebun raya, mengamati ekosistem secara langsung, dan menyusun jurnal pengamatan sederhana.',
        tone: 'bg-linear-to-br from-sage-200 to-cream-300',
    },
    {
        category: 'Pengumuman',
        icon: '📝',
        date: '25 Juni 2026',
        title: 'PPDB Gelombang II Resmi Dibuka',
        excerpt:
            'Pendaftaran siswa baru jenjang SD dan SMP tahun ajaran 2026/2027 dibuka. Kuota terbatas dengan beasiswa prestasi tersedia.',
        tone: 'bg-linear-to-br from-cream-200 to-gold-300',
    },
    {
        category: 'Berita',
        icon: '🤝',
        date: '14 Juni 2026',
        title: 'Parenting Class: Sinergi Sekolah dan Keluarga',
        excerpt:
            'Lebih dari 300 orang tua hadir dalam sesi bersama psikolog anak membahas pendampingan belajar di rumah.',
        tone: 'bg-linear-to-br from-sage-200 to-sage-400',
    },
];

const achievements = [
    {
        icon: '🥇',
        level: 'Nasional',
        title: 'Juara 1 Musabaqah Hifzhil Qur\'an 10 Juz',
        description: 'Meraih peringkat pertama pada ajang tahfidz tingkat nasional kategori pelajar.',
        student: 'Aisyah Nur Fadhilah',
        grade: 'SMP Kelas 8',
    },
    {
        icon: '🔬',
        level: 'Nasional',
        title: 'Medali Perak Olimpiade Sains (IPA)',
        description: 'Bersaing dengan 500 peserta se-Indonesia pada babak final olimpiade sains pelajar.',
        student: 'Muhammad Rafi Alfarizi',
        grade: 'SMP Kelas 9',
    },
    {
        icon: '🏸',
        level: 'Provinsi',
        title: 'Juara 1 Bulu Tangkis Tunggal Putra',
        description: 'Menjuarai kompetisi olahraga pelajar tingkat provinsi kategori usia SD.',
        student: 'Zaidan Hafiz',
        grade: 'SD Kelas 6',
    },
    {
        icon: '🎨',
        level: 'Provinsi',
        title: 'Juara 2 Lomba Kaligrafi Islami',
        description: 'Karya kaligrafi dengan komposisi geometris meraih penghargaan dewan juri.',
        student: 'Khansa Aulia',
        grade: 'SD Kelas 5',
    },
    {
        icon: '📐',
        level: 'Internasional',
        title: 'Honorable Mention Math Challenge',
        description: 'Penghargaan pada kompetisi matematika internasional berbasis daring.',
        student: 'Farrel Adhitama',
        grade: 'SMP Kelas 7',
    },
    {
        icon: '🎤',
        level: 'Nasional',
        title: 'Juara 3 Lomba Pidato Bahasa Arab',
        description: 'Menampilkan orasi bertema akhlak mulia di hadapan juri tingkat nasional.',
        student: 'Nadhira Salsabila',
        grade: 'SMP Kelas 8',
    },
    {
        icon: '⚽',
        level: 'Provinsi',
        title: 'Juara 1 Futsal Antar Sekolah Islam',
        description: 'Tim futsal SD tampil tanpa kekalahan sepanjang turnamen.',
        student: 'Tim Futsal SD',
        grade: 'SD',
    },
    {
        icon: '📚',
        level: 'Nasional',
        title: 'Juara Harapan 1 Lomba Cerdas Cermat',
        description: 'Mewakili wilayah pada kompetisi cerdas cermat pelajar tingkat nasional.',
        student: 'Tim LCC SMP',
        grade: 'SMP',
    },
];

const contacts = [
    { icon: '📍', label: 'Alamat', value: 'Jl. Pendidikan Islami No. 45, Kec. Cimahi Utara, Kota Cimahi, Jawa Barat 40514' },
    { icon: '📞', label: 'Telepon', value: '(022) 8765 4321' },
    { icon: '✉️', label: 'Email', value: 'info@alazka.sch.id' },
    { icon: '🕐', label: 'Jam Operasional', value: 'Senin – Jumat, 07.00 – 16.00 WIB' },
];

const socials = [
    { label: 'Instagram', href: '#' },
    { label: 'YouTube', href: '#' },
    { label: 'Facebook', href: '#' },
    { label: 'WhatsApp', href: '#' },
];

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Beranda" />

    <div class="min-h-screen bg-cream-50 font-sans text-sage-900">
        <SiteNavbar :school-name="props.schoolName" :links="navLinks" />

        <main>
            <!-- ============ HERO ============ -->
            <!-- `isolate` wajib: tanpa stacking context sendiri, kanvas -z-10 akan
                 tertimpa background bg-cream-50 milik wrapper halaman. -->
            <section id="beranda" class="relative isolate flex min-h-screen items-center overflow-hidden">
                <!-- Latar 3D Three.js -->
                <div class="absolute inset-0 -z-10">
                    <HeroBackground />
                </div>

                <!-- Gradasi lembut agar teks tetap kontras di atas kanvas 3D. -->
                <div
                    class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-b from-cream-50/70 via-cream-50/30 to-cream-50">
                </div>

                <div class="container-page relative pt-32 pb-20 text-center">
                    <div v-reveal="{ from: 'fade', duration: 1 }" class="flex justify-center">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-sage-200 bg-white/70 px-4 py-2 text-xs font-semibold text-sage-700 backdrop-blur sm:text-sm">
                            <span class="h-2 w-2 rounded-full bg-gold-400"></span>
                            Terakreditasi A &middot; SD &amp; SMP Islam Terpadu
                        </span>
                    </div>

                    <h1 v-reveal="{ from: 'zoom', duration: 1.2, delay: 0.1 }"
                        class="mx-auto mt-7 max-w-4xl font-display text-4xl font-bold leading-[1.15] text-sage-900 sm:text-5xl lg:text-6xl">
                        Membentuk Generasi
                        <span class="text-sage-600">Cerdas</span>,
                        <span class="text-gold-600">Berakhlak Mulia</span>,
                        dan Berwawasan Global
                    </h1>

                    <p v-reveal="{ delay: 0.3 }"
                        class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-sage-700/90 sm:text-lg">
                        Sekolah Islam terpadu yang memadukan kekuatan akademik, pembiasaan Al-Qur'an, dan pembentukan
                        karakter dalam lingkungan belajar yang teduh, aman, dan menyenangkan bagi ananda.
                    </p>

                    <div v-reveal="{ delay: 0.45, stagger: 0.12 }"
                        class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <a href="#keunggulan"
                            class="w-full rounded-full bg-sage-600 px-8 py-4 text-sm font-semibold text-cream-50 shadow-xl shadow-sage-600/25 transition hover:-translate-y-0.5 hover:bg-sage-700 sm:w-auto"
                            @click.prevent="scrollTo('#keunggulan')">
                            Jelajahi Sekolah
                        </a>
                        <a href="#ppdb"
                            class="w-full rounded-full border border-sage-300 bg-white/80 px-8 py-4 text-sm font-semibold text-sage-800 backdrop-blur transition hover:-translate-y-0.5 hover:border-gold-400 hover:bg-white sm:w-auto"
                            @click.prevent="scrollTo('#ppdb')">
                            Daftar Sekarang
                        </a>
                    </div>

                    <!-- Statistik singkat -->
                    <dl v-reveal="{ delay: 0.6, stagger: 0.1 }"
                        class="mx-auto mt-16 grid max-w-3xl grid-cols-2 gap-4 sm:grid-cols-4">
                        <div v-for="stat in stats" :key="stat.label"
                            class="rounded-3xl border border-sage-100 bg-white/70 px-4 py-5 backdrop-blur">
                            <dt class="font-display text-2xl font-bold text-sage-700 sm:text-3xl">{{ stat.value }}</dt>
                            <dd class="mt-1 text-xs font-medium text-sage-600 sm:text-sm">{{ stat.label }}</dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="pointer-events-none absolute bottom-6 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[11px] font-medium uppercase tracking-[0.25em] text-sage-500 lg:flex">
                    <span>Scroll</span>
                    <span class="h-10 w-px animate-pulse bg-linear-to-b from-sage-400 to-transparent"></span>
                </div>
            </section>

            <!-- ============ KEUNGGULAN ============ -->
            <section id="keunggulan" class="pattern-islamic scroll-mt-24 bg-white py-24 sm:py-32">
                <div class="container-page">
                    <div class="mx-auto max-w-2xl text-center">
                        <p v-reveal="{ from: 'fade' }"
                            class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">
                            Keunggulan Kami
                        </p>
                        <h2 v-reveal="{ delay: 0.1 }"
                            class="mt-3 font-display text-3xl font-bold text-sage-900 sm:text-4xl">
                            Empat Pilar Pendidikan di Sekolah Kami
                        </h2>
                        <p v-reveal="{ delay: 0.2 }" class="mt-4 text-base leading-relaxed text-sage-700/85">
                            Setiap pilar dirancang agar ananda tumbuh seimbang — kuat ilmunya, lembut akhlaknya, dan
                            siap menghadapi tantangan zaman.
                        </p>
                    </div>

                    <div v-reveal="{ stagger: 0.14 }" class="mt-16 grid gap-6 md:grid-cols-2">
                        <article v-for="item in advantages" :key="item.title"
                            class="group relative overflow-hidden rounded-3xl border border-sage-100 bg-cream-50 p-8 transition duration-300 hover:-translate-y-1.5 hover:border-sage-300 hover:bg-white hover:shadow-[0_24px_60px_-30px_rgba(36,57,49,0.45)]">
                            <div
                                class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-sage-200/40 opacity-0 blur-3xl transition duration-500 group-hover:opacity-100">
                            </div>

                            <div
                                class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-sage-200 to-cream-200 text-3xl transition duration-300 group-hover:scale-105">
                                {{ item.icon }}
                            </div>

                            <h3 class="relative mt-6 font-display text-xl font-bold text-sage-900">{{ item.title }}</h3>

                            <p class="relative mt-3 text-sm leading-relaxed text-sage-700/85">{{ item.description }}</p>

                            <ul class="relative mt-6 flex flex-wrap gap-2">
                                <li v-for="point in item.points" :key="point"
                                    class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-sage-600 ring-1 ring-inset ring-sage-200 transition group-hover:ring-gold-400/50">
                                    {{ point }}
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <!-- ============ BERITA & KEGIATAN ============ -->
            <section id="berita" class="scroll-mt-24 bg-cream-50 py-24 sm:py-32">
                <div class="container-page">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-xl">
                            <p v-reveal="{ from: 'fade' }"
                                class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">
                                Kabar Sekolah
                            </p>
                            <h2 v-reveal="{ delay: 0.1 }"
                                class="mt-3 font-display text-3xl font-bold text-sage-900 sm:text-4xl">
                                Berita &amp; Kegiatan Terbaru
                            </h2>
                            <p v-reveal="{ delay: 0.2 }" class="mt-4 text-base leading-relaxed text-sage-700/85">
                                Ikuti keseharian, program, dan momen membanggakan yang berlangsung di lingkungan
                                sekolah.
                            </p>
                        </div>

                        <a v-reveal="{ from: 'left', delay: 0.2 }" href="#berita"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full border border-sage-300 px-6 py-3 text-sm font-semibold text-sage-700 transition hover:border-gold-400 hover:bg-white">
                            Lihat Semua Berita
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>

                    <div v-reveal="{ from: 'fade', delay: 0.25 }" class="mt-14">
                        <NewsSlider :items="news" />
                    </div>
                </div>
            </section>

            <!-- ============ PRESTASI ============ -->
            <section id="prestasi" class="pattern-islamic scroll-mt-24 bg-white py-24 sm:py-32">
                <div class="container-page">
                    <div class="mx-auto max-w-2xl text-center">
                        <p v-reveal="{ from: 'fade' }"
                            class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">
                            Pencapaian
                        </p>
                        <h2 v-reveal="{ delay: 0.1 }"
                            class="mt-3 font-display text-3xl font-bold text-sage-900 sm:text-4xl">
                            Prestasi Siswa SD &amp; SMP
                        </h2>
                        <p v-reveal="{ delay: 0.2 }" class="mt-4 text-base leading-relaxed text-sage-700/85">
                            Buah dari proses belajar yang konsisten — di bidang akademik, olahraga, seni, hingga
                            tahfidz Al-Qur'an.
                        </p>
                    </div>

                    <div v-reveal="{ from: 'fade', delay: 0.25 }" class="mt-14">
                        <AchievementSlider :items="achievements" />
                    </div>
                </div>
            </section>

            <!-- ============ CTA PPDB ============ -->
            <section id="ppdb" class="scroll-mt-24 bg-cream-50 pb-24 sm:pb-32">
                <div class="container-page">
                    <div v-reveal="{ from: 'zoom' }"
                        class="relative overflow-hidden rounded-[2rem] bg-linear-to-br from-sage-600 to-sage-800 px-8 py-16 text-center sm:px-16">
                        <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-gold-400/15 blur-3xl">
                        </div>
                        <div
                            class="pointer-events-none absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-cream-200/15 blur-3xl">
                        </div>

                        <div class="relative">
                            <span
                                class="inline-block rounded-full bg-gold-400/20 px-4 py-1.5 text-xs font-semibold text-gold-300">
                                PPDB Tahun Ajaran 2026/2027
                            </span>

                            <h2 class="mt-6 font-display text-3xl font-bold leading-tight text-cream-50 sm:text-4xl">
                                Mari Wujudkan Masa Depan Terbaik untuk Ananda
                            </h2>

                            <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-cream-200 sm:text-base">
                                Pendaftaran gelombang II telah dibuka dengan kuota terbatas. Tersedia beasiswa prestasi
                                akademik dan tahfidz bagi calon siswa terpilih.
                            </p>

                            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <a href="#ppdb"
                                    class="w-full rounded-full bg-gold-400 px-8 py-4 text-sm font-bold text-sage-900 shadow-xl shadow-black/15 transition hover:-translate-y-0.5 hover:bg-gold-300 sm:w-auto">
                                    Daftar Sekarang
                                </a>
                                <a href="#kontak"
                                    class="w-full rounded-full border border-cream-200/40 px-8 py-4 text-sm font-semibold text-cream-50 transition hover:-translate-y-0.5 hover:bg-white/10 sm:w-auto"
                                    @click.prevent="scrollTo('#kontak')">
                                    Konsultasi Dulu
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- ============ FOOTER / KONTAK ============ -->
        <footer id="kontak" class="scroll-mt-24 bg-sage-900 text-cream-100">
            <div class="container-page py-20">
                <div class="grid gap-12 lg:grid-cols-[1.1fr_1fr_1fr]">
                    <!-- Identitas & sosial -->
                    <div v-reveal="{ from: 'fade' }">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gold-400 font-display text-lg font-bold text-sage-900">
                                A
                            </span>
                            <span class="leading-tight">
                                <span class="block font-display text-lg font-bold text-cream-50">{{ props.schoolName
                                    }}</span>
                                <span class="block text-xs text-cream-300/70">SD &amp; SMP Islam Terpadu</span>
                            </span>
                        </div>

                        <p class="mt-6 max-w-sm text-sm leading-relaxed text-cream-200/70">
                            Mendidik generasi Qur'ani yang cerdas, berakhlak mulia, dan siap berkontribusi bagi umat
                            serta bangsa.
                        </p>

                        <ul class="mt-7 flex flex-wrap gap-2">
                            <li v-for="social in socials" :key="social.label">
                                <a :href="social.href"
                                    class="inline-block rounded-full border border-cream-200/20 px-4 py-2 text-xs font-semibold text-cream-100 transition hover:border-gold-400 hover:bg-white/5 hover:text-gold-300">
                                    {{ social.label }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Kontak -->
                    <div v-reveal="{ delay: 0.1 }">
                        <h3 class="font-display text-sm font-bold uppercase tracking-[0.18em] text-gold-300">
                            Hubungi Kami
                        </h3>

                        <ul class="mt-6 space-y-5">
                            <li v-for="contact in contacts" :key="contact.label" class="flex gap-3">
                                <span class="mt-0.5 text-lg" aria-hidden="true">{{ contact.icon }}</span>
                                <span>
                                    <span class="block text-xs font-semibold text-cream-300/60">{{ contact.label
                                        }}</span>
                                    <span class="block text-sm leading-relaxed text-cream-100/90">{{ contact.value
                                        }}</span>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- Peta singkat + navigasi -->
                    <div v-reveal="{ delay: 0.2 }">
                        <h3 class="font-display text-sm font-bold uppercase tracking-[0.18em] text-gold-300">
                            Lokasi Sekolah
                        </h3>

                        <div class="mt-6 overflow-hidden rounded-3xl border border-cream-200/15">
                            <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox=107.5300%2C-6.8800%2C107.5600%2C-6.8600&amp;layer=mapnik"
                                title="Peta lokasi sekolah" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                class="h-52 w-full border-0 grayscale-[35%]"></iframe>
                        </div>

                        <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2">
                            <li v-for="link in navLinks" :key="link.hash">
                                <a :href="link.hash"
                                    class="text-sm text-cream-200/70 transition hover:text-gold-300"
                                    @click.prevent="scrollTo(link.hash)">
                                    {{ link.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-16 flex flex-col items-center justify-between gap-3 border-t border-cream-200/10 pt-8 text-xs text-cream-300/60 sm:flex-row">
                    <p>&copy; {{ currentYear }} {{ props.schoolName }}. Seluruh hak cipta dilindungi.</p>
                    <p>Dibangun dengan penuh amanah untuk pendidikan Indonesia.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
