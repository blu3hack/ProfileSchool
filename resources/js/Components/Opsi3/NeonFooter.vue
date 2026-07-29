<script setup>
import { computed } from 'vue';

import { PHONE_URL, WHATSAPP_URL } from '../../lib/contact';
import { goToSection } from '../../lib/navigate';
import { useTheme } from '../../lib/theme';

const { theme } = useTheme();

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    links: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
    /** Teks & tautan footer; diedit admin lewat /admin/konten. */
    content: { type: Object, default: () => ({}) },
});

const currentYear = new Date().getFullYear();

const text = (key, fallback = '') => props.content?.[key] || fallback;

/** Peta cadangan bila field admin kosong / tidak bisa disematkan. */
const FALLBACK_MAP = 'https://www.openstreetmap.org/export/embed.html?bbox=107.5300%2C-6.8800%2C107.5600%2C-6.8600&layer=mapnik';

/**
 * URL peta yang benar-benar aman disematkan.
 *
 * Google memblokir tautan biasa (mis. `google.com/maps/place/...` atau link
 * "Bagikan") dengan `X-Frame-Options`, sehingga iframe menolak menampilkannya.
 * Helper ini merapikan apa pun yang ditempel admin:
 * - Bila menempel seluruh kode `<iframe …>`, ambil isi `src`-nya.
 * - Hanya izinkan URL embed yang memang boleh di-frame; selain itu pakai
 *   peta cadangan agar footer tidak pernah menampilkan error.
 */
const mapSrc = computed(() => {
    const raw = (props.content?.map_embed || '').trim();

    if (!raw) {
        return FALLBACK_MAP;
    }

    // Admin menempel tag <iframe …src="…">? Ambil src-nya saja.
    const fromIframe = raw.match(/<iframe[^>]*\ssrc=["']([^"']+)["']/i);
    const url = (fromIframe ? fromIframe[1] : raw).replace(/&amp;/g, '&');

    // Daftar pola URL yang sah untuk disematkan.
    const embeddable =
        /^https:\/\/www\.google\.com\/maps\/embed\?/i.test(url) ||       // Google "Sematkan peta"
        /^https:\/\/maps\.google\.com\/maps\?[^]*\boutput=embed\b/i.test(url) || // Google output=embed
        /openstreetmap\.org\/export\/embed/i.test(url);                  // OpenStreetMap embed

    return embeddable ? url : FALLBACK_MAP;
});

/** Logo unggahan admin (URL siap pakai). Kosong → jatuh ke inisial otomatis. */
const logo = computed(() => props.content?.nav_logo || '');
const initial = computed(() => (props.schoolName || 'A').trim().charAt(0).toUpperCase());

/** Tiga tombol kontak cepat — tujuannya diambil dari konten yang bisa diedit. */
const quickActions = computed(() => [
    { icon: '💬', label: 'WhatsApp Admin', href: text('quick_whatsapp', WHATSAPP_URL), tone: 'from-aqua-400 to-aqua-600' },
    { icon: '📞', label: 'Telepon Sekolah', href: text('quick_phone', PHONE_URL), tone: 'from-volt-400 to-volt-500' },
    { icon: '✉️', label: 'Kirim Email', href: text('quick_email', 'mailto:info@alazka.sch.id'), tone: 'from-plasma-400 to-plasma-500' },
]);

const goTo = (hash) => goToSection(hash);
</script>

