<script setup>
import { shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

import { usePointerScroll } from '../../../lib/pointer-scroll';

/**
 * Kabut partikel neon yang melayang di ruang. Berbeda dari rosette Opsi 2,
 * sebarannya acak dalam kotak 3D sehingga terasa seperti debu bintang —
 * bereaksi terhadap kursor (paralaks) dan scroll (dorongan ke kamera).
 */
const props = defineProps({
    count: { type: Number, default: 700 },
    /** Lebar, tinggi, kedalaman area sebaran. */
    spread: { type: Array, default: () => [26, 14, 18] },
    color: { type: String, default: '#34e2f5' },
    size: { type: Number, default: 0.06 },
    opacity: { type: Number, default: 0.8 },
    pointerStrength: { type: Number, default: 1 },
    /** Kecepatan naik partikel (unit/detik). */
    rise: { type: Number, default: 0.35 },
    spin: { type: Number, default: 0.02 },
});

const points = shallowRef(null);

const { pointer, scroll } = usePointerScroll();

const [spreadX, spreadY, spreadZ] = props.spread;

const positions = new Float32Array(props.count * 3);
/** Kecepatan naik per partikel — variasi kecil agar tidak seragam. */
const speeds = new Float32Array(props.count);

for (let i = 0; i < props.count; i += 1) {
    positions[i * 3] = (Math.random() - 0.5) * spreadX;
    positions[i * 3 + 1] = (Math.random() - 0.5) * spreadY;
    positions[i * 3 + 2] = (Math.random() - 0.5) * spreadZ;

    speeds[i] = 0.4 + Math.random() * 1.2;
}

const halfY = spreadY / 2;

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed, delta }) => {
    const mesh = points.value;

    if (!mesh) {
        return;
    }

    const attribute = mesh.geometry.attributes.position;

    for (let i = 0; i < props.count; i += 1) {
        const index = i * 3 + 1;

        attribute.array[index] += speeds[i] * props.rise * delta;

        // Partikel yang keluar dari batas atas lahir kembali di bawah.
        if (attribute.array[index] > halfY) {
            attribute.array[index] = -halfY;
        }
    }

    attribute.needsUpdate = true;

    mesh.rotation.y = elapsed * props.spin;

    // Paralaks kursor — di-lerp agar geraknya halus, bukan menyentak.
    const damping = Math.min(1, delta * 2);
    const targetX = pointer.x * 1.6 * props.pointerStrength;
    const targetY = -pointer.y * 0.9 * props.pointerStrength;

    mesh.position.x += (targetX - mesh.position.x) * damping;
    mesh.position.y += (targetY - mesh.position.y) * damping;

    // Scroll mendorong seluruh kabut mendekat ke kamera.
    mesh.position.z = scroll.progress * 8;
});
</script>

<template>
    <TresPoints ref="points">
        <TresBufferGeometry :position="[positions, 3]" />
        <TresPointsMaterial :size="props.size" :color="props.color" :transparent="true" :opacity="props.opacity"
            :size-attenuation="true" :depth-write="false" />
    </TresPoints>
</template>
