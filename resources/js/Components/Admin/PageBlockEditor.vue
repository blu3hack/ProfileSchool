<script setup>
import { ref } from 'vue';

import CodeEditor from './CodeEditor.vue';
import ImagePicker from './ImagePicker.vue';
import RichText from './RichText.vue';

/**
 * Penyusun blok halaman kustom — mode "Visual Builder".
 *
 * Satu blok = satu kartu yang bisa dilipat, dipindah, dan dihapus. Bentuk
 * datanya harus sama dengan App\Support\PageBlocks di server, karena di sana
 * blok yang masuk dibakukan & divalidasi.
 *
 * Daftarnya diubah LANGSUNG (push/splice) lewat `defineModel`: yang dipegang
 * komponen ini adalah array milik `useForm` di halaman induk, jadi setiap
 * perubahan langsung ikut terkirim saat form disimpan.
 */
const blocks = defineModel({ type: Array, default: () => [] });

const props = defineProps({
    options: { type: Object, required: true },
    /** Pesan galat dari server, mis. `blocks.2.text`. */
    errors: { type: Object, default: () => ({}) },
});

const inputClass =
    'w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100';

/** Kartu yang sedang dilipat — disimpan per objek blok, bukan per indeks,
 *  supaya tetap benar setelah blok dipindah urutannya. */
const collapsed = ref(new Set());

const toggleCollapse = (block) => {
    collapsed.value.has(block) ? collapsed.value.delete(block) : collapsed.value.add(block);
};

/** Blok baru beserta nilai awalnya. */
const BLANK = {
    richtext: () => ({ type: 'richtext', html: '' }),
    heading: () => ({ type: 'heading', text: '', level: 'h2' }),
    image: () => ({ type: 'image', src: '', alt: '', caption: '', width: 'normal' }),
    gallery: () => ({ type: 'gallery', columns: 3, items: [{ src: '', caption: '' }] }),
    quote: () => ({ type: 'quote', text: '', cite: '' }),
    embed: () => ({ type: 'embed', url: '', title: '', caption: '' }),
    cta: () => ({ type: 'cta', title: '', description: '', label: '', href: '', style: 'primary' }),
    cards: () => ({
        type: 'cards',
        columns: 3,
        items: [{ icon: '', title: '', description: '', image: '', href: '' }],
    }),
    html: () => ({ type: 'html', html: '' }),
};

const addBlock = (type) => blocks.value.push(BLANK[type]());

const removeBlock = (index) => {
    if (window.confirm('Hapus blok ini?')) {
        blocks.value.splice(index, 1);
    }
};

const moveBlock = (index, direction) => {
    const target = index + direction;

    if (target < 0 || target >= blocks.value.length) {
        return;
    }

    const [block] = blocks.value.splice(index, 1);
    blocks.value.splice(target, 0, block);
};

const duplicateBlock = (index) => {
    // Salinan lewat JSON: isi blok memang murni data (teks, angka, daftar),
    // dan `structuredClone` bisa menolak objek reaktif Vue.
    blocks.value.splice(index + 1, 0, JSON.parse(JSON.stringify(blocks.value[index])));
};

const addItem = (block) => {
    block.items.push(block.type === 'gallery'
        ? { src: '', caption: '' }
        : { icon: '', title: '', description: '', image: '', href: '' });
};

const removeItem = (block, index) => block.items.splice(index, 1);

const meta = (type) => props.options.blockTypes.find((item) => item.value === type) ?? { label: type, icon: '🧱' };

