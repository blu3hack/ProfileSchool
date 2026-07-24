<script setup>
import { getSmoothScroll } from '../../lib/smooth-scroll';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    links: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    socials: { type: Array, default: () => [] },
});

const currentYear = new Date().getFullYear();

const quickActions = [
    { icon: '💬', label: 'WhatsApp Admin', href: 'https://wa.me/622287654321', tone: 'from-mint-400 to-mint-600' },
    { icon: '📞', label: 'Telepon Sekolah', href: 'tel:+622287654321', tone: 'from-sky-soft-400 to-sky-soft-500' },
    { icon: '✉️', label: 'Kirim Email', href: 'mailto:info@alazka.sch.id', tone: 'from-lilac-400 to-lilac-500' },
];

const goTo = (hash) => {
    const target = document.querySelector(hash);

    if (!target) {
        return;
    }

    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset: -96, duration: 1.4 });
    } else {
        target.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <footer id="kontak" class="relative scroll-mt-28 overflow-hidden bg-sage-900 text-cream-100">
        <!-- Gelombang pemisah di sisi atas footer -->
        <div class="pointer-events-none absolute inset-x-0 -top-px text-canvas-50">
            <svg class="block h-16 w-full sm:h-24" viewBox="0 0 1440 120" preserveAspectRatio="none"
                aria-hidden="true">
                <path fill="currentColor"
                    d="M0,64 C240,120 480,0 720,32 C960,64 1200,120 1440,72 L1440,0 L0,0 Z" />
            </svg>
        </div>

        <!-- Pendar pastel lembut di latar footer -->
        <div class="pointer-events-none absolute -left-24 top-24 h-72 w-72 rounded-full bg-mint-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-lilac-500/15 blur-3xl">
        </div>
        <div class="pattern-lattice pointer-events-none absolute inset-0 opacity-[0.07]"></div>

        <div class="container-page relative pb-12 pt-28 sm:pt-36">
            <!-- Tombol kontak cepat -->
            <div class="glass-panel-dark grid gap-3 rounded-[2rem] p-4 sm:grid-cols-3">
                <a v-for="action in quickActions" :key="action.label" :href="action.href"
                    class="group flex items-center gap-3 rounded-3xl bg-white/5 px-5 py-4 transition duration-300 hover:-translate-y-1 hover:bg-white/10">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-linear-to-br text-lg shadow-lg transition duration-300 group-hover:scale-110"
                        :class="action.tone">
                        {{ action.icon }}
                    </span>
                    <span class="text-sm font-semibold text-cream-50">{{ action.label }}</span>
                    <span class="ml-auto text-cream-300/60 transition group-hover:translate-x-1" aria-hidden="true">
                        &rarr;
                    </span>
                </a>
            </div>

            <div class="mt-14 grid gap-12 lg:grid-cols-[1.15fr_1fr_1fr]">
                <!-- Identitas -->
                <div>
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-12 w-12 rotate-[22.5deg] items-center justify-center rounded-2xl bg-linear-to-br from-mint-400 to-mint-600 font-display text-lg font-bold text-white shadow-lg shadow-mint-500/30">
                            <span class="-rotate-[22.5deg]">A</span>
                        </span>
                        <span class="leading-tight">
                            <span class="block font-display text-lg font-bold text-cream-50">{{ props.schoolName }}</span>
                            <span class="block text-xs text-mint-300/80">SD &amp; SMP Islam Terpadu</span>
                        </span>
                    </div>

                    <p class="mt-6 max-w-sm text-sm leading-relaxed text-cream-200/70">
                        Mendidik generasi Qur'ani yang cerdas, berakhlak mulia, dan siap berkontribusi bagi umat serta
                        bangsa — dengan pendekatan belajar yang hangat dan relevan bagi anak masa kini.
                    </p>

                    <ul class="mt-7 flex flex-wrap gap-2">
                        <li v-for="social in props.socials" :key="social.label">
                            <a :href="social.href"
                                class="inline-block rounded-full border border-cream-200/20 px-4 py-2 text-xs font-semibold text-cream-100 transition duration-300 hover:-translate-y-0.5 hover:border-mint-400/60 hover:bg-white/5 hover:text-mint-300">
                                {{ social.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Kontak -->
                <div>
                    <h3 class="font-display text-sm font-bold uppercase tracking-[0.18em] text-mint-300">
                        Hubungi Kami
                    </h3>

                    <ul class="mt-6 space-y-5">
                        <li v-for="contact in props.contacts" :key="contact.label" class="flex gap-3">
                            <span class="mt-0.5 text-lg" aria-hidden="true">{{ contact.icon }}</span>
                            <span>
                                <span class="block text-xs font-semibold text-cream-300/60">{{ contact.label }}</span>
                                <a v-if="contact.href" :href="contact.href"
                                    class="block text-sm leading-relaxed text-cream-100/90 transition hover:text-mint-300">
                                    {{ contact.value }}
                                </a>
                                <span v-else class="block text-sm leading-relaxed text-cream-100/90">
                                    {{ contact.value }}
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Lokasi + navigasi -->
                <div>
                    <h3 class="font-display text-sm font-bold uppercase tracking-[0.18em] text-mint-300">
                        Lokasi Sekolah
                    </h3>

                    <div class="mt-6 overflow-hidden rounded-3xl border border-cream-200/15">
                        <iframe
                            src="https://www.openstreetmap.org/export/embed.html?bbox=107.5300%2C-6.8800%2C107.5600%2C-6.8600&amp;layer=mapnik"
                            title="Peta lokasi sekolah" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            class="h-48 w-full border-0 grayscale-[35%]"></iframe>
                    </div>

                    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2">
                        <li v-for="link in props.links" :key="link.hash">
                            <a :href="link.hash" class="text-sm text-cream-200/70 transition hover:text-mint-300"
                                @click.prevent="goTo(link.hash)">
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-14 flex flex-col items-center justify-between gap-3 border-t border-cream-200/10 pt-8 text-xs text-cream-300/60 sm:flex-row">
                <p>&copy; {{ currentYear }} {{ props.schoolName }}. Seluruh hak cipta dilindungi.</p>
                <p>Dibangun dengan penuh amanah untuk pendidikan Indonesia.</p>
            </div>
        </div>
    </footer>
</template>
