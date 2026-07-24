<script setup>
import { shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

const props = defineProps({
    color: { type: String, default: '#6366f1' },
});

const knot = shallowRef(null);

// useLoop() harus dipanggil dari komponen DI DALAM <TresCanvas>,
// karena butuh context render yang disediakan canvas.
const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed }) => {
    const mesh = knot.value;

    if (!mesh) {
        return;
    }

    mesh.rotation.x = elapsed * 0.18;
    mesh.rotation.y = elapsed * 0.26;
    mesh.position.y = Math.sin(elapsed * 0.9) * 0.18;
});
</script>

<template>
    <TresMesh ref="knot">
        <TresTorusKnotGeometry :args="[1.25, 0.38, 220, 40]" />
        <TresMeshStandardMaterial :color="props.color" :roughness="0.22" :metalness="0.85" />
    </TresMesh>
</template>
