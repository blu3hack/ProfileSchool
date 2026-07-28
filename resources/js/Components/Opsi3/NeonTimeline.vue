<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { gsap, refreshScrollTriggers } from '../../lib/smooth-scroll';

/**
 * Linimasa prestasi versi neon: "data stream" vertikal yang terisi mengikuti
 * scroll, dengan kartu yang terbang masuk dari kedalaman (rotateY + translateZ)
 * — jauh lebih dramatis dari geser datar pada Opsi 2.
 */
const props = defineProps({
    items: { type: Array, default: () => [] },
});

const root = ref(null);
const line = ref(null);

let ctx = null;

const levelTone = {
    Internasional: 'border-plasma-400/50 bg-plasma-400/12 text-plasma-300',
    Nasional: 'border-aqua-400/50 bg-aqua-400/12 text-aqua-200',
    Provinsi: 'border-solar-400/50 bg-solar-400/12 text-solar-300',
};

const toneOf = (level) => levelTone[level] ?? levelTone.Nasional;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    // gsap.context menampung semua tween + trigger agar sekali revert bersih.
    ctx = gsap.context(() => {
        // Berkas cahaya terisi seiring scroll.
        gsap.fromTo(
            line.value,
            { scaleY: 0 },
            {
                scaleY: 1,
                ease: 'none',
                transformOrigin: 'top center',
                scrollTrigger: {
                    trigger: root.value,
                    start: 'top 70%',
                    end: 'bottom 75%',
                    scrub: true,
                },
            },
        );

        const rows = gsap.utils.toArray('[data-timeline-row]', root.value);

        rows.forEach((row, index) => {
            const card = row.querySelector('[data-timeline-card]');
            const node = row.querySelector('[data-timeline-node]');
            const fromLeft = index % 2 === 0;

            // Kartu datang dari kedalaman ruang, bukan sekadar dari samping.
            // `toggleActions` (bukan `once`) supaya animasinya terulang setiap
            // kali baris ini masuk viewport lagi, bukan cuma sekali seumur halaman.
            gsap.from(card, {
                x: fromLeft ? -90 : 90,
                z: -420,
                y: 50,
                opacity: 0,
                rotateY: fromLeft ? -32 : 32,
                rotateX: 10,
                duration: 1.15,
                ease: 'power3.out',
                scrollTrigger: { trigger: row, start: 'top 84%', toggleActions: 'play none none reverse' },
            });

            gsap.from(node, {
                scale: 0,
                opacity: 0,
                duration: 0.8,
                delay: 0.15,
                ease: 'back.out(2.4)',
                scrollTrigger: { trigger: row, start: 'top 84%', toggleActions: 'play none none reverse' },
            });
        });

        // Konten baru bisa menggeser layout — hitung ulang sekali setelah mount.
        // Digabung dengan permintaan refresh komponen lain di frame yang sama.
        refreshScrollTriggers();
    }, root.value);
});

onBeforeUnmount(() => ctx?.revert());
</script>

<template>
    <div ref="root" class="relative" style="perspective: 1500px">
        <!-- Rel linimasa -->
        <div
            class="pointer-events-none absolute inset-y-0 left-5 w-px bg-void-600/80 md:left-1/2 md:-translate-x-1/2">
        </div>
        <div ref="line"
            class="pointer-events-none absolute inset-y-0 left-5 w-[3px] -translate-x-[1px] rounded-full bg-linear-to-b from-aqua-400 via-volt-400 to-plasma-400 shadow-[0_0_22px_rgba(52,226,245,0.85)] md:left-1/2 md:-translate-x-1/2">
        </div>

        <ol class="relative space-y-10 md:space-y-16" style="transform-style: preserve-3d">
            <li v-for="(item, index) in props.items" :key="item.title" data-timeline-row
                class="relative flex flex-col gap-4 pl-14 md:grid md:grid-cols-2 md:items-center md:gap-12 md:pl-0">
                <!-- Titik simpul berdenyut -->
                <span data-timeline-node
                    class="pulse-node absolute left-5 top-6 z-10 flex h-11 w-11 -translate-x-1/2 items-center justify-center rounded-full border border-aqua-400/40 bg-void-900/90 text-lg backdrop-blur md:left-1/2 md:top-1/2 md:-translate-y-1/2">
                    <span class="absolute inset-0 rounded-full bg-aqua-400/25 blur-md"></span>
                    <span class="relative">{{ item.icon }}</span>
                </span>

                <!-- Kartu prestasi, bergantian sisi kiri/kanan di layar lebar -->
                <div data-timeline-card
                    class="holo-panel holo-edge group rounded-[1.75rem] p-6 transition duration-500 hover:-translate-y-1.5 hover:shadow-[0_0_50px_-14px_rgba(52,226,245,0.6)]"
                    :class="index % 2 === 0
                        ? 'md:col-start-1 md:mr-4 md:text-right'
                        : 'md:col-start-2 md:ml-4'">
                    <div class="flex flex-wrap items-center gap-2"
                        :class="index % 2 === 0 ? 'md:justify-end' : ''">
                        <span class="rounded-full border px-3 py-1 text-[11px] font-bold" :class="toneOf(item.level)">
                            {{ item.level }}
                        </span>
                        <span
                            class="rounded-full border border-void-600 bg-void-800/70 px-3 py-1 font-mono text-[11px] font-bold text-slate-300">
                            {{ item.year }}
                        </span>
                    </div>

                    <h3 class="mt-4 font-display text-lg font-bold leading-snug text-slate-50">{{ item.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300/75">{{ item.description }}</p>

                    <div class="mt-5 hairline-neon"></div>

                    <p class="mt-4 text-sm font-semibold text-aqua-200">
                        {{ item.student }}
                        <span class="ml-1 text-xs font-medium text-slate-400">· {{ item.grade }}</span>
                    </p>
                </div>

                <!-- Kolom kosong penyeimbang grid -->
                <div class="hidden md:block"
                    :class="index % 2 === 0 ? 'md:col-start-2' : 'md:col-start-1 md:row-start-1'">
                </div>
            </li>
        </ol>
    </div>
</template>
