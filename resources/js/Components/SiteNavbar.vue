<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { getSmoothScroll } from '../lib/smooth-scroll';

const props = defineProps({
    schoolName: { type: String, default: 'Alazka Islamic School' },
    links: { type: Array, default: () => [] },
});

const scrolled = ref(false);
const menuOpen = ref(false);

const onScroll = () => (scrolled.value = window.scrollY > 24);

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

/** Lenis mengendalikan scroll, jadi anchor default browser dilewati. */
const goTo = (hash) => {
    menuOpen.value = false;

    const target = document.querySelector(hash);

    if (!target) {
        return;
    }

    const lenis = getSmoothScroll();

    if (lenis) {
        lenis.scrollTo(target, { offset: -80, duration: 1.4 });
    } else {
        target.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
        :class="scrolled || menuOpen ? 'bg-cream-50/85 shadow-[0_4px_30px_-18px_rgba(36,57,49,0.6)] backdrop-blur-xl' : 'bg-transparent'">
        <nav class="container-page flex h-18 items-center justify-between gap-4 py-3" aria-label="Navigasi utama">
            <!-- Logo -->
            <a href="#beranda" class="flex shrink-0 items-center gap-3" @click.prevent="goTo('#beranda')">
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-linear-to-br from-sage-500 to-sage-700 font-display text-lg font-bold text-cream-50 shadow-md">
                    A
                </span>
                <span class="leading-tight">
                    <span class="block font-display text-base font-bold text-sage-800">{{ props.schoolName }}</span>
                    <span class="block text-[11px] font-medium tracking-wide text-sage-500">SD &amp; SMP Islam
                        Terpadu</span>
                </span>
            </a>

            <!-- Menu desktop -->
            <ul class="hidden items-center gap-1 lg:flex">
                <li v-for="link in props.links" :key="link.hash">
                    <a :href="link.hash"
                        class="rounded-full px-4 py-2 text-sm font-semibold text-sage-700 transition hover:bg-sage-100 hover:text-sage-900"
                        @click.prevent="goTo(link.hash)">
                        {{ link.label }}
                    </a>
                </li>
            </ul>

            <div class="flex items-center gap-2">
                <a href="#ppdb"
                    class="hidden rounded-full bg-sage-600 px-5 py-2.5 text-sm font-semibold text-cream-50 shadow-lg shadow-sage-600/25 transition hover:-translate-y-0.5 hover:bg-sage-700 sm:inline-block"
                    @click.prevent="goTo('#ppdb')">
                    Pendaftaran (PPDB)
                </a>

                <!-- Tombol menu mobile -->
                <button type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-2xl border border-sage-200 text-sage-700 transition hover:bg-sage-100 lg:hidden"
                    :aria-expanded="menuOpen" aria-controls="menu-mobile" aria-label="Buka menu navigasi"
                    @click="menuOpen = !menuOpen">
                    <svg v-if="!menuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Menu mobile -->
        <Transition enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0" leave-active-class="transition duration-150 ease-in"
            leave-to-class="-translate-y-2 opacity-0">
            <div v-show="menuOpen" id="menu-mobile" class="border-t border-sage-100 lg:hidden">
                <ul class="container-page flex flex-col gap-1 py-4">
                    <li v-for="link in props.links" :key="link.hash">
                        <a :href="link.hash"
                            class="block rounded-2xl px-4 py-3 text-sm font-semibold text-sage-700 transition hover:bg-sage-100"
                            @click.prevent="goTo(link.hash)">
                            {{ link.label }}
                        </a>
                    </li>
                    <li>
                        <a href="#ppdb"
                            class="mt-2 block rounded-2xl bg-sage-600 px-4 py-3 text-center text-sm font-semibold text-cream-50"
                            @click.prevent="goTo('#ppdb')">
                            Pendaftaran (PPDB)
                        </a>
                    </li>
                </ul>
            </div>
        </Transition>
    </header>
</template>