/** Ringkasan isi blok untuk judul kartu saat dilipat. */
const summary = (block) => {
    const plain = (html) => (html ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

    const text = {
        richtext: () => plain(block.html),
        html: () => plain(block.html) || `${(block.html ?? '').length} karakter kode`,
        heading: () => block.text,
        image: () => block.caption || block.alt || block.src,
        gallery: () => `${block.items?.length ?? 0} foto`,
        quote: () => block.text,
        embed: () => block.title || block.url,
        cta: () => block.title || block.label,
        cards: () => `${block.items?.length ?? 0} kartu`,
    }[block.type]?.() ?? '';

    return text.length > 70 ? `${text.slice(0, 70)}…` : text;
};

const errorFor = (key) => props.errors[key] ?? '';
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-900">Isi Halaman</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Tambahkan blok sesuai kebutuhan, lalu urutkan dengan tombol ↑ ↓.
                </p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button v-for="type in props.options.blockTypes" :key="type.value" type="button"
                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-teal-400 hover:text-teal-600"
                @click="addBlock(type.value)">
                + {{ type.icon }} {{ type.label }}
            </button>
        </div>

        <p v-if="!blocks.length" class="mt-6 rounded-xl bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
            Belum ada blok. Mulai dengan “+ 📝 Teks / Paragraf”.
        </p>

        <div v-for="(block, index) in blocks" :key="index"
            class="mt-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
            <!-- ====================== KEPALA KARTU BLOK ====================== -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                    class="rounded-full bg-white px-3 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200 transition hover:text-teal-600"
                    :title="collapsed.has(block) ? 'Buka blok' : 'Lipat blok'" @click="toggleCollapse(block)">
                    {{ collapsed.has(block) ? '▸' : '▾' }} {{ meta(block.type).icon }} {{ meta(block.type).label }}
                </button>

                <span v-if="collapsed.has(block) && summary(block)" class="truncate text-xs text-slate-500">
                    {{ summary(block) }}
                </span>

                <div class="ml-auto flex gap-1">
                    <button type="button"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                        aria-label="Naikkan blok" @click="moveBlock(index, -1)">↑</button>
                    <button type="button"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                        aria-label="Turunkan blok" @click="moveBlock(index, 1)">↓</button>
                    <button type="button"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 transition hover:text-teal-600"
                        aria-label="Duplikat blok" @click="duplicateBlock(index)">⧉</button>
                    <button type="button"
                        class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs text-rose-500 transition hover:bg-rose-50"
                        aria-label="Hapus blok" @click="removeBlock(index)">✕</button>
                </div>
            </div>

            <div v-show="!collapsed.has(block)" class="mt-3 space-y-3">
                <!-- =========================== TEKS KAYA =========================== -->
                <RichText v-if="block.type === 'richtext'" v-model="block.html" />

                <!-- =========================== SUB JUDUL =========================== -->
                <template v-else-if="block.type === 'heading'">
                    <input v-model="block.text" type="text" :class="inputClass" placeholder="Teks sub judul">
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Ukuran</label>
                        <select v-model="block.level" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="h2">Besar (H2)</option>
                            <option value="h3">Kecil (H3)</option>
                        </select>
                    </div>
                </template>

                <!-- ============================ GAMBAR ============================ -->
                <template v-else-if="block.type === 'image'">
                    <ImagePicker v-model="block.src" label="Gambar" :preview-url="block.src_url ?? ''" />
                    <input v-model="block.alt" type="text" :class="inputClass"
                        placeholder="Teks alternatif (dibaca pembaca layar)">
                    <input v-model="block.caption" type="text" :class="inputClass" placeholder="Keterangan (opsional)">
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Lebar</label>
                        <select v-model="block.width" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option v-for="width in props.options.widths" :key="width.value" :value="width.value">
                                {{ width.label }}
                            </option>
                        </select>
                    </div>
                </template>

                <!-- ============================ GALERI ============================ -->
                <template v-else-if="block.type === 'gallery'">
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Jumlah kolom</label>
                        <select v-model.number="block.columns" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option v-for="column in props.options.columns" :key="column" :value="column">
                                {{ column }} kolom
                            </option>
                        </select>
                    </div>

                    <div v-for="(item, itemIndex) in block.items" :key="itemIndex"
                        class="rounded-xl border border-slate-200 bg-white p-3">
                        <ImagePicker v-model="item.src" :label="`Foto ${itemIndex + 1}`"
                            :preview-url="item.src_url ?? ''" />
                        <div class="mt-3 flex gap-2">
                            <input v-model="item.caption" type="text" :class="inputClass"
                                placeholder="Keterangan foto (opsional)">
                            <button type="button"
                                class="shrink-0 rounded-xl border border-rose-200 px-3 text-sm text-rose-500 transition hover:bg-rose-50"
                                @click="removeItem(block, itemIndex)">Hapus</button>
                        </div>
                    </div>

                    <button type="button" class="text-xs font-semibold text-teal-600 hover:text-teal-700"
                        @click="addItem(block)">+ Tambah foto</button>
                </template>

                <!-- =========================== KUTIPAN =========================== -->
                <template v-else-if="block.type === 'quote'">
                    <textarea v-model="block.text" rows="3" :class="[inputClass, 'resize-y']"
                        placeholder="Isi kutipan"></textarea>
                    <input v-model="block.cite" type="text" :class="inputClass"
                        placeholder="Sumber kutipan, mis. Kepala Sekolah">
                </template>

                <!-- ======================= SEMATAN VIDEO/URL ======================= -->
                <template v-else-if="block.type === 'embed'">
                    <input v-model="block.url" type="text" :class="inputClass"
                        placeholder="https://www.youtube.com/watch?v=…">
                    <p class="text-xs text-slate-500">
                        Tempel tautan biasa — YouTube, Vimeo, Google Drive/Docs/Form, Maps, Spotify, dan sejenisnya
                        otomatis diubah ke bentuk sematan. Tautan di luar daftar itu tampil sebagai tautan biasa.
                    </p>
                    <input v-model="block.title" type="text" :class="inputClass"
                        placeholder="Judul sematan (untuk pembaca layar)">
                    <input v-model="block.caption" type="text" :class="inputClass" placeholder="Keterangan (opsional)">
                </template>

                <!-- ========================= TOMBOL AJAKAN ========================= -->
                <template v-else-if="block.type === 'cta'">
                    <input v-model="block.title" type="text" :class="inputClass" placeholder="Judul kotak ajakan">
                    <textarea v-model="block.description" rows="2" :class="[inputClass, 'resize-y']"
                        placeholder="Penjelasan singkat (opsional)"></textarea>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input v-model="block.label" type="text" :class="inputClass" placeholder="Teks tombol, mis. Daftar PPDB">
                        <input v-model="block.href" type="text" :class="inputClass" placeholder="https://… atau /berita">
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Gaya tombol</label>
                        <select v-model="block.style" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option v-for="style in props.options.ctaStyles" :key="style.value" :value="style.value">
                                {{ style.label }}
                            </option>
                        </select>
                    </div>
                </template>

                <!-- ========================== GRID / KARTU ========================== -->
                <template v-else-if="block.type === 'cards'">
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Jumlah kolom</label>
                        <select v-model.number="block.columns" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option v-for="column in props.options.columns" :key="column" :value="column">
                                {{ column }} kolom
                            </option>
                        </select>
                    </div>

                    <div v-for="(item, itemIndex) in block.items" :key="itemIndex"
                        class="space-y-3 rounded-xl border border-slate-200 bg-white p-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">Kartu {{ itemIndex + 1 }}</span>
                            <button type="button"
                                class="ml-auto rounded-lg border border-rose-200 px-2 py-1 text-xs text-rose-500 transition hover:bg-rose-50"
                                @click="removeItem(block, itemIndex)">Hapus</button>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-[6rem_1fr]">
                            <input v-model="item.icon" type="text" maxlength="4" :class="inputClass" placeholder="🎓">
                            <input v-model="item.title" type="text" :class="inputClass" placeholder="Judul kartu">
                        </div>
                        <textarea v-model="item.description" rows="2" :class="[inputClass, 'resize-y']"
                            placeholder="Deskripsi singkat"></textarea>
                        <input v-model="item.href" type="text" :class="inputClass"
                            placeholder="Tautan kartu (opsional), mis. /berita">
                        <ImagePicker v-model="item.image" label="Gambar kartu (opsional)"
                            :preview-url="item.image_url ?? ''" />
                    </div>

                    <button type="button" class="text-xs font-semibold text-teal-600 hover:text-teal-700"
                        @click="addItem(block)">+ Tambah kartu</button>
                </template>

                <!-- ========================== KODE HTML ========================== -->
                <template v-else-if="block.type === 'html'">
                    <CodeEditor v-model="block.html" />
                    <p class="text-xs text-slate-500">
                        Kode disaring di server: tag &lt;script&gt;, atribut on… , dan formulir dibuang;
                        &lt;style&gt; serta tata letak kustom tetap berjalan.
                    </p>
                </template>

                <p v-if="errorFor(`blocks.${index}.html`) || errorFor(`blocks.${index}.text`) || errorFor(`blocks.${index}.url`)"
                    class="text-xs font-semibold text-rose-600">
                    {{ errorFor(`blocks.${index}.html`) || errorFor(`blocks.${index}.text`) || errorFor(`blocks.${index}.url`) }}
                </p>
            </div>
        </div>
    </section>
</template>
