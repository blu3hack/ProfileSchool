/**
 * Penggolong target tautan.
 *
 * Menu tambahan diisi admin dengan alamat bebas: bisa menunjuk section
 * beranda, halaman lain situs ini, atau alamat di luar. Ketiganya butuh
 * perlakuan berbeda — digulir mulus lewat Lenis, dikunjungi lewat Inertia
 * (tanpa muat ulang), atau dibuka di tab baru. Satu tempat ini yang
 * memutuskannya supaya navbar dan section kartu tidak pernah berbeda pendapat.
 */

/** Alamat lengkap ke situs lain: https://…, http://…, //cdn… */
const EXTERNAL = /^(https?:\/\/|\/\/)/i;

/** Skema non-http yang ditangani sistem operasi: mailto:, tel:, whatsapp:, … */
const SCHEME = /^[a-z][a-z0-9+.-]*:/i;

/**
 * @returns {'section'|'internal'|'external'|'scheme'} Jenis tautan.
 *  - `section`  → #kontak, digulir (atau kembali ke beranda dulu bila perlu)
 *  - `internal` → /berita, dikunjungi lewat Inertia
 *  - `external` → https://…, dibuka di tab baru
 *  - `scheme`   → mailto:/tel:, diserahkan ke perangkat, tetap di tab yang sama
 */
export const linkKind = (href = '') => {
    const target = href.trim();

    if (target.startsWith('#')) {
        return 'section';
    }

    if (EXTERNAL.test(target)) {
        return 'external';
    }

    if (SCHEME.test(target)) {
        return 'scheme';
    }

    return 'internal';
};
