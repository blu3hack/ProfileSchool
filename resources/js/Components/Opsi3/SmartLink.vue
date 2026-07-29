<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import { linkKind } from '../../lib/links';
import { goToSection } from '../../lib/navigate';

/**
 * Tautan yang memilih sendiri bentuknya sesuai target.
 *
 * Alamat menu tambahan diketik admin, jadi komponen pemakainya tidak bisa tahu
 * lebih dulu apakah itu section beranda, halaman situs ini, atau alamat luar.
 * Di sini keputusannya dibuat sekali; pemakainya cukup menulis <SmartLink>
 * dengan kelas sesuai tempatnya.
 *
 * Kelas & atribut lain diteruskan otomatis ke elemen akar (attribute
 * fallthrough), jadi tampilannya sepenuhnya milik pemakai.
 */
const props = defineProps({
    href: { type: String, default: '#' },
});

/** Dipancarkan tiap tautan diklik — dipakai navbar untuk menutup menu. */
const emit = defineEmits(['navigate']);

const kind = computed(() => linkKind(props.href));

/**
 * Lenis yang memegang kendali gulir, jadi anchor bawaan browser dilewati.
 * Bila section-nya tidak ada di halaman ini, helper pindah ke beranda dulu.
 */
const onSection = (event) => {
    event.preventDefault();
    emit('navigate');

    // `#` telanjang bukan selector yang sah — `querySelector('#')` melempar
    // error. Bisa saja tersimpan dari form admin, jadi ditolak di sini.
    if (props.href.trim().length < 2) {
        return;
    }

    goToSection(props.href);
};
</script>

<template>
    <Link v-if="kind === 'internal'" :href="props.href" @click="emit('navigate')">
        <slot />
    </Link>

    <a v-else-if="kind === 'section'" :href="props.href" @click="onSection">
        <slot />
    </a>

    <!-- Alamat luar dibuka di tab baru; mailto:/tel: tetap di tab yang sama
         supaya perangkat bisa mengambil alih tanpa meninggalkan halaman. -->
    <a v-else :href="props.href" :target="kind === 'external' ? '_blank' : null"
        :rel="kind === 'external' ? 'noopener noreferrer' : null" @click="emit('navigate')">
        <slot />
    </a>
</template>
