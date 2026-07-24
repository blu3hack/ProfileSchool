<script setup>
import { onBeforeUnmount, onMounted, shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

const props = defineProps({
    count: { type: Number, default: 900 },
    color: { type: String, default: '#9bbfab' },
    spread: { type: Number, default: 14 },
});

const group = shallowRef(null);

// Sebar partikel sekali saja saat setup — tidak perlu reaktif.
const positions = new Float32Array(props.count * 3);

for (let i = 0; i < props.count; i += 1) {
    positions[i * 3] = (Math.random() - 0.5) * props.spread;
    positions[i * 3 + 1] = (Math.random() - 0.5) * props.spread * 0.7;
    positions[i * 3 + 2] = (Math.random() - 0.5) * props.spread * 0.6;
}

// Parallax halus mengikuti kursor.
const pointer = { x: 0, y: 0 };

const onPointerMove = (event) => {
    pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (event.clientY / window.innerHeight) * 2 - 1;
};

onMounted(() => window.addEventListener('pointermove', onPointerMove, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('pointermove', onPointerMove));

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed, delta }) => {
    const mesh = group.value;

    if (!mesh) {
        return;
    }

    mesh.rotation.y = elapsed * 0.02;
    mesh.rotation.x = Math.sin(elapsed * 0.1) * 0.05;

    // Lerp ke arah kursor supaya geraknya lembut, bukan menyentak.
    const damping = Math.min(1, delta * 1.5);
    mesh.position.x += (pointer.x * 0.6 - mesh.position.x) * damping;
    mesh.position.y += (-pointer.y * 0.4 - mesh.position.y) * damping;
});
</script>

<template>
    <TresPoints ref="group">
        <TresBufferGeometry :position="[positions, 3]" />
        <TresPointsMaterial :size="0.055" :color="props.color" :transparent="true" :opacity="0.75"
            :size-attenuation="true" :depth-write="false" />
    </TresPoints>
</template>
