<script setup>
import { shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

import { usePointerScroll } from '../../../lib/pointer-scroll';

/**
 * Cincin poligon tipis (default segi delapan). Dua cincin dengan `rotation`
 * berselisih 22.5° akan membentuk bintang delapan — motif dasar geometri islami.
 */
const props = defineProps({
    position: { type: Array, default: () => [0, 0, 0] },
    radius: { type: Number, default: 2 },
    thickness: { type: Number, default: 0.02 },
    segments: { type: Number, default: 8 },
    color: { type: String, default: '#8adcc2' },
    opacity: { type: Number, default: 0.55 },
    rotation: { type: Number, default: 0 },
    speed: { type: Number, default: 0.12 },
    /** Seberapa jauh cincin ikut bergeser saat halaman di-scroll. */
    scrollShift: { type: Number, default: 2 },
    pointerStrength: { type: Number, default: 0.5 },
});

const mesh = shallowRef(null);

const { pointer, scroll } = usePointerScroll();

const baseZ = props.position[2] ?? 0;

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed, delta }) => {
    const ring = mesh.value;

    if (!ring) {
        return;
    }

    ring.rotation.z = props.rotation + elapsed * props.speed;
    ring.rotation.x = Math.sin(elapsed * 0.3) * 0.25;

    // Cincin melayang menjauh/mendekat kamera seiring scroll → kesan kedalaman.
    const damping = Math.min(1, delta * 2.5);
    const targetZ = baseZ - scroll.progress * props.scrollShift;
    const targetX = (props.position[0] ?? 0) + pointer.x * props.pointerStrength;
    const targetY = (props.position[1] ?? 0) - pointer.y * props.pointerStrength;

    ring.position.z += (targetZ - ring.position.z) * damping;
    ring.position.x += (targetX - ring.position.x) * damping;
    ring.position.y += (targetY - ring.position.y) * damping;
});
</script>

<template>
    <TresMesh ref="mesh" :position="props.position">
        <TresRingGeometry :args="[props.radius, props.radius + props.thickness, props.segments]" />
        <TresMeshBasicMaterial :color="props.color" :transparent="true" :opacity="props.opacity" :side="2"
            :depth-write="false" />
    </TresMesh>
</template>
