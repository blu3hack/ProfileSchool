<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';

import AdminLayout from '../../../Components/Admin/AdminLayout.vue';

/** Daftar berita dengan pencarian, sakelar terbit, dan hapus. */
const props = defineProps({
    news: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

let debounce = null;

watch(search, (value) => {
    clearTimeout(debounce);

    debounce = setTimeout(() => {
        router.get('/admin/berita', { search: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 350);
});

const destroy = (item) => {
    if (!window.confirm(`Hapus berita "${item.title}"? Tindakan ini tidak bisa dibatalkan.`)) {
        return;
    }

    router.delete(`/admin/berita/${item.id}`, { preserveScroll: true });
};

const togglePublish = (item) => {
    router.patch(`/admin/berita/${item.id}/terbit`, {}, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout title="📰 Berita" subtitle="Tambah, ubah, terbitkan, atau hapus artikel berita sekolah.">
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <Link href="/admin/berita/tambah"
                class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700">
                + Tulis Berita
            </Link>

            <input v-model="search" type="search" placeholder="Cari judul atau kategori…"
                class="w-full max-w-xs rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 sm:w-72">

            <span class="text-sm text-slate-500">{{ props.news.total }} berita</span>
        </div>

        <!-- `overflow-x-auto`: kolom yang melebihi lebar layar bisa digulir,
             bukan terpotong seperti saat memakai `overflow-hidden`. -->
        <div data-lenis-prevent class="admin-scroll overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <div v-if="!props.news.data.length" class="px-5 py-12 text-center text-sm text-slate-500">
                Tidak ada berita yang cocok.
            </div>

            <table v-else class="w-full min-w-3xl text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-20 px-4 py-3">Banner</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="w-40 px-4 py-3">Kategori</th>
                        <th class="w-36 px-4 py-3">Tanggal</th>
                        <th class="w-28 px-4 py-3">Status</th>
                        <th class="w-40 px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-for="item in props.news.data" :key="item.id" class="hover:bg-slate-50/60">
                        <td class="px-4 py-3">
                            <img v-if="item.image" :src="item.image" alt="" class="h-10 w-14 rounded-lg object-cover">
                            <span v-else class="text-xs text-slate-300">—</span>
                        </td>

                        <td class="px-4 py-3">
                            <Link :href="`/admin/berita/${item.id}/ubah`"
                                class="font-semibold text-slate-800 transition hover:text-teal-600">
                                {{ item.icon }} {{ item.title }}
                            </Link>
                            <span class="block text-xs text-slate-400">/berita/{{ item.slug }}</span>
                        </td>

                        <td class="px-4 py-3 text-slate-600">{{ item.category }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ item.date }}</td>

                        <td class="px-4 py-3">
                            <button type="button" class="rounded-full px-3 py-1 text-[11px] font-bold transition"
                                :class="item.is_published
                                    ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                                @click="togglePublish(item)">
                                {{ item.is_published ? 'Terbit' : 'Draf' }}
                            </button>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a :href="`/berita/${item.slug}`" target="_blank"
                                class="font-semibold text-slate-500 transition hover:text-slate-700">Lihat</a>
                            <Link :href="`/admin/berita/${item.id}/ubah`"
                                class="ml-3 font-semibold text-teal-600 transition hover:text-teal-700">Ubah</Link>
                            <button type="button" class="ml-3 font-semibold text-rose-500 transition hover:text-rose-600"
                                @click="destroy(item)">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Halaman -->
        <div v-if="props.news.links.length > 3" class="mt-5 flex flex-wrap gap-1">
            <component :is="link.url ? 'a' : 'span'" v-for="link in props.news.links" :key="link.label" :href="link.url"
                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                :class="[
                    link.active ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200',
                    link.url ? 'hover:bg-teal-50' : 'cursor-default opacity-40',
                ]" v-html="link.label" />
        </div>
    </AdminLayout>
</template>
