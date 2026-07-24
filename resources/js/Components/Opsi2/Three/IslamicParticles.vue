<script setup>
import { shallowRef } from 'vue';
import { useLoop } from '@tresjs/core';

import { usePointerScroll } from '../../../lib/pointer-scroll';

const props = defineProps({
    /** Jumlah cincin konsentris pada rosette. */
    rings: { type: Number, default: 9 },
    /** Simetri putar — 8 mengikuti bintang delapan khas ornamen islami. */
    symmetry: { type: Number, default: 8 },
    /** Radius cincin terluar. */
    radius: { type: Number, default: 6 },
    color: { type: String, default: '#57c6a4' },
    size: { type: Number, default: 0.055 },
    opacity: { type: Number, default: 0.8 },
    /** Ketebalan sebaran pada sumbu Z. */
    depth: { type: Number, default: 2.4 },
    /** Rotasi awal pola (radian) — untuk menumpuk dua lapis rosette. */
    phase: { type: Number, default: 0 },
    /** Pengali reaksi terhadap kursor. */
    pointerStrength: { type: Number, default: 1 },
    spin: { type: Number, default: 0.05 },
});

const points = shallowRef(null);

const { pointer, scroll } = usePointerScroll();

/**
 * Susun titik-titik pada pola rosette: tiap cincin berisi kelipatan
 * `symmetry` titik, sehingga hasilnya simetris delapan penjuru seperti
 * ornamen geometri islami. Dihitung sekali saat setup — tidak reaktif.
 */
const buildRosette = () => {
    const perRing = [];
    let total = 0;

    for (let ring = 1; ring <= props.rings; ring += 1) {
        // Cincin makin luar makin padat agar kerapatan visual merata.
        const count = props.symmetry * ring * 2;
        perRing.push(count);
        total += count;
    }

    const positions = new Float32Array(total * 3);
    // Simpan radius & sudut awal untuk animasi "napas" tiap frame.
    const seeds = new Float32Array(total * 2);

    let index = 0;

    perRing.forEach((count, ringIndex) => {
        const ratio = (ringIndex + 1) / props.rings;
        const ringRadius = props.radius * ratio;

        for (let i = 0; i < count; i += 1) {
            const angle = props.phase + (i / count) * Math.PI * 2;

            // Modulasi radius mengikuti simetri → membentuk kelopak bintang.
            const petal = 1 + Math.cos(angle * props.symmetry) * 0.12;
            const r = ringRadius * petal;

            positions[index * 3] = Math.cos(angle) * r;
            positions[index * 3 + 1] = Math.sin(angle) * r;
            positions[index * 3 + 2] = (Math.random() - 0.5) * props.depth * ratio;

            seeds[index * 2] = r;
            seeds[index * 2 + 1] = angle;

            index += 1;
        }
    });

    return { positions, seeds, total };
};

const { positions, seeds, total } = buildRosette();

const { onBeforeRender } = useLoop();

onBeforeRender(({ elapsed, delta }) => {
    const mesh = points.value;

    if (!mesh) {
        return;
    }

    // Rotasi dasar + dorongan tambahan seiring scroll halaman.
    mesh.rotation.z = props.phase + elapsed * props.spin + scroll.progress * Math.PI * 1.2;

    // Kursor memiringkan bidang rosette — di-lerp supaya tidak menyentak.
    const damping = Math.min(1, delta * 2);
    const targetX = -pointer.y * 0.32 * props.pointerStrength;
    const targetY = pointer.x * 0.32 * props.pointerStrength;

    mesh.rotation.x += (targetX - mesh.rotation.x) * damping;
    mesh.rotation.y += (targetY - mesh.rotation.y) * damping;

    // Efek "napas": seluruh pola mengembang-mengempis pelan.
    const breath = 1 + Math.sin(elapsed * 0.45) * 0.03;
    mesh.scale.setScalar(breath);

    // Riak halus pada sumbu Z, dihitung dari radius tiap titik.
    const attribute = mesh.geometry.attributes.position;

    for (let i = 0; i < total; i += 1) {
        const r = seeds[i * 2];
        attribute.array[i * 3 + 2] += Math.sin(elapsed * 1.1 - r * 0.6) * delta * 0.12;
    }

    attribute.needsUpdate = true;
});
</script>

<template>
    <TresPoints ref="points">
        <TresBufferGeometry :position="[positions, 3]" />
        <TresPointsMaterial :size="props.size" :color="props.color" :transparent="true" :opacity="props.opacity"
            :size-attenuation="true" :depth-write="false" />
    </TresPoints>
</template>
