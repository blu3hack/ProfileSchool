<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';

/**
 * Popup (lightbox) foto galeri: menampilkan gambar besar beserta judul &
 * keterangannya. Dipakai bersama oleh carousel di beranda dan halaman
 * /galeri lengkap.
 *
 * Dikendalikan lewat `index` (v-model): angka = foto yang dibuka,
 * null = tertutup. Menyediakan navigasi prev/next + dukungan keyboard.
 */
const props = defineProps({
    images: { type: Array, default: () => [] },
    /** Indeks foto yang sedang dibuka; null berarti popup tertutup. */
    index: { type: Number, default: null },
});

const emit = defineEmits(['update:index']);

const isOpen = computed(() => props.index !== null && props.index >= 0);
const current = computed(() => (isOpen.value ? props.images[props.index] ?? null : null));

const close = () => emit('update:index', null);

const go = (step) => {
    if (!isOpen.value || !props.images.length) {
        return;
    }

    const next = (props.index + step + props.images.length) % props.images.length;
    emit('update:index', next);
};

const onKeydown = (event) => {
    if (!isOpen.value) {
        return;
    }

    if (event.key === 'Escape') {
        close();
    } else if (event.key === 'ArrowRight') {
        go(1);
    } else if (event.key === 'ArrowLeft') {
        go(-1);
    }
};

/** Kunci scroll body + pasang listener keyboard hanya selama popup terbuka. */
const scrollLock = ref('');

watch(isOpen, (open) => {
    if (typeof document === 'undefined') {
        return;
    }

    if (open) {
        scrollLock.value = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', onKeydown);
    } else {
        document.body.style.overflow = scrollLock.value;
        window.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = scrollLock.value;
        window.removeEventListener('keydown', onKeydown);
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="lightbox-fade">
            <div v-if="isOpen && current" class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-8"
                role="dialog" aria-modal="true" :aria-label="current.title || 'Foto galeri'">
                <!-- Latar gelap — klik untuk menutup. -->
                <div class="absolute inset-0 bg-void-950/85 backdrop-blur-md" @click="close"></div>
                <div class="scanlines pointer-events-none absolute inset-0 opacity-30"></div>

                <!-- Tombol tutup -->
                <button type="button" aria-label="Tutup"
                    class="absolute right-4 top-4 z-10 flex h-11 w-11 items-center justify-center rounded-full border border-white/15 bg-void-900/70 text-lg text-slate-200 transition hover:border-aqua-400/60 hover:text-aqua-200 sm:right-6 sm:top-6"
                    @click="close">
                    ✕
                </button>

                <!-- Navigasi prev/next -->
                <button v-if="props.images.length > 1" type="button" aria-label="Foto sebelumnya"
                    class="absolute left-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-void-900/70 text-xl text-slate-200 transition hover:border-aqua-400/60 hover:text-aqua-200 sm:left-6"
                    @click.stop="go(-1)">
                    ‹
                </button>
                <button v-if="props.images.length > 1" type="button" aria-label="Foto berikutnya"
                    class="absolute right-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-void-900/70 text-xl text-slate-200 transition hover:border-aqua-400/60 hover:text-aqua-200 sm:right-6"
                    @click.stop="go(1)">
                    ›
                </button>

                <!-- Isi popup -->
                <figure class="relative z-[1] flex max-h-full w-full max-w-4xl flex-col overflow-hidden rounded-[1.75rem] border border-aqua-400/20 bg-void-900/80 shadow-[0_0_80px_-20px_rgba(52,226,245,0.6)]"
                    @click.stop>
                    <div class="relative flex-1 overflow-hidden bg-void-950">
                        <Transition name="lightbox-img" mode="out-in">
                            <img :key="current.src" :src="current.src" :alt="current.alt || current.title"
                                class="mx-auto max-h-[65vh] w-full object-contain">
                        </Transition>
                    </div>

                    <figcaption v-if="current.title || current.caption"
                        class="relative border-t border-white/10 px-6 py-5">
                        <div class="pattern-lattice-neon pointer-events-none absolute inset-0 opacity-10"></div>
                        <div class="relative">
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-aqua-300">
                                Galeri Sekolah
                            </p>
                            <h3 v-if="current.title" class="mt-1.5 font-display text-lg font-bold text-slate-50 sm:text-xl">
                                {{ current.title }}
                            </h3>
                            <p v-if="current.caption" class="mt-2 text-sm leading-relaxed text-slate-300/85">
                                {{ current.caption }}
                            </p>
                            <p v-if="current.credit" class="mt-2 text-[10px] uppercase tracking-[0.18em] text-slate-500">
                                {{ current.credit }}
                            </p>
                        </div>
                    </figcaption>

                    <span v-if="props.images.length > 1"
                        class="pointer-events-none absolute right-5 top-4 rounded-full bg-void-950/70 px-3 py-1 text-[11px] font-semibold tabular-nums text-slate-300">
                        {{ props.index + 1 }} / {{ props.images.length }}
                    </span>
                </figure>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
    transition: opacity 0.3s ease;
}

.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
    opacity: 0;
}

.lightbox-img-enter-active,
.lightbox-img-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.lightbox-img-enter-from {
    opacity: 0;
    transform: scale(0.98);
}

.lightbox-img-leave-to {
    opacity: 0;
    transform: scale(1.02);
}

@media (prefers-reduced-motion: reduce) {

    .lightbox-fade-enter-active,
    .lightbox-fade-leave-active,
    .lightbox-img-enter-active,
    .lightbox-img-leave-active {
        transition-duration: 0.01ms;
    }

    .lightbox-img-enter-from,
    .lightbox-img-leave-to {
        transform: none;
    }
}
</style>
