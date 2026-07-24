<script setup>
import { TresCanvas } from '@tresjs/core';

import IslamicParticles from './Three/IslamicParticles.vue';
import GeometryRing from './Three/GeometryRing.vue';

/**
 * Latar 3D hero: rosette partikel bersimetri delapan yang merespons kursor
 * dan scroll, dikelilingi cincin poligon melayang.
 */

// Dua cincin dengan selisih 22.5° membentuk siluet bintang delapan.
const rings = [
    { position: [-3.6, 1.4, -3], radius: 2.1, color: '#8adcc2', rotation: 0, speed: 0.1, opacity: 0.5 },
    { position: [-3.6, 1.4, -3], radius: 2.1, color: '#8adcc2', rotation: Math.PI / 8, speed: -0.1, opacity: 0.5 },
    { position: [4.4, -1.2, -4], radius: 2.8, color: '#c4b8f3', rotation: 0, speed: -0.08, opacity: 0.45 },
    { position: [4.4, -1.2, -4], radius: 2.8, color: '#c4b8f3', rotation: Math.PI / 8, speed: 0.08, opacity: 0.45 },
    { position: [1.6, 2.6, -6], radius: 1.4, color: '#e6c98f', rotation: 0, speed: 0.16, opacity: 0.55 },
    { position: [-2.4, -2.6, -5], radius: 1.1, color: '#a3cdf6', rotation: Math.PI / 8, speed: -0.14, opacity: 0.5 },
];
</script>

<template>
    <TresCanvas clear-color="#fbfcfe" :alpha="true" :antialias="true" power-preference="high-performance">
        <TresPerspectiveCamera :position="[0, 0, 9]" :fov="52" />

        <!-- Lapis 1: rosette utama, mint, bereaksi penuh ke kursor. -->
        <IslamicParticles :rings="9" :symmetry="8" :radius="6.2" color="#57c6a4" :size="0.05" :opacity="0.85"
            :pointer-strength="1" :spin="0.045" />

        <!-- Lapis 2: rosette lebih besar & redup sebagai kedalaman latar. -->
        <IslamicParticles :rings="6" :symmetry="8" :radius="9" color="#a595e6" :size="0.038" :opacity="0.5"
            :depth="3.4" :phase="Math.PI / 8" :pointer-strength="0.45" :spin="-0.03" />

        <!-- Lapis 3: percikan emas tipis agar nuansa tetap hangat. -->
        <IslamicParticles :rings="4" :symmetry="8" :radius="4.2" color="#d9b26a" :size="0.045" :opacity="0.55"
            :depth="1.6" :phase="Math.PI / 16" :pointer-strength="1.4" :spin="0.08" />

        <GeometryRing v-for="(ring, index) in rings" :key="index" v-bind="ring" />

        <TresAmbientLight :intensity="1.6" />
        <TresDirectionalLight :position="[4, 6, 5]" :intensity="1.4" color="#ffffff" />
    </TresCanvas>
</template>
