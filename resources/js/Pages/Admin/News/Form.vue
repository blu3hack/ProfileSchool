<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

import AdminLayout from '../../../Components/Admin/AdminLayout.vue';
import ImagePicker from '../../../Components/Admin/ImagePicker.vue';
import TagInput from '../../../Components/Admin/TagInput.vue';

/**
 * Editor berita. Isi artikel disusun sebagai daftar blok
 * (paragraf, sub judul, daftar poin, kutipan) yang bisa ditambah,
 * diurutkan ulang, dan dihapus satu per satu.
 */
const props = defineProps({
    news: { type: Object, default: null },
    options: { type: Object, required: true },
});

const isEdit = computed(() => !!props.news);

const form = useForm({
    title: props.news?.title ?? '',
    slug: props.news?.slug ?? '',
    category: props.news?.category ?? props.options.categories[0],
    icon: props.news?.icon ?? '📰',
    accent: props.news?.accent ?? 'mint',
    excerpt: props.news?.excerpt ?? '',
    author: props.news?.author ?? '',
    read_time: props.news?.read_time ?? '',
    image: props.news?.image ?? '',
    image_caption: props.news?.image_caption ?? '',
    tags: props.news?.tags ?? [],
    gallery: props.news?.gallery?.map((item) => ({ src: item.src, caption: item.caption ?? '' })) ?? [],
    body: props.news?.body?.map((block) => ({
        type: block.type,
        text: block.text ?? '',
        cite: block.cite ?? '',
        items: block.items ?? [],
    })) ?? [{ type: 'paragraph', text: '', cite: '', items: [] }],
    is_published: props.news ? props.news.is_published : true,
    published_at: props.news?.published_at ?? new Date().toISOString().slice(0, 10),
});

const inputClass =
    'w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100';

// ------------------------------------------------------------ blok isi
const addBlock = (type) => {
    form.body.push({ type, text: '', cite: '', items: type === 'list' ? [''] : [] });
};

const removeBlock = (index) => form.body.splice(index, 1);

const moveBlock = (index, direction) => {
    const target = index + direction;

    if (target < 0 || target >= form.body.length) {
        return;
    }

    const [block] = form.body.splice(index, 1);
    form.body.splice(target, 0, block);
};

const addListItem = (block) => block.items.push('');
const removeListItem = (block, index) => block.items.splice(index, 1);

const blockLabel = (type) => props.options.blockTypes.find((b) => b.value === type)?.label ?? type;

// --------------------------------------------------------------- galeri
const addGalleryItem = () => form.gallery.push({ src: '', caption: '' });
const removeGalleryItem = (index) => form.gallery.splice(index, 1);

// --------------------------------------------------------------- simpan
const submit = () => {
    if (isEdit.value) {
        form.put(`/admin/berita/${props.news.id}`);
    } else {
        form.post('/admin/berita');
    }
};

/** Pesan galat untuk field bersarang, mis. `body.0.text`. */
const errorFor = (key) => form.errors[key] ?? '';
</script>

