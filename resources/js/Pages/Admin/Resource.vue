<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

import AdminLayout from '../../Components/Admin/AdminLayout.vue';
import FormField from '../../Components/Admin/FormField.vue';

/**
 * Halaman kelola untuk semua koleksi konten sederhana (statistik, pilar,
 * kegiatan, prestasi, kontak, media sosial, menu).
 *
 * Struktur tabel & formnya datang dari server (config/admin_resources.php),
 * jadi satu halaman ini melayani semua jenis konten.
 */
const props = defineProps({
    resource: { type: String, required: true },
    meta: { type: Object, required: true },
    fields: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
});

const base = computed(() => `/admin/koleksi/${props.resource}`);

/** null = form tertutup, 'new' = tambah, angka = id yang sedang diubah. */
const editing = ref(null);

const blankValues = () => {
    const values = { is_active: true };

    props.fields.forEach((field) => {
        values[field.name] = field.default ?? (field.type === 'tags' ? [] : '');
    });

    return values;
};

const form = useForm(blankValues());

const openCreate = () => {
    form.defaults(blankValues());
    form.reset();
    form.clearErrors();
    editing.value = 'new';
};

const openEdit = (item) => {
    const values = { is_active: !!item.is_active };

    props.fields.forEach((field) => {
        const value = item[field.name];

        values[field.name] = field.type === 'tags'
            ? (Array.isArray(value) ? value : [])
            : (value ?? '');
    });

    form.defaults(values);
    form.reset();
    form.clearErrors();
    editing.value = item.id;
};

const close = () => {
    editing.value = null;
    form.clearErrors();
};

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => close() };

    if (editing.value === 'new') {
        form.post(base.value, options);
    } else {
        form.put(`${base.value}/${editing.value}`, options);
    }
};

const destroy = (item) => {
    const name = item.title ?? item.label ?? item.slug ?? item.value ?? 'item ini';

    if (!window.confirm(`Hapus "${name}"? Tindakan ini tidak bisa dibatalkan.`)) {
        return;
    }

    router.delete(`${base.value}/${item.id}`, { preserveScroll: true });
};

const move = (item, direction) => {
    router.patch(`${base.value}/${item.id}/urutan`, { direction }, { preserveScroll: true });
};

const toggle = (item) => {
    router.patch(`${base.value}/${item.id}/tampil`, {}, { preserveScroll: true });
};

/**
 * Judul kolom tabel diambil dari label field yang sesuai. Kolom yang bukan
 * field (mis. penghitung kunjungan) mengambil judulnya dari meta.columnLabels.
 */
const columnLabel = (name) => props.fields.find((field) => field.name === name)?.label
    ?? props.meta.columnLabels?.[name]
    ?? name;

const imageFields = computed(() => props.fields.filter((field) => field.type === 'image'));
</script>

<template>
    <AdminLayout :title="`${props.meta.icon} ${props.meta.label}`" :subtitle="props.meta.description">
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <button type="button"
                class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700"
                @click="openCreate">
                + Tambah {{ props.meta.singular }}
            </button>
            <span class="text-sm text-slate-500">{{ props.items.length }} item tersimpan</span>
        </div>

        <!-- ============================== TABEL ==============================
             `overflow-x-auto` (dulu `overflow-hidden`): di layar sempit kolom
             yang melebihi lebar layar dulu terpotong tanpa bisa dijangkau.
             Sudutnya tetap membulat karena kotak ini tetap memotong isinya. -->
        <div data-lenis-prevent class="admin-scroll overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <div v-if="!props.items.length" class="px-5 py-12 text-center text-sm text-slate-500">
                Belum ada data. Klik "Tambah {{ props.meta.singular }}" untuk membuat yang pertama.
            </div>

            <table v-else class="w-full min-w-3xl text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-24 px-4 py-3">Urutan</th>
                        <th v-if="imageFields.length" class="w-20 px-4 py-3">Gambar</th>
                        <th v-for="column in props.meta.columns" :key="column" class="px-4 py-3">
                            {{ columnLabel(column) }}
                        </th>
                        <th class="w-28 px-4 py-3">Status</th>
                        <th class="w-32 px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-for="(item, index) in props.items" :key="item.id" class="hover:bg-slate-50/60">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button type="button" :disabled="index === 0"
                                    class="rounded-lg border border-slate-200 px-2 py-1 text-xs text-slate-500 transition hover:bg-white disabled:opacity-30"
                                    aria-label="Naikkan urutan" @click="move(item, 'up')">↑</button>
                                <button type="button" :disabled="index === props.items.length - 1"
                                    class="rounded-lg border border-slate-200 px-2 py-1 text-xs text-slate-500 transition hover:bg-white disabled:opacity-30"
                                    aria-label="Turunkan urutan" @click="move(item, 'down')">↓</button>
                            </div>
                        </td>

                        <td v-if="imageFields.length" class="px-4 py-3">
                            <img v-if="item[`${imageFields[0].name}_url`]" :src="item[`${imageFields[0].name}_url`]"
                                alt="" class="h-10 w-14 rounded-lg object-cover">
                            <span v-else class="text-xs text-slate-300">—</span>
                        </td>

                        <td v-for="column in props.meta.columns" :key="column" class="px-4 py-3">
                            <span class="line-clamp-2 text-slate-700">{{ item[column] || '—' }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <button type="button" class="rounded-full px-3 py-1 text-[11px] font-bold transition"
                                :class="item.is_active
                                    ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                @click="toggle(item)">
                                {{ item.is_active ? 'Tampil' : 'Disembunyikan' }}
                            </button>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button type="button" class="font-semibold text-teal-600 transition hover:text-teal-700"
                                @click="openEdit(item)">
                                Ubah
                            </button>
                            <button type="button" class="ml-3 font-semibold text-rose-500 transition hover:text-rose-600"
                                @click="destroy(item)">
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- =============================== FORM =============================== -->
        <div v-if="editing !== null" data-lenis-prevent
            class="admin-scroll fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-slate-900/50 p-4 sm:p-8">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="font-bold text-slate-900">
                        {{ editing === 'new' ? `Tambah ${props.meta.singular}` : `Ubah ${props.meta.singular}` }}
                    </h2>
                    <button type="button" class="text-slate-400 transition hover:text-slate-700" aria-label="Tutup"
                        @click="close">✕</button>
                </div>

                <form data-lenis-prevent
                    class="admin-scroll max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6" @submit.prevent="submit">
                    <FormField v-for="field in props.fields" :key="field.name" v-model="form[field.name]" :field="field"
                        :error="form.errors[field.name]" />

                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input v-model="form.is_active" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-teal-600">
                        Tampilkan di halaman publik
                    </label>
                </form>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                    <button type="button" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                        @click="close">
                        Batal
                    </button>
                    <button type="button" :disabled="form.processing"
                        class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60"
                        @click="submit">
                        {{ form.processing ? 'Menyimpan…' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
