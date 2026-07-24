<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

import AdminLayout from '../../../Components/Admin/AdminLayout.vue';
import ImagePicker from '../../../Components/Admin/ImagePicker.vue';
import TagInput from '../../../Components/Admin/TagInput.vue';

/**
 * Editor agenda "Next Event". Selain jadwal pelaksanaan (dipakai hitung
 * mundur), isinya disusun sebagai blok seperti berita, ditambah susunan
 * acara (rundown) yang bisa diurutkan.
 */
const props = defineProps({
    event: { type: Object, default: null },
    options: { type: Object, required: true },
});

const isEdit = computed(() => !!props.event);

const form = useForm({
    title: props.event?.title ?? '',
    slug: props.event?.slug ?? '',
    category: props.event?.category ?? props.options.categories[0],
    icon: props.event?.icon ?? '📅',
    accent: props.event?.accent ?? 'mint',
    excerpt: props.event?.excerpt ?? '',
    location: props.event?.location ?? '',
    organizer: props.event?.organizer ?? '',
    audience: props.event?.audience ?? '',
    registration_url: props.event?.registration_url ?? '',
    registration_label: props.event?.registration_label ?? '',
    image: props.event?.image ?? '',
    image_caption: props.event?.image_caption ?? '',
    tags: props.event?.tags ?? [],
    rundown: props.event?.rundown?.map((row) => ({
        time: row.time ?? '',
        title: row.title ?? '',
        description: row.description ?? '',
    })) ?? [],
    gallery: props.event?.gallery?.map((item) => ({ src: item.src, caption: item.caption ?? '' })) ?? [],
    body: props.event?.body?.map((block) => ({
        type: block.type,
        text: block.text ?? '',
        cite: block.cite ?? '',
        items: block.items ?? [],
    })) ?? [{ type: 'paragraph', text: '', cite: '', items: [] }],
    is_published: props.event ? props.event.is_published : true,
    starts_at: props.event?.starts_at ?? '',
    ends_at: props.event?.ends_at ?? '',
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

// -------------------------------------------------------------- rundown
const addRundownRow = () => form.rundown.push({ time: '', title: '', description: '' });
const removeRundownRow = (index) => form.rundown.splice(index, 1);

const moveRundownRow = (index, direction) => {
    const target = index + direction;

    if (target < 0 || target >= form.rundown.length) {
        return;
    }

    const [row] = form.rundown.splice(index, 1);
    form.rundown.splice(target, 0, row);
};

// --------------------------------------------------------------- galeri
const addGalleryItem = () => form.gallery.push({ src: '', caption: '' });
const removeGalleryItem = (index) => form.gallery.splice(index, 1);

// --------------------------------------------------------------- simpan
const submit = () => {
    if (isEdit.value) {
        form.put(`/admin/event/${props.event.id}`);
    } else {
        form.post('/admin/event');
    }
};

/** Pesan galat untuk field bersarang, mis. `body.0.text`. */
const errorFor = (key) => form.errors[key] ?? '';
</script>

<template>
    <AdminLayout :title="isEdit ? '📅 Ubah Agenda' : '📅 Tambah Agenda'"
        subtitle="Atur jadwal, unggah gambar, susun acara, lalu terbitkan agenda.">
        <form class="grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
            <!-- =========================== KOLOM UTAMA =========================== -->
            <div class="space-y-6">
                <!-- Informasi dasar -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Informasi Acara</h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Judul</label>
                            <input v-model="form.title" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Contoh: Gebyar Milad ke-15 Alazka Islamic School">
                            <p v-if="errorFor('title')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('title') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Slug URL</label>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Kosongkan untuk dibuat otomatis dari judul. Alamatnya menjadi /event/slug.
                            </p>
                            <input v-model="form.slug" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="gebyar-milad-ke-15">
                            <p v-if="errorFor('slug')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('slug') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Ringkasan</label>
                            <p class="mt-0.5 text-xs text-slate-500">Tampil di kartu agenda pada beranda & indeks.</p>
                            <textarea v-model="form.excerpt" rows="3" :class="[inputClass, 'mt-2 resize-y']"></textarea>
                            <p v-if="errorFor('excerpt')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('excerpt') }}
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Lokasi</label>
                                <input v-model="form.location" type="text" :class="[inputClass, 'mt-2']"
                                    placeholder="Aula Utama Alazka">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Peserta / Sasaran</label>
                                <input v-model="form.audience" type="text" :class="[inputClass, 'mt-2']"
                                    placeholder="Siswa SD & SMP, Wali Murid">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Penyelenggara</label>
                            <input v-model="form.organizer" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Panitia Milad / OSIS">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Tag</label>
                            <TagInput v-model="form.tags" class="mt-2" placeholder="Tambah tag lalu tekan Enter" />
                        </div>
                    </div>
                </section>

                <!-- Gambar -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Gambar Acara</h2>

                    <div class="mt-5 space-y-5">
                        <ImagePicker v-model="form.image" label="Gambar Utama"
                            hint="Tampil sebagai sampul kartu dan header halaman detail."
                            :preview-url="props.event?.image_url ?? ''" />
                        <p v-if="errorFor('image')" class="text-xs font-semibold text-rose-600">{{ errorFor('image') }}</p>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Keterangan Gambar</label>
                            <input v-model="form.image_caption" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Contoh: Panggung utama gebyar milad tahun lalu.">
                        </div>
                    </div>
                </section>

                <!-- Deskripsi acara (blok) -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="font-bold text-slate-900">Deskripsi Acara</h2>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="type in props.options.blockTypes" :key="type.value" type="button"
                                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-teal-400 hover:text-teal-600"
                                @click="addBlock(type.value)">
                                + {{ type.label }}
                            </button>
                        </div>
                    </div>

                    <p v-if="!form.body.length"
                        class="mt-6 rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Belum ada blok. Tambahkan paragraf untuk mulai menulis.
                    </p>

                    <div v-for="(block, index) in form.body" :key="index"
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-full bg-white px-3 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">
                                {{ blockLabel(block.type) }}
                            </span>
                            <div class="ml-auto flex gap-1">
                                <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                                    aria-label="Naikkan blok" @click="moveBlock(index, -1)">↑</button>
                                <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                                    aria-label="Turunkan blok" @click="moveBlock(index, 1)">↓</button>
                                <button type="button"
                                    class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs text-rose-500 transition hover:bg-rose-50"
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

                <!-- Susunan acara (rundown) -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900">Susunan Acara</h2>
                            <p class="mt-1 text-sm text-slate-500">Rangkaian kegiatan beserta jamnya (opsional).</p>
                        </div>
                        <button type="button"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-teal-400 hover:text-teal-600"
                            @click="addRundownRow">
                            + Tambah Baris
                        </button>
                    </div>

                    <div v-for="(row, index) in form.rundown" :key="index"
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex gap-2">
                            <input v-model="row.time" type="text" class="w-32 shrink-0 rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                placeholder="08.00">
                            <input v-model="row.title" type="text" :class="inputClass" placeholder="Nama kegiatan">
                            <div class="ml-auto flex shrink-0 gap-1">
                                <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                                    aria-label="Naikkan baris" @click="moveRundownRow(index, -1)">↑</button>
                                <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                                    aria-label="Turunkan baris" @click="moveRundownRow(index, 1)">↓</button>
                                <button type="button"
                                    class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs text-rose-500 transition hover:bg-rose-50"
                                    aria-label="Hapus baris" @click="removeRundownRow(index)">✕</button>
                            </div>
                        </div>
                        <input v-model="row.description" type="text" :class="[inputClass, 'mt-2']"
                            placeholder="Keterangan singkat (opsional)">
                    </div>
                </section>

                <!-- Galeri foto pendukung -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900">Foto Pendukung</h2>
                            <p class="mt-1 text-sm text-slate-500">Galeri tambahan yang menyertai acara.</p>
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
                            :preview-url="props.event?.gallery?.[index]?.url ?? ''" />
                        <div class="mt-3 flex gap-2">
                            <input v-model="item.caption" type="text" :class="inputClass"
                                placeholder="Keterangan foto (opsional)">
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
                    <h2 class="font-bold text-slate-900">Jadwal &amp; Publikasi</h2>

                    <div class="mt-5">
                        <label class="block text-sm font-semibold text-slate-700">Waktu Mulai</label>
                        <p class="mt-0.5 text-xs text-slate-500">Dipakai untuk hitung mundur di halaman publik.</p>
                        <input v-model="form.starts_at" type="datetime-local" :class="[inputClass, 'mt-2']">
                        <p v-if="errorFor('starts_at')" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ errorFor('starts_at') }}
                        </p>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-semibold text-slate-700">Waktu Selesai</label>
                        <p class="mt-0.5 text-xs text-slate-500">Opsional. Isi untuk acara berjam-jam / lintas hari.</p>
                        <input v-model="form.ends_at" type="datetime-local" :class="[inputClass, 'mt-2']">
                        <p v-if="errorFor('ends_at')" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ errorFor('ends_at') }}
                        </p>
                    </div>

                    <label class="mt-5 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input v-model="form.is_published" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-teal-600">
                        Terbitkan agenda ini
                    </label>

                    <div class="mt-6 flex flex-col gap-2">
                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan…' : (isEdit ? 'Simpan Perubahan' : 'Simpan Agenda') }}
                        </button>
                        <Link href="/admin/event"
                            class="rounded-xl px-5 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                            Batal
                        </Link>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Pendaftaran</h2>

                    <div class="mt-4 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Tautan Pendaftaran</label>
                            <input v-model="form.registration_url" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="https://wa.me/62… atau tautan formulir">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Label Tombol</label>
                            <input v-model="form.registration_label" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Daftar Sekarang">
                        </div>
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
