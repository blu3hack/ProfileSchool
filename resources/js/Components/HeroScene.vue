<script setup>
import { TresCanvas } from '@tresjs/core';
import { OrbitControls, Stars } from '@tresjs/cientos';
import AnimatedKnot from './Three/AnimatedKnot.vue';

const props = defineProps({
    color: { type: String, default: '#6366f1' },
    controls: { type: Boolean, default: true },
});
</script>

<template>
    <TresCanvas clear-color="#070b18" :alpha="true">
        <TresPerspectiveCamera :position="[0, 0, 6]" :fov="45" />
        <OrbitControls v-if="props.controls" :enable-zoom="false" :enable-pan="false" :auto-rotate="true"
            :auto-rotate-speed="0.4" />

        <Stars :radius="60" :depth="40" :count="1800" :size="0.7" :size-attenuation="true" />

        <AnimatedKnot :color="props.color" />

        <TresAmbientLight :intensity="0.6" />
        <TresDirectionalLight :position="[4, 5, 5]" :intensity="2.4" />
        <TresPointLight :position="[-5, -2, -3]" :intensity="18" color="#ec4899" />
    </TresCanvas>
</template>
