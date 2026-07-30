<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';

import AdminLayout from '../../../Components/Admin/AdminLayout.vue';

/** Daftar halaman kustom: cari, terbitkan/sembunyikan, ubah, hapus. */
const props = defineProps({
    pages: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

let debounce = null;

watch(search, (value) => {
    clearTimeout(debounce);

    debounce = setTimeout(() => {
        router.get('/admin/halaman', { search: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 350);
});

const destroy = (item) => {
    if (!window.confirm(`Hapus halaman "${item.title}"? Alamat /${item.slug} akan menjadi 404.`)) {
        return;
    }

    router.delete(`/admin/halaman/${item.id}`, { preserveScroll: true });
};

const togglePublish = (item) => {
    router.patch(`/admin/halaman/${item.id}/terbit`, {}, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout title="📄 Halaman Kustom"
        subtitle="Halaman dengan alamat sendiri di situs, mis. /datasiswa — disusun per blok atau lewat kode HTML.">
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <Link href="/admin/halaman/tambah"
                class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700">
                + Buat Halaman
            </Link>

            <input v-model="search" type="search" placeholder="Cari judul atau alamat…"
                class="w-full max-w-xs rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 sm:w-72">

            <span class="text-sm text-slate-500">{{ props.pages.total }} halaman</span>
        </div>

        <div data-lenis-prevent class="admin-scroll overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <div v-if="!props.pages.data.length" class="px-5 py-12 text-center text-sm text-slate-500">
                Belum ada halaman kustom. Tekan “Buat Halaman” untuk memulai.
            </div>

            <table v-else class="w-full min-w-3xl text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Judul & Alamat</th>
                        <th class="w-52 px-4 py-3">Mode</th>
                        <th class="w-36 px-4 py-3">Diperbarui</th>
                        <th class="w-28 px-4 py-3">Status</th>
                        <th class="w-40 px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-for="item in props.pages.data" :key="item.id" class="hover:bg-slate-50/60">
                        <td class="px-4 py-3">
                            <Link :href="`/admin/halaman/${item.id}/ubah`"
                                class="font-semibold text-slate-800 transition hover:text-teal-600">
                                {{ item.title }}
                            </Link>
                            <span class="block font-mono text-xs text-slate-400">/{{ item.slug }}</span>
                        </td>

                        <td class="px-4 py-3 text-slate-600">
                            {{ item.mode === 'builder' ? '🧩 Visual Builder' : '🧬 Kode HTML' }}
                            <span v-if="item.blockCount !== null" class="block text-xs text-slate-400">
                                {{ item.blockCount }} blok
                            </span>
                        </td>

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
                            <!-- Draf pun bisa dibuka: halaman publiknya
                                 menampilkan pratinjau untuk admin yang login. -->
                            <a :href="`/${item.slug}`" target="_blank"
                                class="font-semibold text-slate-500 transition hover:text-slate-700">Lihat</a>
                            <Link :href="`/admin/halaman/${item.id}/ubah`"
                                class="ml-3 font-semibold text-teal-600 transition hover:text-teal-700">Ubah</Link>
                            <button type="button" class="ml-3 font-semibold text-rose-500 transition hover:text-rose-600"
                                @click="destroy(item)">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="props.pages.links.length > 3" class="mt-5 flex flex-wrap gap-1">
            <component :is="link.url ? 'a' : 'span'" v-for="link in props.pages.links" :key="link.label" :href="link.url"
                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                :class="[
                    link.active ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200',
                    link.url ? 'hover:bg-teal-50' : 'cursor-default opacity-40',
                ]" v-html="link.label" />
        </div>
    </AdminLayout>
</template>
