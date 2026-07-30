<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';

import AdminLayout from '../../../Components/Admin/AdminLayout.vue';
import CodeEditor from '../../../Components/Admin/CodeEditor.vue';
import ImagePicker from '../../../Components/Admin/ImagePicker.vue';
import PageBlockEditor from '../../../Components/Admin/PageBlockEditor.vue';

/**
 * Editor halaman kustom.
 *
 * Dua mode pengisian, dipilih di kartu paling atas dan bisa ditukar kapan saja:
 * isi kedua mode disimpan di kolom yang berbeda, jadi berpindah mode tidak
 * menghapus pekerjaan yang sudah dilakukan di mode sebelumnya.
 */
const props = defineProps({
    page: { type: Object, default: null },
    options: { type: Object, required: true },
});

const isEdit = computed(() => !!props.page);

const form = useForm({
    title: props.page?.title ?? '',
    slug: props.page?.slug ?? '',
    mode: props.page?.mode ?? 'builder',
    eyebrow: props.page?.eyebrow ?? '',
    summary: props.page?.summary ?? '',
    hero_image: props.page?.hero_image ?? '',
    blocks: props.page?.blocks ?? [],
    html: props.page?.html ?? '',
    meta_title: props.page?.meta_title ?? '',
    meta_description: props.page?.meta_description ?? '',
    og_image: props.page?.og_image ?? '',
    is_published: props.page ? props.page.is_published : false,
    published_at: props.page?.published_at ?? new Date().toISOString().slice(0, 10),
});

const inputClass =
    'w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100';

/**
 * Slug diusulkan dari judul selama admin belum menyentuh kolomnya sendiri —
 * dan hanya untuk halaman baru: mengubah alamat halaman yang sudah terbit akan
 * mematikan tautan yang sudah tersebar.
 */
const slugTouched = ref(isEdit.value);

const slugify = (value) => value
    .toLowerCase()
    .normalize('NFD')
    // Buang tanda diakritik yang terpisah setelah normalisasi (é → e).
    .replace(/\p{Diacritic}/gu, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 100);

watch(() => form.title, (value) => {
    if (!slugTouched.value) {
        form.slug = slugify(value);
    }
});

const previewUrl = computed(() => `${props.options.baseUrl}${form.slug || '…'}`);

/** Alamat yang sudah dipegang situs — dicegah sebelum menekan simpan. */
const slugClash = computed(() => form.slug && props.options.reserved.includes(form.slug));

/**
 * Setelah tersimpan, isi editor disegarkan dengan apa yang benar-benar dipakai
 * halaman: hasil saringan HtmlSanitizer beserta blok yang sudah dibakukan
 * (blok kosong terbuang, tautan tak aman dikosongkan). Tanpa ini, editor masih
 * memperlihatkan tempelan mentah tadi — dan admin tidak pernah tahu bagian mana
 * yang ikut dibuang sebelum ia memuat ulang halamannya.
 */
const syncFromServer = () => {
    form.html = props.page?.html ?? '';
    // Disalin, bukan dirujuk: penyusun blok mengubah array ini di tempat, dan
    // prop halaman bukan miliknya untuk diubah.
    form.blocks = JSON.parse(JSON.stringify(props.page?.blocks ?? []));
    form.defaults();
};

const submit = () => {
    if (isEdit.value) {
        form.put(`/admin/halaman/${props.page.id}`, {
            preserveScroll: true,
            onSuccess: syncFromServer,
        });
    } else {
        form.post('/admin/halaman');
    }
};

const destroy = () => {
    if (window.confirm(`Hapus halaman "${form.title}"? Alamat /${form.slug} akan menjadi 404.`)) {
        router.delete(`/admin/halaman/${props.page.id}`);
    }
};

const errorFor = (key) => form.errors[key] ?? '';
</script>