<template>
    <AdminLayout :title="isEdit ? '📝 Ubah Berita' : '📝 Tulis Berita'"
        subtitle="Lengkapi informasi berita, unggah banner, lalu susun isi artikel per blok.">
        <form class="grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
            <!-- =========================== KOLOM UTAMA =========================== -->
            <div class="space-y-6">
                <!-- Informasi dasar -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Informasi Berita</h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Judul</label>
                            <input v-model="form.title" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Contoh: Tim Robotik SMP Melaju ke Final Nasional">
                            <p v-if="errorFor('title')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('title') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Slug URL</label>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Kosongkan untuk dibuat otomatis dari judul. Alamatnya menjadi /berita/slug.
                            </p>
                            <input v-model="form.slug" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="tim-robotik-melaju-final">
                            <p v-if="errorFor('slug')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('slug') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Ringkasan</label>
                            <p class="mt-0.5 text-xs text-slate-500">Tampil di kartu berita pada beranda & indeks.</p>
                            <textarea v-model="form.excerpt" rows="3" :class="[inputClass, 'mt-2 resize-y']"></textarea>
                            <p v-if="errorFor('excerpt')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('excerpt') }}
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Penulis</label>
                                <input v-model="form.author" type="text" :class="[inputClass, 'mt-2']"
                                    placeholder="Humas Sekolah">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Waktu Baca</label>
                                <input v-model="form.read_time" type="text" :class="[inputClass, 'mt-2']"
                                    placeholder="Kosongkan untuk dihitung otomatis">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Tag</label>
                            <TagInput v-model="form.tags" class="mt-2" placeholder="Tambah tag lalu tekan Enter" />
                        </div>
                    </div>
                </section>

                <!-- Banner -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Banner Berita</h2>

                    <div class="mt-5 space-y-5">
                        <ImagePicker v-model="form.image" label="Gambar Banner"
                            hint="Tampil sebagai sampul kartu dan header halaman detail."
                            :preview-url="props.news?.image_url ?? ''" />
                        <p v-if="errorFor('image')" class="text-xs font-semibold text-rose-600">{{ errorFor('image') }}</p>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Keterangan Gambar</label>
                            <input v-model="form.image_caption" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Contoh: Tim robotik menguji purwarupa sebelum babak regional.">
                        </div>
                    </div>
                </section>

                <!-- Isi artikel -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="font-bold text-slate-900">Isi Artikel</h2>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="type in props.options.blockTypes" :key="type.value" type="button"
                                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-teal-400 hover:text-teal-600"
                                @click="addBlock(type.value)">
                                + {{ type.label }}
                            </button>
                        </div>
                    </div>

                    <p v-if="!form.body.length" class="mt-6 rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Belum ada blok. Tambahkan paragraf untuk mulai menulis.
                    </p>

                    <div v-for="(block, index) in form.body" :key="index"
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">
                                {{ blockLabel(block.type) }}
                            </span>
                            <div class="ml-auto flex gap-1">
                                <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                                    aria-label="Naikkan blok" @click="moveBlock(index, -1)">↑</button>
                                <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                                    aria-label="Turunkan blok" @click="moveBlock(index, 1)">↓</button>
                                <button type="button" class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs text-rose-500 transition hover:bg-rose-50"
                                    aria-label="Hapus blok" @click="removeBlock(index)">✕</button>
                            </div>
                        </div>

                        <!-- Daftar poin -->
                        <div v-if="block.type === 'list'" class="mt-3 space-y-2">
                            <div v-for="(item, itemIndex) in block.items" :key="itemIndex" class="flex gap-2">
                                <input v-model="block.items[itemIndex]" type="text" :class="inputClass"
                                    placeholder="Isi poin daftar">
                                <button type="button"
                                    class="shrink-0 rounded-xl border border-rose-200 px-3 text-sm text-rose-500 transition hover:bg-rose-50"
                                    aria-label="Hapus poin" @click="removeListItem(block, itemIndex)">✕</button>
                            </div>
                            <button type="button" class="text-xs font-semibold text-teal-600 hover:text-teal-700"
                                @click="addListItem(block)">
                                + Tambah poin
                            </button>
                        </div>

                        <!-- Paragraf / sub judul / kutipan -->
                        <template v-else>
                            <input v-if="block.type === 'heading'" v-model="block.text" type="text"
                                :class="[inputClass, 'mt-3']" placeholder="Teks sub judul">
                            <textarea v-else v-model="block.text" rows="4" :class="[inputClass, 'mt-3 resize-y']"
                                :placeholder="block.type === 'quote' ? 'Isi kutipan' : 'Tulis paragraf di sini'"></textarea>

                            <input v-if="block.type === 'quote'" v-model="block.cite" type="text"
                                :class="[inputClass, 'mt-2']" placeholder="Sumber kutipan, mis. Kepala Sekolah">
                        </template>
                    </div>
                </section>

                <!-- Galeri foto pendukung -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900">Foto Pendukung</h2>
                            <p class="mt-1 text-sm text-slate-500">Galeri tambahan yang menyertai artikel.</p>
                        </div>
                        <button type="button"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-teal-400 hover:text-teal-600"
                            @click="addGalleryItem">
                            + Tambah Foto
                        </button>
                    </div>

                    <div v-for="(item, index) in form.gallery" :key="index"
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <ImagePicker v-model="item.src" :label="`Foto ${index + 1}`"
                            :preview-url="props.news?.gallery?.[index]?.url ?? ''" />
                        <div class="mt-3 flex gap-2">
                            <input v-model="item.caption" type="text" :class="inputClass" placeholder="Keterangan foto (opsional)">
                            <button type="button"
                                class="shrink-0 rounded-xl border border-rose-200 px-3 text-sm text-rose-500 transition hover:bg-rose-50"
                                @click="removeGalleryItem(index)">Hapus</button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ============================== SAMPING ============================== -->
            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Publikasi</h2>

                    <label class="mt-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input v-model="form.is_published" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-teal-600">
                        Terbitkan berita ini
                    </label>

                    <div class="mt-5">
                        <label class="block text-sm font-semibold text-slate-700">Tanggal Terbit</label>
                        <input v-model="form.published_at" type="date" :class="[inputClass, 'mt-2']">
                    </div>

                    <div class="mt-6 flex flex-col gap-2">
                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan…' : (isEdit ? 'Simpan Perubahan' : 'Simpan Berita') }}
                        </button>
                        <Link href="/admin/berita"
                            class="rounded-xl px-5 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                            Batal
                        </Link>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Tampilan</h2>

                    <div class="mt-4 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Kategori</label>
                            <select v-model="form.category" :class="[inputClass, 'mt-2']">
                                <option v-for="category in props.options.categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Ikon (emoji)</label>
                            <input v-model="form.icon" type="text" maxlength="4" :class="[inputClass, 'mt-2']">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Warna Aksen</label>
                            <select v-model="form.accent" :class="[inputClass, 'mt-2']">
                                <option v-for="accent in props.options.accents" :key="accent.value" :value="accent.value">
                                    {{ accent.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </section>
            </aside>
        </form>
    </AdminLayout>
</template>
