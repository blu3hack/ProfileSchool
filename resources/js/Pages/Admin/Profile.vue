<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

import AdminLayout from '../../Components/Admin/AdminLayout.vue';

/** Ubah identitas akun admin dan ganti kata sandi. */
const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});

const profileForm = useForm({
    name: user.value.name ?? '',
    email: user.value.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const inputClass =
    'w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100';

const saveProfile = () => profileForm.put('/admin/profil', { preserveScroll: true });

const savePassword = () => passwordForm.put('/admin/profil/kata-sandi', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
});
</script>

<template>
    <AdminLayout title="👤 Profil Saya" subtitle="Perbarui identitas akun dan kata sandi panel admin.">
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Identitas -->
            <form class="rounded-2xl border border-slate-200 bg-white p-6" @submit.prevent="saveProfile">
                <h2 class="font-bold text-slate-900">Identitas Akun</h2>

                <div class="mt-5 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nama</label>
                        <input v-model="profileForm.name" type="text" :class="[inputClass, 'mt-2']">
                        <p v-if="profileForm.errors.name" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ profileForm.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Email</label>
                        <input v-model="profileForm.email" type="email" :class="[inputClass, 'mt-2']">
                        <p v-if="profileForm.errors.email" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ profileForm.errors.email }}
                        </p>
                    </div>
                </div>

                <button type="submit" :disabled="profileForm.processing"
                    class="mt-6 rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 disabled:opacity-60">
                    Simpan Profil
                </button>
            </form>

            <!-- Kata sandi -->
            <form class="rounded-2xl border border-slate-200 bg-white p-6" @submit.prevent="savePassword">
                <h2 class="font-bold text-slate-900">Ganti Kata Sandi</h2>
                <p class="mt-1 text-sm text-slate-500">Minimal 8 karakter. Gunakan kombinasi yang sulit ditebak.</p>

                <div class="mt-5 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Kata Sandi Saat Ini</label>
                        <input v-model="passwordForm.current_password" type="password" autocomplete="current-password"
                            :class="[inputClass, 'mt-2']">
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Kata Sandi Baru</label>
                        <input v-model="passwordForm.password" type="password" autocomplete="new-password"
                            :class="[inputClass, 'mt-2']">
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ passwordForm.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Ulangi Kata Sandi Baru</label>
                        <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password"
                            :class="[inputClass, 'mt-2']">
                    </div>
                </div>

                <button type="submit" :disabled="passwordForm.processing"
                    class="mt-6 rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 disabled:opacity-60">
                    Ganti Kata Sandi
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
