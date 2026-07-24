<script setup>
import { onMounted, shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

import { usePointerScroll } from '../../../lib/pointer-scroll';

/**
 * Lantai kisi neon yang berlari menjauh dari kamera — fondasi visual
 * bertema cyber. Dua GridHelper ditumpuk (atas & bawah) agar hero terasa
 * seperti berada di dalam koridor cahaya.
 */
const props = defineProps({
    /** Panjang sisi kisi. */
    size: { type: Number, default: 90 },
    /** Jumlah petak per sisi — menentukan jarak antar garis. */
    divisions: { type: Number, default: 45 },
    color: { type: String, default: '#0fc3dd' },
    centerColor: { type: String, default: '#a97bff' },
    opacity: { type: Number, default: 0.55 },
    /** Ketinggian kisi terhadap titik nol. */
    y: { type: Number, default: -4 },
    speed: { type: Number, default: 3.2 },
});

const grid = shallowRef(null);

const { scroll } = usePointerScroll();

/** Jarak antar garis — dipakai untuk mengulang posisi tanpa terlihat "lompat". */
const step = props.size / props.divisions;

onMounted(() => {
    const material = grid.value?.material;

    if (!material) {
        return;
    }

    material.transparent = true;
    material.opacity = props.opacity;
    material.depthWrite = false;
});

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed }) => {
    if (!grid.value) {
        return;
    }

    // Modulo satu petak: kisi terlihat bergerak terus tanpa pernah habis.
    grid.value.position.z = ((elapsed * props.speed) % step) + scroll.progress * 6;
});
</script>

<template>
    <TresGridHelper ref="grid" :args="[props.size, props.divisions, props.centerColor, props.color]"
        :position="[0, props.y, 0]" />
</template>
