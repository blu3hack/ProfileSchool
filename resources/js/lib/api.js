/**
 * Pembungkus `fetch` untuk permintaan XHR di panel admin (unggah gambar &
 * memuat pustaka media). Halaman biasa tetap memakai Inertia.
 *
 * Token CSRF diambil dari cookie XSRF-TOKEN yang dipasang Laravel, lalu
 * dikirim lewat header X-XSRF-TOKEN — persis pola yang dipakai Axios.
 */
const csrfToken = () => {
    const match = document.cookie.match(/(^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[2]) : '';
};

const baseHeaders = () => ({
    'X-XSRF-TOKEN': csrfToken(),
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
});

/** Melempar Error berisi pesan validasi Laravel bila permintaan gagal. */
const toResult = async (response) => {
    const payload = await response.json().catch(() => ({}));

    if (response.ok) {
        return payload;
    }

    const message = payload?.errors
        ? Object.values(payload.errors).flat().join(' ')
        : (payload?.message ?? 'Terjadi kesalahan pada server.');

    throw new Error(message);
};

export const postForm = async (url, formData) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: baseHeaders(),
        body: formData,
        credentials: 'same-origin',
    });

    return toResult(response);
};

export const getJson = async (url) => {
    const response = await fetch(url, {
        headers: baseHeaders(),
        credentials: 'same-origin',
    });

    return toResult(response);
};
