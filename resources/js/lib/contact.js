/**
 * Kontak resmi sekolah, dipakai sebagai jaring pengaman terakhir untuk tombol
 * WhatsApp/telepon.
 *
 * Nilai sebenarnya selalu datang dari panel admin (`site_settings`, lihat
 * `config/site_content.php`). Konstanta di sini hanya terpakai bila sebuah
 * halaman dirender tanpa prop `content` — mis. komponen dipakai lepas saat
 * pengembangan.
 *
 * Sengaja satu tempat: sebelumnya nomor yang sama ditulis ulang di beberapa
 * komponen, dan saat nomornya berubah ada yang tertinggal memakai nomor lama.
 */
export const WHATSAPP_URL = 'https://wa.me/6285606000606';

export const PHONE_URL = 'tel:+6285606000606';
