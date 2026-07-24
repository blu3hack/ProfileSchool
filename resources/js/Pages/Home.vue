<script setup>
import { Head } from '@inertiajs/vue3';
import HeroScene from '../Components/HeroScene.vue';
import ShowcaseSlider from '../Components/ShowcaseSlider.vue';

defineProps({
    appName: String,
    laravelVersion: String,
    phpVersion: String,
});

const features = [
    { icon: '🧊', title: 'Three.js / WebGL', description: 'Render 3D real-time langsung di browser, tanpa plugin.' },
    { icon: '⚡', title: 'Inertia + Vue 3', description: 'SPA yang responsif dengan routing Laravel yang familiar.' },
    { icon: '🌊', title: 'Lenis Smooth Scroll', description: 'Scroll ter-interpolasi yang halus dan enak diikuti.' },
    { icon: '🎬', title: 'GSAP ScrollTrigger', description: 'Animasi presisi yang terpicu saat elemen masuk layar.' },
    { icon: '🎠', title: 'Swiper', description: 'Carousel dengan efek coverflow, autoplay, dan gesture.' },
];
</script>

<template>
    <Head title="Home" />

    <div class="min-h-screen bg-[#070b18] text-slate-100">
        <!-- HERO 3D -->
        <section class="relative h-screen w-full overflow-hidden">
            <div class="absolute inset-0">
                <HeroScene color="#6366f1" />
            </div>

            <div class="pointer-events-none relative z-10 flex h-full flex-col items-center justify-center px-6 text-center">
                <div v-reveal="{ from: 'zoom', duration: 1.2 }">
                    <h1 class="bg-linear-to-r from-indigo-300 via-white to-pink-300 bg-clip-text text-5xl font-semibold text-transparent sm:text-7xl">
                        {{ appName }}
                    </h1>
                </div>

                <p v-reveal="{ delay: 0.25 }" class="mt-6 max-w-xl text-base text-slate-300 sm:text-lg">
                    Website 3D interaktif dengan animasi scroll yang halus.
                </p>

                <div v-reveal="{ delay: 0.5, from: 'fade' }" class="mt-10 text-xs uppercase tracking-[0.3em] text-slate-500">
                    Scroll ke bawah
                </div>
            </div>
        </section>

        <!-- FITUR -->
        <section class="mx-auto max-w-5xl px-6 py-32">
            <h2 v-reveal class="text-center text-3xl font-semibold sm:text-4xl">Teknologi yang terpasang</h2>

            <div v-reveal="{ stagger: 0.12 }" class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="feature in features" :key="feature.title"
                    class="rounded-2xl border border-white/10 bg-white/3 p-6 transition hover:border-indigo-400/40 hover:bg-white/6">
                    <div class="text-3xl">{{ feature.icon }}</div>
                    <h3 class="mt-4 font-semibold">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ feature.description }}</p>
                </div>
            </div>
        </section>

        <!-- SLIDER -->
        <section class="py-24">
            <h2 v-reveal="'left'" class="px-6 text-center text-3xl font-semibold sm:text-4xl">Showcase Slider</h2>
            <p v-reveal="{ delay: 0.15 }" class="mt-3 px-6 text-center text-sm text-slate-400">
                Digerakkan Swiper — drag, atau biarkan autoplay berjalan.
            </p>

            <div v-reveal="{ from: 'fade', delay: 0.3 }" class="mt-14">
                <ShowcaseSlider :items="features" />
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-white/10 px-6 py-16 text-center text-sm text-slate-500">
            <p v-reveal="{ from: 'fade' }">
                Laravel v{{ laravelVersion }} &middot; PHP v{{ phpVersion }} &middot; Vue 3 &middot; Three.js
            </p>
        </footer>
    </div>
</template>
