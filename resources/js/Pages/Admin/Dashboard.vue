<script setup>
import { Link } from '@inertiajs/vue3';

import AdminLayout from '../../Components/Admin/AdminLayout.vue';

const props = defineProps({
    stats: { type: Array, default: () => [] },
    recentNews: { type: Array, default: () => [] },
});

const shortcuts = [
    { icon: '📝', label: 'Tulis Berita Baru', description: 'Buat artikel lengkap dengan banner & galeri foto.', href: '/admin/berita/tambah' },
    { icon: '🏆', label: 'Tambah Prestasi', description: 'Catat capaian siswa terbaru beserta fotonya.', href: '/admin/koleksi/achievements' },
    { icon: '✏️', label: 'Ubah Teks Halaman', description: 'Judul hero, deskripsi section, hingga tombol ajakan.', href: '/admin/konten' },
    { icon: '🖼️', label: 'Ganti Foto Beranda', description: 'Unggah foto gedung sekolah untuk latar hero.', href: '/admin/konten' },
];
</script>

<template>
    <AdminLayout title="Dasbor" subtitle="Ringkasan isi situs dan pintasan pengelolaan konten.">
        <!-- Ringkasan angka -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link v-for="stat in props.stats" :key="stat.label" :href="stat.href"
                class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-xl">
                    {{ stat.icon }}
                </span>
                <span>
                    <span class="block text-2xl font-extrabold text-slate-900">{{ stat.value }}</span>
                    <span class="block text-sm text-slate-500">{{ stat.label }}</span>
                </span>
            </Link>
        </div>

        <!-- Pintasan -->
        <h2 class="mt-10 text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Pintasan</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <Link v-for="item in shortcuts" :key="item.label" :href="item.href"
                class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md">
                <span class="text-2xl">{{ item.icon }}</span>
                <span class="mt-3 block font-bold text-slate-900">{{ item.label }}</span>
                <span class="mt-1 block text-sm text-slate-500">{{ item.description }}</span>
            </Link>
        </div>

        <!-- Berita terbaru -->
        <div class="mt-10 rounded-2xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-900">Berita Terbaru</h2>
                <Link href="/admin/berita" class="text-sm font-semibold text-teal-600 hover:text-teal-700">
                    Kelola semua →
                </Link>
            </div>

            <p v-if="!props.recentNews.length" class="px-5 py-8 text-center text-sm text-slate-500">
                Belum ada berita. <Link href="/admin/berita/tambah" class="font-semibold text-teal-600">Tulis yang pertama</Link>.
            </p>

            <ul v-else class="divide-y divide-slate-100">
                <li v-for="news in props.recentNews" :key="news.id" class="flex items-center gap-4 px-5 py-3.5">
                    <span class="min-w-0 flex-1">
                        <Link :href="news.edit_url" class="block truncate font-semibold text-slate-800 hover:text-teal-600">
                            {{ news.title }}
                        </Link>
                        <span class="text-xs text-slate-500">{{ news.category }} · {{ news.date }}</span>
                    </span>
                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-bold"
                        :class="news.is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                        {{ news.is_published ? 'Terbit' : 'Draf' }}
                    </span>
                </li>
            </ul>
        </div>
    </AdminLayout>
</template>
