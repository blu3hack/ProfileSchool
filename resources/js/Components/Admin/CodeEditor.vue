<script setup>
import { onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue';

/**
 * Editor kode HTML untuk halaman kustom mode "Kode HTML".
 *
 * CodeMirror 6 dimuat lewat dynamic import — jadi berkasnya baru diunduh saat
 * form halaman kustom benar-benar dibuka, bukan ikut menumpang di bundel
 * halaman admin lain. Bila impornya gagal (jaringan mati, berkas build belum
 * ada), komponen jatuh ke `<textarea>` biasa: tetap bisa menempel & menyunting
 * kode, hanya tanpa pewarnaan sintaks.
 *
 * Kode yang disimpan TIDAK dipercaya apa adanya — server menyaringnya lewat
 * App\Support\HtmlSanitizer sebelum menyimpan maupun menampilkan.
 */
const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '<section class="…">…</section>' },
});

const emit = defineEmits(['update:modelValue']);

const host = ref(null);
/** `shallowRef`: EditorView adalah objek besar yang tak perlu reaktif dalam. */
const view = shallowRef(null);
const ready = ref(false);
const uploadError = ref('');

/** Perubahan yang berasal dari editor sendiri — jangan dipasang ulang. */
let internal = false;

const setValue = (value) => {
    internal = true;
    emit('update:modelValue', value);
    Promise.resolve().then(() => (internal = false));
};

onMounted(async () => {
    try {
        const [{ EditorView, basicSetup }, { html }] = await Promise.all([
            import('codemirror'),
            import('@codemirror/lang-html'),
        ]);

        view.value = new EditorView({
            doc: props.modelValue ?? '',
            parent: host.value,
            extensions: [
                basicSetup,
                html(),
                EditorView.lineWrapping,
                EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        setValue(update.state.doc.toString());
                    }
                }),
            ],
        });

        ready.value = true;
    } catch (error) {
        // Textarea cadangan sudah tampil; cukup catat sebabnya.
        console.warn('CodeMirror gagal dimuat, memakai editor teks biasa.', error);
    }
});

onBeforeUnmount(() => view.value?.destroy());

// Nilai dari luar (unggah berkas HTML, atau hasil saringan server saat form
// dimuat ulang) ditulis ulang ke dokumen editor.
watch(() => props.modelValue, (value) => {
    const editor = view.value;

    if (internal || !editor || editor.state.doc.toString() === value) {
        return;
    }

    editor.dispatch({
        changes: { from: 0, to: editor.state.doc.length, insert: value ?? '' },
    });
});

/**
 * Unggah berkas .html — dibaca langsung di peramban, tidak perlu menitipkannya
 * ke server dulu. Isinya masuk ke editor supaya admin bisa memeriksa dan
 * menyuntingnya sebelum menyimpan.
 */
const readFile = (event) => {
    const file = event.target.files?.[0];

    uploadError.value = '';

    if (!file) {
        return;
    }

    if (!/\.(html?|txt)$/i.test(file.name)) {
        uploadError.value = 'Hanya berkas .html, .htm, atau .txt yang bisa dibaca.';
        event.target.value = '';

        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        uploadError.value = 'Berkas terlalu besar (maksimal 2 MB).';
        event.target.value = '';

        return;
    }

    const reader = new FileReader();

    reader.onload = () => setValue(String(reader.result ?? ''));
    reader.onerror = () => (uploadError.value = 'Berkas gagal dibaca.');
    reader.readAsText(file, 'UTF-8');

    event.target.value = '';
};
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <label
                class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                📄 Unggah Berkas HTML
                <input type="file" accept=".html,.htm,.txt,text/html" class="hidden" @change="readFile">
            </label>

            <button type="button"
                class="rounded-lg border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                @click="setValue('')">
                Kosongkan
            </button>

            <span class="text-xs text-slate-400">
                {{ (props.modelValue ?? '').length.toLocaleString('id-ID') }} karakter
            </span>
        </div>

        <p v-if="uploadError" class="mt-2 text-xs font-semibold text-rose-600">{{ uploadError }}</p>

        <!-- Editor CodeMirror. `data-lenis-prevent` tidak diperlukan di /admin
             (smooth scroll dimatikan di sana), tapi dipasang sebagai pengaman
             sama seperti area bergulir lain di panel. -->
        <div v-show="ready" ref="host" data-lenis-prevent
            class="admin-code-editor mt-3 overflow-hidden rounded-xl border border-slate-300 bg-white"></div>

        <!-- Cadangan bila CodeMirror gagal dimuat. -->
        <textarea v-if="!ready" :value="props.modelValue" rows="18" spellcheck="false" :placeholder="props.placeholder"
            data-lenis-prevent
            class="admin-scroll mt-3 w-full rounded-xl border border-slate-300 px-3 py-2.5 font-mono text-xs leading-relaxed text-slate-800 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
            @input="setValue($event.target.value)"></textarea>
    </div>
</template>
