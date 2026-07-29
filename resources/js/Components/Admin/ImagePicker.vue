<script setup>
import { computed, ref } from 'vue';

import { getJson, postForm } from '../../lib/api';

/**
 * Pemilih gambar serbaguna: unggah berkas baru, pilih dari pustaka media,
 * atau tempel URL gambar dari luar.
 *
 * `modelValue` berisi path relatif hasil unggahan (mis. `uploads/2026/07/foto.jpg`)
 * atau URL penuh — keduanya diterima backend lewat helper MediaUrl.
 */
const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: 'Gambar' },
    hint: { type: String, default: '' },
    previewUrl: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const uploading = ref(false);
const error = ref('');
const libraryOpen = ref(false);
const library = ref([]);
const loadingLibrary = ref(false);
const fileInput = ref(null);
/** Pratinjau lokal setelah unggah, sebelum halaman dimuat ulang. */
const uploadedUrl = ref('');

const preview = computed(() => {
    const value = props.modelValue ?? '';

    if (uploadedUrl.value) {
        return uploadedUrl.value;
    }

    if (!value) {
        return '';
    }

    return value.startsWith('http') || value.startsWith('/') ? value : `/storage/${value}`;
});

const upload = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploading.value = true;
    error.value = '';

    try {
        const data = new FormData();
        data.append('file', file);

        const media = await postForm('/admin/media', data);

        uploadedUrl.value = media.url;
        emit('update:modelValue', media.path);
    } catch (e) {
        error.value = e.message;
    } finally {
        uploading.value = false;

        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};

const openLibrary = async () => {
    libraryOpen.value = !libraryOpen.value;

    if (!libraryOpen.value || library.value.length) {
        return;
    }

    loadingLibrary.value = true;

    try {
        library.value = await getJson('/admin/media/daftar');
    } catch (e) {
        error.value = e.message;
    } finally {
        loadingLibrary.value = false;
    }
};

const choose = (media) => {
    uploadedUrl.value = media.url;
    emit('update:modelValue', media.path);
    libraryOpen.value = false;
};

const clear = () => {
    uploadedUrl.value = '';
    emit('update:modelValue', '');
};
</script>

<template>
    <div>
        <label class="block text-sm font-semibold text-slate-700">{{ props.label }}</label>
        <p v-if="props.hint" class="mt-0.5 text-xs text-slate-500">{{ props.hint }}</p>

        <div class="mt-2 flex flex-col gap-4 sm:flex-row">
            <!-- Pratinjau -->
            <div
                class="flex h-32 w-full shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 sm:w-52">
                <img v-if="preview || props.previewUrl" :src="preview || props.previewUrl" alt=""
                    class="h-full w-full object-cover">
                <span v-else class="text-xs text-slate-400">Belum ada gambar</span>
            </div>

            <div class="flex-1">
                <div class="flex flex-wrap gap-2">
                    <label
                        class="cursor-pointer rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700"
                        :class="{ 'pointer-events-none opacity-60': uploading }">
                        {{ uploading ? 'Mengunggah…' : 'Unggah Gambar' }}
                        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="upload">
                    </label>

                    <button type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        @click="openLibrary">
                        Pilih dari Pustaka
                    </button>

                    <button v-if="props.modelValue" type="button"
                        class="rounded-lg border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                        @click="clear">
                        Hapus
                    </button>
                </div>

                <!-- URL / path juga bisa diketik manual -->
                <input :value="props.modelValue" type="text" placeholder="atau tempel URL gambar di sini"
                    class="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                    @input="emit('update:modelValue', $event.target.value); uploadedUrl = ''">

                <p class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP, atau GIF · maksimal 5 MB.</p>
                <p v-if="error" class="mt-1 text-xs font-semibold text-rose-600">{{ error }}</p>
            </div>
        </div>

        <!-- Pustaka media -->
        <div v-if="libraryOpen" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <p v-if="loadingLibrary" class="py-4 text-center text-sm text-slate-500">Memuat pustaka…</p>
            <p v-else-if="!library.length" class="py-4 text-center text-sm text-slate-500">
                Pustaka masih kosong. Unggah gambar pertama Anda.
            </p>
            <div v-else data-lenis-prevent
                class="admin-scroll grid max-h-64 grid-cols-3 gap-2 overflow-y-auto sm:grid-cols-5">
                <button v-for="media in library" :key="media.id" type="button"
                    class="group overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-teal-500"
                    :title="media.name" @click="choose(media)">
                    <img :src="media.url" :alt="media.alt ?? media.name" class="h-20 w-full object-cover">
                </button>
            </div>
        </div>
    </div>
</template>
