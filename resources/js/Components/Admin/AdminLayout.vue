<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: { type: String, default: 'Panel Admin' },
    subtitle: { type: String, default: '' },
});

const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const flash = computed(() => page.props.flash ?? {});
/** Menu koleksi konten dibangun server dari config/admin_resources.php. */
const resourceMenu = computed(() => page.props.adminMenu ?? []);

const sidebarOpen = ref(false);

const mainMenu = [
    { label: 'Dasbor', icon: '🏠', href: '/admin' },
    { label: 'Konten Halaman', icon: '✏️', href: '/admin/konten' },
    { label: 'Tema Website', icon: '🎨', href: '/admin/tema' },
    { label: 'Berita', icon: '📰', href: '/admin/berita' },
    { label: 'Next Event', icon: '📅', href: '/admin/event' },
    { label: 'Pustaka Media', icon: '🖼️', href: '/admin/media' },
];

/** Menu aktif bila URL saat ini diawali href-nya (agar sub-halaman ikut tersorot). */
const isActive = (href) => {
    const current = page.url.split('?')[0];

    return href === '/admin' ? current === '/admin' : current.startsWith(href);
};

/** Notifikasi hijau/merah yang muncul sesaat setelah aksi simpan/hapus. */
const notice = ref(null);
let noticeTimer = null;

watch(flash, (value) => {
    if (!value?.success && !value?.error) {
        return;
    }

    notice.value = value.success
        ? { type: 'success', text: value.success }
        : { type: 'error', text: value.error };

    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => (notice.value = null), 4000);
}, { immediate: true, deep: true });

const logout = () => router.post('/logout');
</script>

<template>
    <Head :title="props.title" />

    <div class="min-h-screen bg-slate-100 text-slate-800">
        <!-- ============================ SIDEBAR ============================ -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full overflow-y-auto bg-slate-900 text-slate-300 transition-transform duration-300 lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }">
            <div class="flex items-center gap-3 px-6 py-6">
                <span
                    class="flex h-11 w-11 rotate-[22.5deg] items-center justify-center rounded-2xl bg-linear-to-br from-teal-400 via-sky-400 to-fuchsia-400 font-bold text-slate-900">
                    <span class="-rotate-[22.5deg]">A</span>
                </span>
                <span class="leading-tight">
                    <span class="block text-sm font-bold text-white">Panel Admin</span>
                    <span class="block text-[11px] uppercase tracking-[0.16em] text-teal-300">Alazka Profile</span>
                </span>
            </div>

            <nav class="px-4 pb-10">
                <p class="px-3 pb-2 pt-4 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Utama</p>
                <Link v-for="item in mainMenu" :key="item.href" :href="item.href"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    :class="isActive(item.href)
                        ? 'bg-teal-400/15 text-teal-200 ring-1 ring-teal-400/30'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    @click="sidebarOpen = false">
                    <span aria-hidden="true">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>

                <p class="px-3 pb-2 pt-6 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">
                    Komponen Halaman
                </p>
                <Link v-for="item in resourceMenu" :key="item.key" :href="item.href"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    :class="isActive(item.href)
                        ? 'bg-teal-400/15 text-teal-200 ring-1 ring-teal-400/30'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    @click="sidebarOpen = false">
                    <span aria-hidden="true">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>

                <p class="px-3 pb-2 pt-6 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Akun</p>
                <Link href="/admin/profil"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    :class="isActive('/admin/profil')
                        ? 'bg-teal-400/15 text-teal-200 ring-1 ring-teal-400/30'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    @click="sidebarOpen = false">
                    <span aria-hidden="true">👤</span>
                    Profil Saya
                </Link>
                <a href="/" target="_blank"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <span aria-hidden="true">🌐</span>
                    Lihat Situs
                </a>
                <button type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-300 transition hover:bg-rose-500/10"
                    @click="logout">
                    <span aria-hidden="true">🚪</span>
                    Keluar
                </button>
            </nav>
        </aside>

        <!-- Latar gelap saat sidebar dibuka di layar kecil -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- ============================= KONTEN ============================= -->
        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex items-center gap-4 px-5 py-4 sm:px-8">
                    <button type="button"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-slate-600 lg:hidden"
                        aria-label="Buka menu" @click="sidebarOpen = true">
                        ☰
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold text-slate-900 sm:text-xl">{{ props.title }}</h1>
                        <p v-if="props.subtitle" class="truncate text-xs text-slate-500 sm:text-sm">
                            {{ props.subtitle }}
                        </p>
                    </div>

                    <div class="ml-auto hidden items-center gap-3 sm:flex">
                        <span class="text-right leading-tight">
                            <span class="block text-sm font-semibold text-slate-800">{{ user?.name }}</span>
                            <span class="block text-[11px] text-slate-500">{{ user?.email }}</span>
                        </span>
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 text-sm font-bold text-teal-700">
                            {{ user?.name?.charAt(0) ?? 'A' }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Notifikasi melayang -->
            <Transition enter-active-class="transition duration-200" enter-from-class="-translate-y-2 opacity-0"
                leave-active-class="transition duration-200" leave-to-class="-translate-y-2 opacity-0">
                <div v-if="notice" class="fixed right-5 top-20 z-50 max-w-sm rounded-xl px-5 py-3 text-sm font-semibold shadow-lg"
                    :class="notice.type === 'success'
                        ? 'bg-emerald-600 text-white'
                        : 'bg-rose-600 text-white'">
                    {{ notice.text }}
                </div>
            </Transition>

            <main class="px-5 py-7 sm:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
