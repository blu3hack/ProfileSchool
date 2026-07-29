<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import AdminLayout from '../../Components/Admin/AdminLayout.vue';
import FormField from '../../Components/Admin/FormField.vue';

/**
 * Editor seluruh teks & gambar tunggal landing page, dikelompokkan
 * per section (hero, keunggulan, berita, kegiatan, prestasi, PPDB, footer).
 */
const props = defineProps({
    groups: { type: Array, default: () => [] },
});

const activeGroup = ref(props.groups[0]?.key ?? '');

/** Semua field digabung jadi satu objek `values` agar disimpan sekali jalan. */
const initialValues = {};

props.groups.forEach((group) => {
    group.fields.forEach((field) => {
        initialValues[field.key] = field.value ?? '';
    });
});

const form = useForm({ values: initialValues });

const submit = () => form.put('/admin/konten', { preserveScroll: true });

const errorFor = (key) => form.errors[`values.${key}`] ?? '';
</script>

<template>
    <AdminLayout title="Konten Halaman"
        subtitle="Ubah judul, paragraf, label tombol, dan foto pada setiap bagian halaman utama.">
        <form @submit.prevent="submit">
            <div class="grid gap-6 lg:grid-cols-[16rem_1fr]">
                <!-- Daftar section -->
                <nav class="lg:sticky lg:top-24 lg:self-start">
                    <ul data-lenis-prevent
                        class="admin-scroll flex gap-2 overflow-x-auto pb-2 lg:flex-col lg:overflow-visible lg:pb-0">
                        <li v-for="group in props.groups" :key="group.key" class="shrink-0 lg:shrink">
                            <button type="button"
                                class="w-full rounded-xl px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="activeGroup === group.key
                                    ? 'bg-teal-600 text-white'
                                    : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
                                @click="activeGroup = group.key">
                                {{ group.label }}
                            </button>
                        </li>
                    </ul>
                </nav>

                <!-- Field pada section terpilih -->
                <div>
                    <div v-for="group in props.groups" v-show="activeGroup === group.key" :key="group.key"
                        class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 class="text-lg font-bold text-slate-900">{{ group.label }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Perubahan langsung tampil di halaman publik setelah disimpan.
                        </p>

                        <div class="mt-6 space-y-6">
                            <FormField v-for="field in group.fields" :key="field.key"
                                v-model="form.values[field.key]"
                                :field="{
                                    label: field.label,
                                    type: field.type,
                                    hint: field.hint,
                                    preview: field.preview,
                                }"
                                :error="errorFor(field.key)" />
                        </div>
                    </div>

                    <div class="sticky bottom-4 mt-6 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan…' : 'Simpan Perubahan' }}
                        </button>
                        <span v-if="form.isDirty" class="text-xs font-semibold text-amber-600">
                            Ada perubahan yang belum disimpan.
                        </span>
                        <a href="/" target="_blank"
                            class="ml-auto text-sm font-semibold text-slate-500 transition hover:text-teal-600">
                            Pratinjau situs ↗
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
