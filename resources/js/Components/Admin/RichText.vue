<script setup>
import { onMounted, ref, watch } from 'vue';

/**
 * Editor teks kaya (WYSIWYG) untuk blok "Teks / Paragraf" halaman kustom.
 *
 * Dibangun di atas `contenteditable` bawaan peramban, bukan pustaka editor.
 * Alasannya: yang dibutuhkan cuma tebal/miring/daftar/tautan/sub judul, dan
 * satu pustaka editor lengkap berarti ratusan kilobita JavaScript untuk panel
 * admin yang dipakai beberapa orang — sementara situsnya sendiri menjaga
 * bundelnya tetap ringan.
 *
 * `document.execCommand` memang berstatus deprecated, tapi masih didukung
 * setiap peramban arus utama dan belum punya penggantinya (Editing Context API
 * belum tersedia luas). Tidak ada yang runtuh bila suatu saat ia dimatikan:
 * kotaknya tetap bisa diketik, hanya tombol formatnya yang berhenti bekerja.
 *
 * HTML apa pun yang keluar dari sini disaring ulang di server
 * (App\Support\HtmlSanitizer), jadi tempelan dari Word/situs lain tidak bisa
 * menyelipkan skrip.
 */
const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Tulis paragraf di sini…' },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
/** Menandai perubahan yang berasal dari ketikan, agar tak menimpa kursor. */
const typing = ref(false);

const TOOLS = [
    { label: 'B', title: 'Tebal', command: 'bold', class: 'font-bold' },
    { label: 'I', title: 'Miring', command: 'italic', class: 'italic' },
    { label: 'U', title: 'Garis bawah', command: 'underline', class: 'underline' },
    { label: 'H2', title: 'Sub judul besar', command: 'formatBlock', value: 'h2' },
    { label: 'H3', title: 'Sub judul kecil', command: 'formatBlock', value: 'h3' },
    { label: '¶', title: 'Paragraf biasa', command: 'formatBlock', value: 'p' },
    { label: '• Daftar', title: 'Daftar berbulat', command: 'insertUnorderedList' },
    { label: '1. Daftar', title: 'Daftar bernomor', command: 'insertOrderedList' },
    { label: '❝', title: 'Kutipan', command: 'formatBlock', value: 'blockquote' },
];

const sync = () => {
    typing.value = true;
    emit('update:modelValue', editor.value?.innerHTML ?? '');
    // Dilepas di microtask berikutnya: `watch` di bawah berjalan setelah emit.
    Promise.resolve().then(() => (typing.value = false));
};

const run = (tool) => {
    editor.value?.focus();
    document.execCommand(tool.command, false, tool.value ?? null);
    sync();
};

const clearFormat = () => {
    editor.value?.focus();
    document.execCommand('removeFormat');
    document.execCommand('formatBlock', false, 'p');
    sync();
};

/** Tautan: alamatnya diminta lewat prompt sederhana, lalu dipasang ke seleksi. */
const addLink = () => {
    const url = window.prompt('Alamat tautan (contoh: https://smpialazka.sch.id/berita)');

    if (!url) {
        return;
    }

    editor.value?.focus();
    document.execCommand('createLink', false, url.trim());
    sync();
};

const removeLink = () => {
    editor.value?.focus();
    document.execCommand('unlink');
    sync();
};

/**
 * Enter membuat paragraf baru, bukan `<div>` — supaya jarak antar paragraf di
 * halaman publik sama dengan blok teks lainnya. Peramban berbasis Chromium
 * memakai `<div>` bila `defaultParagraphSeparator` tidak disetel.
 */
onMounted(() => {
    document.execCommand('defaultParagraphSeparator', false, 'p');

    if (editor.value) {
        editor.value.innerHTML = props.modelValue ?? '';
    }
});

// Nilai dari luar (mis. blok dipindah urutannya, atau form dimuat ulang)
// dipasang ulang; ketikan sendiri dilewati agar kursor tidak melompat ke awal.
watch(() => props.modelValue, (value) => {
    if (!typing.value && editor.value && editor.value.innerHTML !== value) {
        editor.value.innerHTML = value ?? '';
    }
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white focus-within:border-teal-500 focus-within:ring-2 focus-within:ring-teal-100">
        <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 px-2 py-1.5">
            <button v-for="tool in TOOLS" :key="tool.label" type="button" :title="tool.title"
                class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-teal-600"
                :class="tool.class" @click.prevent="run(tool)">
                {{ tool.label }}
            </button>

            <span class="mx-1 h-4 w-px bg-slate-300" aria-hidden="true"></span>

            <button type="button" title="Tambah tautan"
                class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-teal-600"
                @click.prevent="addLink">🔗 Tautan</button>
            <button type="button" title="Hapus tautan"
                class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-teal-600"
                @click.prevent="removeLink">⛓️‍💥</button>
            <button type="button" title="Bersihkan format"
                class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-rose-500"
                @click.prevent="clearFormat">Bersihkan</button>
        </div>

        <!-- `admin-rich-text` memberi tag polos di dalamnya tampilan dasar,
             lihat resources/css/app.css. -->
        <div ref="editor" contenteditable="true" role="textbox" aria-multiline="true"
            :data-placeholder="props.placeholder" data-lenis-prevent
            class="admin-rich-text admin-scroll max-h-[28rem] min-h-32 overflow-y-auto px-4 py-3 text-sm leading-relaxed text-slate-800 outline-none"
            @input="sync" @blur="sync"></div>
    </div>
</template>
