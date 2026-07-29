import { ref, watch } from 'vue';

/**
 * State tema (dark/light) untuk halaman publik "Opsi 3".
 *
 * Sengaja modul-level: navbar (tombol toggle) dan halaman (atribut
 * `data-theme` pada <html>) memakai ref yang sama tanpa perlu provide/inject.
 *
 * Nilai `data-theme` inilah yang dibaca CSS untuk menimpa variabel warna —
 * jadi seluruh utilitas Tailwind ikut berubah tanpa prefiks `dark:`.
 *
 * Urutan penentuannya sudah dikerjakan skrip pra-render di <head> (lihat
 * resources/views/app.blade.php): bawaan dari panel admin ➜ ditimpa pilihan
 * pengunjung di localStorage bila belum kedaluwarsa ➜ 'system' dipetakan ke
 * preferensi perangkat. Modul ini tinggal membaca hasilnya, jadi tidak ada
 * kemungkinan JS dan HTML berselisih (sumber kedipan & "balik ke gelap").
 */

/** Konfigurasi yang ditanam skrip <head>; null di panel admin. */
const boot = typeof window === 'undefined' ? null : (window.__alazkaTheme ?? null);

/** Harus sama dengan App\Support\ThemeMode::STORAGE_KEY. */
const STORAGE_KEY = boot?.storageKey ?? 'alazka:opsi3-theme';

/** Stamp bawaan situs; pilihan pengunjung harus >= ini agar tetap dihormati. */
const SERVER_STAMP = Number(boot?.stamp ?? 0);

const isTheme = (value) => value === 'dark' || value === 'light';

const systemTheme = () => (typeof window !== 'undefined'
    && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');

/**
 * Nilai awal = apa yang sudah terpasang di <html>. Kalau modul ini termuat di
 * halaman tanpa tema (panel admin), jatuh ke bawaan situs.
 */
const readInitial = () => {
    if (typeof document === 'undefined') {
        return 'dark';
    }

    const applied = document.documentElement.dataset.theme;

    if (isTheme(applied)) {
        return applied;
    }

    const fallback = boot?.default ?? 'dark';

    return fallback === 'system' ? systemTheme() : fallback;
};

export const theme = ref(readInitial());

/**
 * Pasang/lepas atribut di <html>.
 *
 * Dilepas saat masuk rute /admin: aturan `[data-theme='light']` membalik skala
 * `--color-slate-*`, dan panel admin memakai skala itu untuk teksnya. Kalau
 * atributnya ikut terbawa lewat navigasi Inertia dari halaman publik, tulisan
 * panel jadi tak terbaca.
 */
export const syncThemeRoot = (enabled) => {
    if (typeof document === 'undefined') {
        return;
    }

    if (enabled) {
        document.documentElement.dataset.theme = theme.value;
    } else {
        delete document.documentElement.dataset.theme;
    }
};

watch(theme, (value) => {
    if (typeof document !== 'undefined' && document.documentElement.dataset.theme) {
        document.documentElement.dataset.theme = value;
    }

    try {
        // Stamp pilihan pengunjung: minimal setinggi stamp server, agar jam
        // browser yang meleset ke belakang tidak membuat pilihannya langsung
        // dianggap kedaluwarsa.
        const stamp = Math.max(SERVER_STAMP, Math.floor(Date.now() / 1000));

        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ mode: value, stamp }));
    } catch {
        // Gagal menyimpan bukan alasan untuk membatalkan pergantian tema.
    }
});

export const setTheme = (value) => {
    theme.value = isTheme(value) ? value : 'dark';
};

export const toggleTheme = () => setTheme(theme.value === 'dark' ? 'light' : 'dark');

export function useTheme() {
    return { theme, setTheme, toggleTheme };
}
