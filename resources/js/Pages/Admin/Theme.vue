<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import AdminLayout from '../../Components/Admin/AdminLayout.vue';

/**
 * Pengaturan warna aksen tema landing page. Admin memilih warna induk
 * untuk empat aksen neon (aqua, volt, plasma, solar) secara terpisah
 * untuk mode gelap & terang; ramp shade lainnya diturunkan otomatis
 * oleh CSS di sisi server.
 */
const props = defineProps({
    palette: { type: Object, required: true },
    defaults: { type: Object, required: true },
    /** { aqua: 'Aksen Utama · Aqua', ... } */
    accents: { type: Object, required: true },
});

const modes = [
    { key: 'dark', label: 'Mode Gelap', icon: '🌙' },
    { key: 'light', label: 'Mode Terang', icon: '☀️' },
];

const activeMode = ref('dark');

/** Salinan dalam agar edit tidak mengubah prop asli sebelum disimpan. */
const clone = (value) => JSON.parse(JSON.stringify(value));

const form = useForm({ palette: clone(props.palette) });

const accentKeys = Object.keys(props.accents);

const submit = () => form.put('/admin/tema', { preserveScroll: true });

/** Kembalikan satu mode ke warna bawaan (aksen + latar). */
const resetMode = (mode) => {
    Object.keys(props.defaults[mode]).forEach((key) => {
        form.palette[mode][key] = props.defaults[mode][key];
    });
};

const resetAll = () => {
    resetMode('dark');
    resetMode('light');
};

const errorFor = (mode, accent) => form.errors[`palette.${mode}.${accent}`] ?? '';

/** Normalisasi input teks jadi "#rrggbb" bila valid. */
const onHexInput = (mode, accent, raw) => {
    let value = String(raw ?? '').trim();

    if (value && !value.startsWith('#')) {
        value = `#${value}`;
    }

    form.palette[mode][accent] = value;
};

/** Warna induk mode yang sedang ditampilkan di pratinjau. */
const preview = computed(() => form.palette[activeMode.value]);

const previewIsDark = computed(() => activeMode.value === 'dark');
</script>

