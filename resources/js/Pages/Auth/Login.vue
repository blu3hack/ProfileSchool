<script setup>
import { Head, useForm } from '@inertiajs/vue3';

/** Form masuk panel admin. */
const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    status: { type: String, default: '' },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => form.post('/login', {
    onFinish: () => form.reset('password'),
});
</script>

<template>
    <Head title="Masuk Panel Admin" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-5 py-12">
        <!-- Ornamen latar bernuansa sama dengan landing page -->
        <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-teal-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-20 h-96 w-96 rounded-full bg-fuchsia-500/15 blur-3xl">
        </div>

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <span
                    class="mx-auto flex h-14 w-14 rotate-[22.5deg] items-center justify-center rounded-2xl bg-linear-to-br from-teal-400 via-sky-400 to-fuchsia-400 text-xl font-bold text-slate-900">
                    <span class="-rotate-[22.5deg]">A</span>
                </span>
                <h1 class="mt-5 text-2xl font-bold text-white">Panel Administrator</h1>
                <p class="mt-1 text-sm text-slate-400">{{ props.schoolName }}</p>
            </div>

            <form class="rounded-3xl border border-slate-800 bg-slate-900/80 p-7 backdrop-blur" @submit.prevent="submit">
                <p v-if="props.status"
                    class="mb-5 rounded-xl bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-300">
                    {{ props.status }}
                </p>

                <label class="block text-sm font-semibold text-slate-300" for="email">Email</label>
                <input id="email" v-model="form.email" type="email" autocomplete="username" required autofocus
                    placeholder="admin@alazka.sch.id"
                    class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 outline-none transition placeholder:text-slate-600 focus:border-teal-400 focus:ring-2 focus:ring-teal-400/20">
                <p v-if="form.errors.email" class="mt-1.5 text-xs font-semibold text-rose-400">{{ form.errors.email }}</p>

                <label class="mt-5 block text-sm font-semibold text-slate-300" for="password">Kata Sandi</label>
                <input id="password" v-model="form.password" type="password" autocomplete="current-password" required
                    placeholder="••••••••"
                    class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 outline-none transition placeholder:text-slate-600 focus:border-teal-400 focus:ring-2 focus:ring-teal-400/20">
                <p v-if="form.errors.password" class="mt-1.5 text-xs font-semibold text-rose-400">
                    {{ form.errors.password }}
                </p>

                <label class="mt-5 flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="form.remember" type="checkbox"
                        class="h-4 w-4 rounded border-slate-600 bg-slate-950 text-teal-500">
                    Ingat saya di perangkat ini
                </label>

                <button type="submit" :disabled="form.processing"
                    class="mt-7 w-full rounded-xl bg-linear-to-r from-teal-400 to-sky-400 px-5 py-3 text-sm font-bold text-slate-900 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                    {{ form.processing ? 'Memproses…' : 'Masuk' }}
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-500">
                <a href="/" class="transition hover:text-teal-300">← Kembali ke situs sekolah</a>
            </p>
        </div>
    </div>
</template>