<template>
    <AdminLayout :title="isEdit ? '📄 Ubah Halaman' : '📄 Buat Halaman'"
        subtitle="Halaman dengan alamat sendiri di situs — susun per blok, atau tempel kode HTML sendiri.">
        <form class="grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
            <!-- =========================== KOLOM UTAMA =========================== -->
            <div class="space-y-6">
                <!-- Informasi dasar & alamat -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Identitas Halaman</h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Judul Halaman</label>
                            <input v-model="form.title" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Contoh: Data Siswa">
                            <p v-if="errorFor('title')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('title') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Alamat (URL Slug)</label>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Huruf kecil, angka, dan tanda hubung. Kosongkan untuk dibuat otomatis dari judul.
                            </p>
                            <div class="mt-2 flex items-center overflow-hidden rounded-xl border border-slate-300 focus-within:border-teal-500 focus-within:ring-2 focus-within:ring-teal-100">
                                <span class="shrink-0 border-r border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs text-slate-500">
                                    {{ props.options.baseUrl }}
                                </span>
                                <input v-model="form.slug" type="text" placeholder="datasiswa"
                                    class="w-full px-3 py-2.5 font-mono text-sm outline-none"
                                    @input="slugTouched = true">
                            </div>
                            <p v-if="errorFor('slug')" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ errorFor('slug') }}
                            </p>
                            <p v-else-if="slugClash" class="mt-1 text-xs font-semibold text-rose-600">
                                “{{ form.slug }}” adalah alamat halaman asli situs. Pilih alamat lain.
                            </p>
                            <p v-else class="mt-1 text-xs text-slate-400">
                                Halaman akan terbuka di <span class="font-mono">{{ previewUrl }}</span>
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Pilihan mode -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Cara Mengisi Halaman</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Bisa ditukar kapan saja — isi kedua mode disimpan terpisah, jadi tidak ada yang hilang.
                    </p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label v-for="mode in props.options.modes" :key="mode.value"
                            class="cursor-pointer rounded-xl border-2 p-4 transition"
                            :class="form.mode === mode.value
                                ? 'border-teal-500 bg-teal-50/60'
                                : 'border-slate-200 hover:border-slate-300'">
                            <span class="flex items-center gap-2">
                                <input v-model="form.mode" type="radio" :value="mode.value"
                                    class="h-4 w-4 text-teal-600">
                                <span class="text-sm font-bold text-slate-800">
                                    {{ mode.value === 'builder' ? '🧩' : '🧬' }} {{ mode.label }}
                                </span>
                            </span>
                            <span class="mt-2 block text-xs leading-relaxed text-slate-500">
                                {{ mode.value === 'builder'
                                    ? 'Susun halaman dari blok siap pakai: teks kaya, gambar, galeri, kutipan, sematan video, tombol, dan grid kartu.'
                                    : 'Tempel atau unggah HTML+CSS sendiri. Kode disaring di server: skrip & formulir dibuang, tata letak dan gaya tetap berjalan.' }}
                            </span>
                        </label>
                    </div>
                </section>

                <!-- Isi halaman: satu dari dua mode -->
                <PageBlockEditor v-if="form.mode === 'builder'" v-model="form.blocks" :options="props.options"
                    :errors="form.errors" />

                <section v-else class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Kode HTML</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Tempel kode, atau unggah berkas .html yang sudah disiapkan. Yang tersimpan adalah hasil
                        saringan keamanan — jadi setelah disimpan, kode di editor ini bisa berbeda sedikit dari
                        yang Anda tempel.
                    </p>

                    <div class="mt-4">
                        <CodeEditor v-model="form.html" />
                        <p v-if="errorFor('html')" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ errorFor('html') }}
                        </p>
                    </div>

                    <details class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-600">
                        <summary class="cursor-pointer font-semibold text-slate-700">
                            Apa saja yang boleh dipakai?
                        </summary>
                        <p class="mt-2 leading-relaxed">
                            <strong>Boleh:</strong> seluruh tag tata letak &amp; teks beserta <code>class</code> dan
                            <code>style</code>-nya, blok <code>&lt;style&gt;</code>, tabel, gambar, ikon SVG, dan
                            <code>&lt;iframe&gt;</code> menuju layanan sematan yang dikenal
                            ({{ props.options.embedHosts.slice(0, 6).join(', ') }}, …).
                        </p>
                        <p class="mt-2 leading-relaxed">
                            <strong>Dibuang:</strong> <code>&lt;script&gt;</code>, atribut penangan event
                            (<code>onclick</code>, <code>onerror</code>, …), tautan <code>javascript:</code>,
                            <code>&lt;iframe&gt;</code> ke situs lain, serta seluruh elemen formulir
                            (<code>&lt;form&gt;</code>, <code>&lt;input&gt;</code>) — untuk formulir, sematkan
                            Google Form lewat <code>&lt;iframe&gt;</code>.
                        </p>
                    </details>
                </section>

                <!-- Kepala halaman -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Kepala Halaman</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Bagian paling atas halaman: label kecil, ringkasan di bawah judul, dan foto latar.
                    </p>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Label Kecil</label>
                            <input v-model="form.eyebrow" type="text" :class="[inputClass, 'mt-2']"
                                placeholder="Contoh: Informasi Akademik">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Ringkasan</label>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Satu-dua kalimat di bawah judul. Dipakai juga sebagai deskripsi SEO bila kolom SEO
                                dikosongkan.
                            </p>
                            <textarea v-model="form.summary" rows="3" :class="[inputClass, 'mt-2 resize-y']"></textarea>
                        </div>

                        <ImagePicker v-model="form.hero_image" label="Foto Latar Kepala Halaman"
                            hint="Opsional. Lanskap, disarankan minimal 1600px."
                            :preview-url="props.page?.hero_image_url ?? ''" />
                    </div>
                </section>

                <!-- SEO -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">SEO &amp; Pratinjau Bagikan</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Yang tampil di hasil pencarian Google dan pratinjau tautan WhatsApp/Facebook.
                    </p>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Meta Title</label>
                            <input v-model="form.meta_title" type="text" :class="[inputClass, 'mt-2']"
                                :placeholder="form.title || 'Kosongkan untuk memakai judul halaman'">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Meta Description</label>
                            <textarea v-model="form.meta_description" rows="2" maxlength="300"
                                :class="[inputClass, 'mt-2 resize-y']"
                                placeholder="Kosongkan untuk memakai ringkasan halaman"></textarea>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ (form.meta_description ?? '').length }}/300 karakter
                            </p>
                        </div>

                        <ImagePicker v-model="form.og_image" label="Gambar OpenGraph"
                            hint="Gambar pratinjau saat tautan dibagikan. Kosongkan untuk memakai foto kepala halaman."
                            :preview-url="props.page?.og_image_url ?? ''" />
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
                        Terbitkan halaman ini
                    </label>
                    <p class="mt-2 text-xs text-slate-500">
                        Selama masih draf, alamatnya menghasilkan 404 bagi pengunjung — Anda sendiri tetap bisa
                        membukanya sebagai pratinjau.
                    </p>

                    <div class="mt-5">
                        <label class="block text-sm font-semibold text-slate-700">Tanggal Terbit</label>
                        <input v-model="form.published_at" type="date" :class="[inputClass, 'mt-2']">
                    </div>

                    <div class="mt-6 flex flex-col gap-2">
                        <button type="submit" :disabled="form.processing || slugClash"
                            class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan…' : (isEdit ? 'Simpan Perubahan' : 'Simpan Halaman') }}
                        </button>

                        <a v-if="isEdit" :href="`/${props.page.slug}`" target="_blank"
                            class="rounded-xl border border-slate-200 px-5 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Lihat Halaman ↗
                        </a>

                        <Link href="/admin/halaman"
                            class="rounded-xl px-5 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                            Kembali ke Daftar
                        </Link>

                        <button v-if="isEdit" type="button"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-rose-500 transition hover:bg-rose-50"
                            @click="destroy">
                            Hapus Halaman
                        </button>
                    </div>
                </section>

                <section v-if="isEdit" class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-bold text-slate-900">Alamat</h2>
                    <p class="mt-2 break-all font-mono text-xs text-slate-500">{{ props.page.url }}</p>
                    <p class="mt-3 text-xs text-slate-500">
                        Mengubah alamat akan memutus tautan lama yang sudah dibagikan. Bila perlu, buat
                        <Link href="/admin/koleksi/shortlinks" class="font-semibold text-teal-600">tautan pendek</Link>
                        dari alamat lama ke alamat baru.
                    </p>
                </section>
            </aside>
        </form>
    </AdminLayout>
</template>