<template>
    <AdminLayout title="Tema Website"
        subtitle="Ubah warna aksen situs untuk mode gelap dan terang sesuka Anda. Perubahan langsung tampil setelah disimpan.">
        <form @submit.prevent="submit">
            <!-- Pemilih mode -->
            <div class="flex flex-wrap items-center gap-2">
                <button v-for="mode in modes" :key="mode.key" type="button"
                    class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                    :class="activeMode === mode.key
                        ? 'bg-teal-600 text-white'
                        : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
                    @click="activeMode = mode.key">
                    <span aria-hidden="true" class="mr-1.5">{{ mode.icon }}</span>{{ mode.label }}
                </button>

                <button type="button"
                    class="ml-auto text-sm font-semibold text-slate-500 transition hover:text-rose-600"
                    @click="resetAll">
                    ↺ Kembalikan semua ke bawaan
                </button>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem]">
                <!-- ======================= PEMILIH WARNA ======================= -->
                <div v-for="mode in modes" v-show="activeMode === mode.key" :key="mode.key"
                    class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-slate-900">
                            Warna Aksen — {{ mode.label }}
                        </h2>
                        <button type="button"
                            class="text-xs font-semibold text-slate-500 transition hover:text-teal-600"
                            @click="resetMode(mode.key)">
                            Kembalikan mode ini
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        Pilih satu warna induk per aksen. Gradasi terang–gelapnya dibuat otomatis.
                    </p>

                    <div class="mt-6 space-y-5">
                        <div v-for="accent in accentKeys" :key="accent">
                            <label class="block text-sm font-semibold text-slate-700">
                                {{ props.accents[accent] }}
                            </label>
                            <div class="mt-2 flex items-center gap-3">
                                <input type="color" :value="form.palette[mode.key][accent]"
                                    class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-200 bg-white p-1"
                                    :aria-label="`Pilih ${props.accents[accent]}`"
                                    @input="form.palette[mode.key][accent] = $event.target.value">
                                <input type="text" :value="form.palette[mode.key][accent]"
                                    class="w-32 rounded-lg border border-slate-200 px-3 py-2 font-mono text-sm uppercase text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                                    placeholder="#34e2f5" maxlength="7"
                                    @input="onHexInput(mode.key, accent, $event.target.value)">
                                <span class="h-8 flex-1 rounded-lg border border-slate-200"
                                    :style="{ backgroundColor: form.palette[mode.key][accent] }"></span>
                            </div>
                            <p v-if="errorFor(mode.key, accent)" class="mt-1.5 text-xs font-semibold text-rose-600">
                                {{ errorFor(mode.key, accent) }}
                            </p>
                        </div>
                    </div>

                    <!-- Warna latar utama -->
                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <label class="block text-sm font-semibold text-slate-700">
                            Warna Latar Utama
                        </label>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ mode.key === 'dark'
                                ? 'Pilih warna gelap agar teks terang tetap terbaca.'
                                : 'Pilih warna terang agar teks gelap tetap terbaca.' }}
                            Permukaan kartu &amp; garis dibuat otomatis dari warna ini.
                        </p>
                        <div class="mt-2 flex items-center gap-3">
                            <input type="color" :value="form.palette[mode.key].background"
                                class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-200 bg-white p-1"
                                aria-label="Pilih warna latar utama"
                                @input="form.palette[mode.key].background = $event.target.value">
                            <input type="text" :value="form.palette[mode.key].background"
                                class="w-32 rounded-lg border border-slate-200 px-3 py-2 font-mono text-sm uppercase text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                                placeholder="#03050e" maxlength="7"
                                @input="onHexInput(mode.key, 'background', $event.target.value)">
                            <span class="h-8 flex-1 rounded-lg border border-slate-200"
                                :style="{ backgroundColor: form.palette[mode.key].background }"></span>
                        </div>
                        <p v-if="errorFor(mode.key, 'background')" class="mt-1.5 text-xs font-semibold text-rose-600">
                            {{ errorFor(mode.key, 'background') }}
                        </p>
                    </div>
                </div>

                <!-- ========================= PRATINJAU ========================= -->
                <div class="lg:sticky lg:top-24 lg:self-start">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                        Pratinjau langsung
                    </p>
                    <div class="overflow-hidden rounded-2xl border p-6 transition-colors"
                        :class="previewIsDark ? 'border-slate-800' : 'border-slate-200'"
                        :style="{ backgroundColor: preview.background }">
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em]"
                            :style="{ color: preview.aqua }">
                            Alazka Islamic School
                        </p>
                        <h3 class="mt-2 text-2xl font-extrabold leading-tight"
                            :style="{ color: previewIsDark ? '#f8fafc' : '#0a1124' }">
                            Generasi
                            <span :style="{ color: preview.plasma }">Qur'ani</span>
                            Masa Depan
                        </h3>

                        <button type="button"
                            class="mt-5 w-full rounded-full px-6 py-3 text-sm font-bold text-slate-900 shadow-lg"
                            :style="{ backgroundImage: `linear-gradient(to right, ${preview.aqua}, ${preview.volt})` }">
                            Daftar Sekarang
                        </button>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span v-for="accent in accentKeys" :key="accent"
                                class="rounded-full border px-3 py-1 text-xs font-bold"
                                :style="{
                                    color: preview[accent],
                                    borderColor: preview[accent],
                                    backgroundColor: `${preview[accent]}1f`,
                                }">
                                {{ accent }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-4 gap-2">
                            <span v-for="accent in accentKeys" :key="accent"
                                class="h-10 rounded-lg"
                                :style="{ backgroundColor: preview[accent] }"></span>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">
                        Pratinjau ini pendekatan. Hasil akhir dengan efek neon &amp; kaca
                        terlihat di halaman utama.
                    </p>
                </div>
            </div>

            <!-- Bilah simpan -->
            <div class="sticky bottom-4 mt-6 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                <button type="submit" :disabled="form.processing"
                    class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60">
                    {{ form.processing ? 'Menyimpan…' : 'Simpan Tema' }}
                </button>
                <span v-if="form.isDirty" class="text-xs font-semibold text-amber-600">
                    Ada perubahan yang belum disimpan.
                </span>
                <a href="/" target="_blank"
                    class="ml-auto text-sm font-semibold text-slate-500 transition hover:text-teal-600">
                    Pratinjau situs ↗
                </a>
            </div>
        </form>
    </AdminLayout>
</template>
