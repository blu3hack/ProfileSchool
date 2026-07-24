<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { gsap } from '../../lib/smooth-scroll';

/**
 * Kartu tilt 3D versi Opsi 3 — lebih ekstrem dari Opsi 2:
 * kemiringan lebih dalam, sorotan neon mengikuti kursor, kilau diagonal
 * yang menyapu, dan sedikit "angkat" pada sumbu Z saat hover.
 *
 * gsap.quickTo dipakai agar pointermove tidak membuat tween baru tiap frame.
 */
const props = defineProps({
    /** Sudut kemiringan maksimum (derajat). */
    max: { type: Number, default: 14 },
    scale: { type: Number, default: 1.04 },
    /** Seberapa jauh kartu terangkat mendekati kamera saat hover (px). */
    lift: { type: Number, default: 40 },
    glare: { type: Boolean, default: true },
    /** Warna sorotan kursor — sesuaikan dengan aksen kartu. */
    glareColor: { type: String, default: 'rgba(124, 243, 255, 0.28)' },
    /**
     * Radius lapisan overlay. Harus disamakan dengan radius kartu di dalam
     * slot, karena `border-radius: inherit` tidak bisa menembus wrapper tilt.
     */
    radius: { type: String, default: '1.75rem' },
});

const scene = ref(null);
const body = ref(null);

let setRotateX = null;
let setRotateY = null;
let setScale = null;
let setZ = null;
let reduced = false;

const glareX = ref(50);
const glareY = ref(50);
const hovering = ref(false);

onMounted(() => {
    reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced || !body.value) {
        return;
    }

    const options = { duration: 0.6, ease: 'power3.out' };

    setRotateX = gsap.quickTo(body.value, 'rotationX', options);
    setRotateY = gsap.quickTo(body.value, 'rotationY', options);
    setScale = gsap.quickTo(body.value, 'scale', options);
    setZ = gsap.quickTo(body.value, 'z', options);
});

onBeforeUnmount(() => {
    if (body.value) {
        gsap.killTweensOf(body.value);
    }
});

const onPointerMove = (event) => {
    if (reduced || !scene.value || !setRotateX) {
        return;
    }

    const rect = scene.value.getBoundingClientRect();

    // Posisi kursor relatif terhadap pusat kartu: -0.5 … 0.5
    const px = (event.clientX - rect.left) / rect.width;
    const py = (event.clientY - rect.top) / rect.height;

    setRotateX(-(py - 0.5) * props.max * 2);
    setRotateY((px - 0.5) * props.max * 2);
    setScale(props.scale);
    setZ(props.lift);

    glareX.value = px * 100;
    glareY.value = py * 100;
    hovering.value = true;
};

const onPointerLeave = () => {
    hovering.value = false;

    if (reduced || !setRotateX) {
        return;
    }

    setRotateX(0);
    setRotateY(0);
    setScale(1);
    setZ(0);
};
</script>

<template>
    <div ref="scene" class="stage-3d" @pointermove="onPointerMove" @pointerleave="onPointerLeave">
        <div ref="body" class="stage-body relative h-full">
            <slot :hovering="hovering" />

            <!-- Sorotan neon mengikuti kursor. -->
            <div v-if="props.glare"
                class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300"
                :class="{ 'opacity-100': hovering }" :style="{
                    borderRadius: props.radius,
                    background: `radial-gradient(20rem circle at ${glareX}% ${glareY}%, ${props.glareColor}, transparent 55%)`,
                }"></div>

            <!-- Kilau diagonal yang menyapu sekali saat kursor masuk. -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden"
                :style="{ borderRadius: props.radius }">
                <div class="absolute -inset-y-12 -left-1/3 w-1/3 -skew-x-12 bg-linear-to-r from-transparent via-white/12 to-transparent transition-transform duration-900 ease-out"
                    :class="hovering ? 'translate-x-[420%]' : 'translate-x-0'"></div>
            </div>
        </div>
    </div>
</template>
