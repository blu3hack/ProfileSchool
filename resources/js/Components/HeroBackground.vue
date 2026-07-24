<script setup>
import { TresCanvas } from '@tresjs/core';
import ParticleField from './Three/ParticleField.vue';
import SoftGeometry from './Three/SoftGeometry.vue';

// Bentuk-bentuk melayang di sekeliling headline — sengaja dijauhkan dari
// tengah layar supaya teks hero tetap terbaca jelas.
const shapes = [
    { position: [-4.2, 1.1, -1.5], scale: 0.95, shape: 'octa', color: '#74a289', speed: 0.9, offset: 0 },
    { position: [4.3, -0.8, -1.2], scale: 1.15, shape: 'icosa', color: '#9bbfab', speed: 0.7, offset: 1.4 },
    { position: [3.1, 1.9, -2.6], scale: 0.7, shape: 'torus', color: '#d9b26a', speed: 1.1, offset: 2.6 },
    { position: [-3.4, -1.8, -2.2], scale: 0.6, shape: 'octa', color: '#cbab7d', speed: 1.2, offset: 3.8 },
    { position: [0.4, 2.4, -4], scale: 0.8, shape: 'icosa', color: '#c1d9cb', speed: 0.6, offset: 5.1 },
];
</script>

<template>
    <TresCanvas clear-color="#fdfbf7" :alpha="true" :antialias="true" :power-preference="'high-performance'">
        <TresPerspectiveCamera :position="[0, 0, 7]" :fov="50" />

        <ParticleField :count="800" color="#9bbfab" :spread="16" />

        <SoftGeometry v-for="(shape, index) in shapes" :key="index" v-bind="shape" />

        <TresAmbientLight :intensity="1.5" />
        <TresDirectionalLight :position="[5, 6, 4]" :intensity="1.8" color="#ffffff" />
        <TresPointLight :position="[-5, -3, 2]" :intensity="14" color="#d9b26a" />
    </TresCanvas>
</template>
