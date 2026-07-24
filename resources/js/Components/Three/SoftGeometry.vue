<script setup>
import { shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

const props = defineProps({
    position: { type: Array, default: () => [0, 0, 0] },
    scale: { type: Number, default: 1 },
    color: { type: String, default: '#74a289' },
    speed: { type: Number, default: 1 },
    /** 'octa' & 'icosa' memberi kesan geometris islami yang tumpul/lembut. */
    shape: { type: String, default: 'octa' },
    offset: { type: Number, default: 0 },
});

const mesh = shallowRef(null);

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed }) => {
    const object = mesh.value;

    if (!object) {
        return;
    }

    const t = elapsed * props.speed;

    object.rotation.x = t * 0.12 + props.offset;
    object.rotation.y = t * 0.17 + props.offset;
    object.position.y = props.position[1] + Math.sin(t * 0.6 + props.offset) * 0.28;
});
</script>

<template>
    <TresMesh ref="mesh" :position="props.position" :scale="props.scale">
        <TresOctahedronGeometry v-if="props.shape === 'octa'" :args="[1, 0]" />
        <TresIcosahedronGeometry v-else-if="props.shape === 'icosa'" :args="[1, 0]" />
        <TresTorusGeometry v-else :args="[0.8, 0.26, 24, 64]" />

        <TresMeshStandardMaterial :color="props.color" :roughness="0.35" :metalness="0.15" :transparent="true"
            :opacity="0.55" :flat-shading="true" />
    </TresMesh>
</template>
