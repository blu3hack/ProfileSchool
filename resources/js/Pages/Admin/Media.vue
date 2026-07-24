<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import AdminLayout from '../../Components/Admin/AdminLayout.vue';
import { postForm } from '../../lib/api';

/** Pustaka gambar: unggah berkas baru, salin path, atau hapus. */
const props = defineProps({
    media: { type: Object, required: true },
});

const uploading = ref(false);
const error = ref('');
const copied = ref(null);

const upload = async (event) => {
    const files = [...(event.target.files ?? [])];

    if (!files.length) {
        return;
    }

    uploading.value = true;
    error.value = '';

    try {
        // Diunggah berurutan supaya pesan galat mudah ditelusuri.
        for (const file of files) {
            const data = new FormData();
            data.append('file', file);

            await postForm('/admin/media', data);
        }

        router.reload({ only: ['media'] });
    } catch (e) {
        error.value = e.message;
    } finally {
        uploading.value = false;
        event.target.value = '';
    }
};

const destroy = (item) => {
    if (!window.confirm(`Hapus "${item.name}"? Gambar yang sedang dipakai akan hilang dari halaman.`)) {
        return;
    }

    router.delete(`/admin/media/${item.id}`, { preserveScroll: true });
};

const copyPath = async (item) => {
    await navigator.clipboard.writeText(item.url);
    copied.value = item.id;
    setTimeout(() => (copied.value = null), 2000);
};
</script>

<template>
    <AdminLayout title="🖼️ Pustaka Media"
        subtitle="Semua gambar yang diunggah — bisa dipakai ulang sebagai banner berita, foto prestasi, atau latar beranda.">
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <label
                class="cursor-pointer rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700"
                :class="{ 'pointer-events-none opacity-60': uploading }">
                {{ uploading ? 'Mengunggah…' : '+ Unggah Gambar' }}
                <input type="file" accept="image/*" multiple class="hidden" @change="upload">
            </label>
            <span class="text-sm text-slate-500">{{ props.media.total }} gambar · maksimal 5 MB per berkas</span>
        </div>

        <p v-if="error" class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ error }}</p>

        <div v-if="!props.media.data.length"
            class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-16 text-center text-sm text-slate-500">
            Pustaka masih kosong. Unggah gambar pertama Anda.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <figure v-for="item in props.media.data" :key="item.id"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <img :src="item.url" :alt="item.alt ?? item.name" class="h-36 w-full object-cover">

                <figcaption class="p-3">
                    <p class="truncate text-xs font-semibold text-slate-700" :title="item.name">{{ item.name }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">{{ item.size_label }} · {{ item.created_at }}</p>

                    <div class="mt-2 flex gap-2">
                        <button type="button" class="text-xs font-semibold text-teal-600 transition hover:text-teal-700"
                            @click="copyPath(item)">
                            {{ copied === item.id ? 'Tersalin!' : 'Salin URL' }}
                        </button>
                        <button type="button" class="ml-auto text-xs font-semibold text-rose-500 transition hover:text-rose-600"
                            @click="destroy(item)">
                            Hapus
                        </button>
                    </div>
                </figcaption>
            </figure>
        </div>

        <div v-if="props.media.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <component :is="link.url ? 'a' : 'span'" v-for="link in props.media.links" :key="link.label" :href="link.url"
                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                :class="[
                    link.active ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200',
                    link.url ? 'hover:bg-teal-50' : 'cursor-default opacity-40',
                ]" v-html="link.label" />
        </div>
    </AdminLayout>
</template>
