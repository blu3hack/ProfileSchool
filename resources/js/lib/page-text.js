/**
 * Pembaca teks halaman yang dipakai bersama seluruh section beranda.
 *
 * Sumbernya satu: prop `content` yang diturunkan halaman dari PageContent
 * (diedit admin lewat /admin/konten). Dulu tiap section menyalin sendiri
 * pasangan helper ini; sekarang cukup:
 *
 *     const { text, raw } = usePageText(() => props.content);
 *
 * Argumennya sengaja berupa fungsi (bukan objeknya langsung) supaya nilai
 * yang dibaca tetap ikut berubah saat Inertia mengganti prop halaman.
 *
 * @param {(() => object)|object} content sumber teks halaman
 */
export function usePageText(content) {
    const bag = () => (typeof content === 'function' ? content() : content) ?? {};

    return {
        /**
         * Baca teks dengan cadangan: `text('hero_badge', 'cadangan')`.
         * Nilai kosong (field dikosongkan admin) tetap jatuh ke teks cadangan
         * supaya layout tidak pernah tampil bolong.
         */
        text: (key, fallback = '') => bag()[key] || fallback,

        /**
         * Baca nilai apa adanya — menghormati string kosong (tidak jatuh ke
         * teks cadangan). Dipakai untuk elemen yang boleh dihapus admin,
         * mis. baris ketiga judul hero.
         */
        raw: (key) => (bag()[key] ?? '').trim(),
    };
}
