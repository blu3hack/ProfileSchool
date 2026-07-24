<script setup>
import { shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

import { usePointerScroll } from '../../../lib/pointer-scroll';

/**
 * Polihedron kerangka (wireframe) yang berputar lambat — "inti holografik"
 * pada hero. Bentuk oktahedron dipilih karena rusuk delapannya senapas
 * dengan motif bintang delapan geometri islami.
 */
const props = defineProps({
    /** 'octa' | 'ico' | 'torus' */
    shape: { type: String, default: 'octa' },
    position: { type: Array, default: () => [0, 0, 0] },
    radius: { type: Number, default: 1.6 },
    /** Tingkat subdivisi — 0 menghasilkan rusuk paling tegas. */
    detail: { type: Number, default: 0 },
    color: { type: String, default: '#34e2f5' },
    opacity: { type: Number, default: 0.6 },
    speed: { type: Number, default: 0.25 },
    /** Amplitudo gerak melayang naik-turun. */
    floatAmount: { type: Number, default: 0.35 },
    pointerStrength: { type: Number, default: 0.4 },
    scrollShift: { type: Number, default: 3 },
});

const mesh = shallowRef(null);

const { pointer, scroll } = usePointerScroll();

const [baseX, baseY, baseZ] = props.position;

/** Fase acak agar beberapa instance tidak melayang serempak. */
const phase = Math.random() * Math.PI * 2;

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed, delta }) => {
    const object = mesh.value;

    if (!object) {
        return;
    }

    object.rotation.x = elapsed * props.speed * 0.7;
    object.rotation.y = elapsed * props.speed;

    const damping = Math.min(1, delta * 2.5);
    const targetX = baseX + pointer.x * props.pointerStrength;
    const targetY = baseY + Math.sin(elapsed * 0.6 + phase) * props.floatAmount - pointer.y * props.pointerStrength;
    const targetZ = baseZ + scroll.progress * props.scrollShift;

    object.position.x += (targetX - object.position.x) * damping;
    object.position.y += (targetY - object.position.y) * damping;
    object.position.z += (targetZ - object.position.z) * damping;
});
</script>

<template>
    <TresMesh ref="mesh" :position="props.position">
        <TresOctahedronGeometry v-if="props.shape === 'octa'" :args="[props.radius, props.detail]" />
        <TresTorusGeometry v-else-if="props.shape === 'torus'"
            :args="[props.radius, props.radius * 0.06, 8, 48]" />
        <TresIcosahedronGeometry v-else :args="[props.radius, props.detail]" />

        <TresMeshBasicMaterial :color="props.color" :wireframe="true" :transparent="true" :opacity="props.opacity"
            :depth-write="false" />
    </TresMesh>
</template>