<template>
    <footer id="kontak" class="relative z-10 scroll-mt-28 overflow-hidden bg-void-950 text-slate-300">
        <!-- Garis horizon neon di batas atas footer -->
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-aqua-400 to-transparent shadow-[0_0_28px_rgba(52,226,245,0.9)]">
        </div>

        <!-- Lantai kisi berperspektif — "horizon" khas cyberpunk -->
        <div class="pointer-events-none absolute inset-x-0 top-0 h-64 overflow-hidden opacity-40">
            <div class="cyber-floor absolute inset-x-[-25%] top-0 h-64"></div>
        </div>

        <!-- Gradient, bukan `blur-3xl` — lihat catatan `.orb-glow` di app.css. -->
        <div class="orb-glow pointer-events-none absolute -left-24 top-24 h-72 w-72"
            style="--orb-color: rgba(15, 195, 221, 0.18)"></div>
        <div class="orb-glow pointer-events-none absolute -right-20 bottom-0 h-80 w-80"
            style="--orb-color: rgba(233, 48, 177, 0.15)"></div>

        <div class="container-page relative pb-12 pt-28 sm:pt-36">
            <!-- Tombol kontak cepat -->
            <div class="holo-panel grid gap-3 rounded-[2rem] p-4 sm:grid-cols-3">
                <a v-for="action in quickActions" :key="action.label" :href="action.href"
                    class="group flex items-center gap-3 rounded-3xl border border-void-700/70 bg-void-800/50 px-5 py-4 transition duration-300 hover:-translate-y-1 hover:border-aqua-400/40 hover:bg-aqua-400/10">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-linear-to-br text-lg shadow-lg transition duration-300 group-hover:scale-110"
                        :class="action.tone">
                        {{ action.icon }}
                    </span>
                    <span class="text-sm font-semibold text-slate-100">{{ action.label }}</span>
                    <span class="ml-auto text-slate-500 transition group-hover:translate-x-1 group-hover:text-aqua-300"
                        aria-hidden="true">
                        &rarr;
                    </span>
                </a>
            </div>

            <div class="mt-14 grid gap-12 lg:grid-cols-[1.15fr_1fr_1fr]">
                <!-- Identitas -->
                <div>
                    <div class="flex items-center gap-3">
                        <span v-if="logo"
                            class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-void-800/50 ring-1 ring-aqua-400/30 shadow-[0_0_26px_rgba(52,226,245,0.4)]">
                            <img :src="logo" :alt="`Logo ${props.schoolName}`" class="h-full w-full object-contain">
                        </span>
                        <span v-else
                            class="flex h-12 w-12 rotate-[22.5deg] items-center justify-center rounded-2xl bg-linear-to-br from-aqua-400 via-volt-400 to-plasma-400 font-display text-lg font-bold text-void-950 shadow-[0_0_26px_rgba(52,226,245,0.55)]">
                            <span class="-rotate-[22.5deg]">{{ initial }}</span>
                        </span>
                        <span class="leading-tight">
                            <span class="block font-display text-lg font-bold text-slate-50">{{ props.schoolName }}</span>
                            <span class="block text-xs uppercase tracking-[0.16em] text-aqua-400/80">
                                {{ text('school_subtitle', 'SD & SMP Islam Terpadu') }}
                            </span>
                        </span>
                    </div>

                    <p class="mt-6 max-w-sm text-sm leading-relaxed text-slate-400">
                        {{ text('footer_description', "Mendidik generasi Qur'ani yang cerdas, berakhlak mulia, dan siap berkontribusi bagi umat serta bangsa.") }}
                    </p>

                    <ul class="mt-7 flex flex-wrap gap-2">
                        <li v-for="social in props.socials" :key="social.label">
                            <a :href="social.href"
                                class="inline-block rounded-full border border-void-600 px-4 py-2 text-xs font-semibold text-slate-300 transition duration-300 hover:-translate-y-0.5 hover:border-aqua-400/60 hover:text-aqua-200 hover:shadow-[0_0_20px_-4px_rgba(52,226,245,0.7)]">
                                {{ social.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Kontak -->
                <div>
                    <h3 class="font-display text-sm font-bold uppercase tracking-[0.18em] text-aqua-300">
                        Hubungi Kami
                    </h3>

                    <ul class="mt-6 space-y-5">
                        <li v-for="contact in props.contacts" :key="contact.label" class="flex gap-3">
                            <span class="mt-0.5 text-lg" aria-hidden="true">{{ contact.icon }}</span>
                            <span>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    {{ contact.label }}
                                </span>
                                <a v-if="contact.href" :href="contact.href"
                                    class="block text-sm leading-relaxed text-slate-200 transition hover:text-aqua-300">
                                    {{ contact.value }}
                                </a>
                                <span v-else class="block text-sm leading-relaxed text-slate-200">
                                    {{ contact.value }}
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Lokasi + navigasi -->
                <div>
                    <h3 class="font-display text-sm font-bold uppercase tracking-[0.18em] text-aqua-300">
                        Lokasi Sekolah
                    </h3>

                    <div class="mt-6 overflow-hidden rounded-3xl border border-aqua-400/20 shadow-[0_0_30px_-14px_rgba(52,226,245,0.8)]">
                        <iframe :src="mapSrc"
                            title="Peta lokasi sekolah" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            class="h-48 w-full border-0 transition duration-500"
                            :class="theme === 'dark' ? 'invert grayscale-[60%] hue-rotate-180' : 'grayscale-[25%]'"></iframe>
                    </div>

                    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2">
                        <li v-for="link in props.links" :key="link.hash">
                            <a :href="link.hash" class="text-sm text-slate-400 transition hover:text-aqua-300"
                                @click.prevent="goTo(link.hash)">
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-14 flex flex-col items-center justify-between gap-3 border-t border-void-700 pt-8 text-xs text-slate-500 sm:flex-row">
                <p>&copy; {{ currentYear }} {{ props.schoolName }}. Seluruh hak cipta dilindungi.</p>
                <p>{{ text('footer_note', 'Dibangun dengan penuh amanah untuk pendidikan Indonesia.') }}</p>
            </div>
        </div>
    </footer>
</template>
