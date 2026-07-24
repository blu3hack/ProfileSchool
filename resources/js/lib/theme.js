import { ref, watch } from 'vue';

/**
 * State tema (dark/light) untuk landing "Opsi 3".
 *
 * Sengaja modul-level: navbar (tombol toggle) dan halaman (atribut
 * `data-theme` pada root) memakai ref yang sama tanpa perlu provide/inject.
 *
 * Nilai `data-theme` inilah yang dibaca CSS untuk menimpa variabel warna —
 * jadi seluruh utilitas Tailwind ikut berubah tanpa prefiks `dark:`.
 */
const STORAGE_KEY = 'alazka:opsi3-theme';

const isTheme = (value) => value === 'dark' || value === 'light';

/** Pilihan tersimpan menang; kalau belum ada, ikuti preferensi sistem. */
const readInitial = () => {
    if (typeof window === 'undefined') {
        return 'dark';
    }

    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);

        if (isTheme(stored)) {
            return stored;
        }
    } catch {
        // localStorage bisa diblokir (mode privat) — abaikan, pakai preferensi sistem.
    }

    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
};

export const theme = ref(readInitial());

watch(theme, (value) => {
    try {
        window.localStorage.setItem(STORAGE_KEY, value);
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
