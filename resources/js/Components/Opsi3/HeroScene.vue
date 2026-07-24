<script setup>
import { computed } from 'vue';
import { TresCanvas } from '@tresjs/core';

import NeonFloor from './Three/NeonFloor.vue';
import NeonSwarm from './Three/NeonSwarm.vue';
import HoloPoly from './Three/HoloPoly.vue';

/**
 * Scene 3D hero Opsi 3 — kanvas dibuat transparan (`clear-alpha` 0) supaya
 * foto gedung sekolah di belakangnya tetap terlihat, lalu dilapisi
 * lantai kisi neon, kabut partikel, dan polihedron holografik melayang.
 */
const props = defineProps({
    /** 'dark' | 'light' — neon diturunkan luminansinya saat tema terang. */
    theme: { type: String, default: 'dark' },
    /**
     * 'high' (desktop) | 'low' (sentuh/mobile). Menentukan jumlah partikel,
     * antialias, dan DPR agar scene tetap ringan di GPU ponsel/tablet.
     */
    quality: { type: String, default: 'high' },
});

const isLight = computed(() => props.theme === 'light');
const isLow = computed(() => props.quality === 'low');

/**
 * Jumlah partikel & DPR dibatasi pada perangkat sentuh. Partikel adalah biaya
 * terberat: tiap frame seluruh posisinya di-loop di CPU lalu diunggah ke GPU,
 * jadi menurunkannya (± setengah) langsung terasa pada kelancaran.
 */
const swarm = computed(() => (isLow.value
    ? { near: 240, mid: 150, far: 60 }
    : { near: 620, mid: 380, far: 140 }));

/** DPR dibatasi supaya GPU tidak me-render pada resolusi retina penuh. */
const dpr = computed(() => (isLow.value ? [1, 1.5] : [1, 2]));

/** Warna neon versi gelap (default) dan versi terang (lebih pekat). */
const palette = computed(() => (isLight.value
    ? {
        aqua: '#0b8ba3',
        aquaSoft: '#0aa2bd',
        volt: '#6231cc',
        voltSoft: '#7c3aed',
        plasma: '#c2249a',
        solar: '#c98d0f',
    }
    : {
        aqua: '#0fc3dd',
        aquaSoft: '#7cf3ff',
        volt: '#8b4dff',
        voltSoft: '#c9b0ff',
        plasma: '#ff9ae4',
        solar: '#ffc73d',
    }));

/** Partikel perlu lebih pekat di atas latar terang agar tetap terbaca. */
const opacityScale = computed(() => (isLight.value ? 0.75 : 1));

const polys = computed(() => [
    { shape: 'octa', position: [-4.6, 1.2, -2], radius: 1.5, color: palette.value.aquaSoft, opacity: 0.55, speed: 0.22 },
    { shape: 'octa', position: [4.8, -0.6, -3], radius: 2.1, color: palette.value.voltSoft, opacity: 0.45, speed: -0.18 },
    { shape: 'torus', position: [3.2, 2.4, -5], radius: 1.5, color: palette.value.plasma, opacity: 0.5, speed: 0.3 },
    { shape: 'ico', position: [-3.2, -2.4, -4], radius: 1.1, color: palette.value.solar, opacity: 0.45, speed: -0.26 },
]);
</script>

<template>
    <TresCanvas :alpha="true" :clear-alpha="0" :antialias="!isLow" :dpr="dpr"
        power-preference="high-performance">
        <TresPerspectiveCamera :position="[0, 0.6, 9]" :fov="55" />

        <!-- Lantai koridor: dua kisi (bawah & atas) yang berlari menjauh. -->
        <NeonFloor :y="-4.2" :speed="3.4" :opacity="0.5 * opacityScale" :color="palette.aqua"
            :center-color="palette.volt" />
        <NeonFloor :y="5.2" :speed="2.2" :opacity="0.22 * opacityScale" :color="palette.volt"
            :center-color="palette.plasma" />

        <!-- Kabut partikel berlapis: aqua rapat di depan, volt renggang di belakang. -->
        <NeonSwarm :count="swarm.near" :color="palette.aquaSoft" :size="0.055" :opacity="0.85 * opacityScale"
            :pointer-strength="1" />
        <NeonSwarm :count="swarm.mid" :spread="[34, 18, 26]" :color="palette.voltSoft" :size="0.045"
            :opacity="0.5 * opacityScale" :pointer-strength="0.45" :rise="0.22" :spin="-0.015" />
        <NeonSwarm :count="swarm.far" :spread="[20, 12, 12]" :color="palette.plasma" :size="0.05"
            :opacity="0.45 * opacityScale" :pointer-strength="1.5" :rise="0.5" />

        <HoloPoly v-for="(poly, index) in polys" :key="index" v-bind="poly" />

        <TresAmbientLight :intensity="1.4" />
        <TresDirectionalLight :position="[4, 6, 5]" :intensity="1.2" :color="palette.aquaSoft" />
    </TresCanvas>
</template>
