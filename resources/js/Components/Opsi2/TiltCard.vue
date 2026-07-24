<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { gsap } from '../../lib/smooth-scroll';

/**
 * Pembungkus kartu dengan efek tilt 3D mengikuti kursor, plus sorotan
 * cahaya (glare) yang bergerak. Memakai gsap.quickTo agar tiap pointermove
 * tidak membuat tween baru.
 */
const props = defineProps({
    /** Sudut kemiringan maksimum (derajat). */
    max: { type: Number, default: 10 },
    /** Skala saat kursor berada di atas kartu. */
    scale: { type: Number, default: 1.03 },
    glare: { type: Boolean, default: true },
});

const scene = ref(null);
const body = ref(null);

let setRotateX = null;
let setRotateY = null;
let setScale = null;
let reduced = false;

const glareX = ref(50);
const glareY = ref(50);
const hovering = ref(false);

onMounted(() => {
    reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced || !body.value) {
        return;
    }

    const options = { duration: 0.5, ease: 'power3.out' };

    setRotateX = gsap.quickTo(body.value, 'rotationX', options);
    setRotateY = gsap.quickTo(body.value, 'rotationY', options);
    setScale = gsap.quickTo(body.value, 'scale', options);
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
};
</script>

<template>
    <div ref="scene" class="tilt-scene" @pointermove="onPointerMove" @pointerleave="onPointerLeave">
        <div ref="body" class="tilt-body relative h-full">
            <slot />

            <!-- Sorotan cahaya mengikuti kursor. -->
            <div v-if="props.glare"
                class="pointer-events-none absolute inset-0 rounded-[inherit] opacity-0 transition-opacity duration-300"
                :class="{ 'opacity-100': hovering }" :style="{
                    background: `radial-gradient(22rem circle at ${glareX}% ${glareY}%, rgba(255,255,255,0.55), transparent 45%)`,
                }"></div>
        </div>
    </div>
</template>
