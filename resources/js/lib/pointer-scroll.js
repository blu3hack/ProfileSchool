import { onBeforeUnmount, onMounted } from 'vue';

/**
 * State input global (kursor + scroll) yang dipakai bersama oleh scene 3D.
 *
 * Sengaja objek biasa, bukan ref: nilainya dibaca di dalam render-loop
 * Three.js tiap frame, jadi reaktivitas Vue justru mubazir di sini.
 */
const pointer = {
    /** -1 (kiri) … 1 (kanan) */
    x: 0,
    /** -1 (atas) … 1 (bawah) */
    y: 0,
};

const scroll = {
    /** Posisi scroll dalam piksel. */
    y: 0,
    /** 0 … 1 terhadap total tinggi dokumen. */
    progress: 0,
};

let subscribers = 0;

const onPointerMove = (event) => {
    pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (event.clientY / window.innerHeight) * 2 - 1;
};

const onScroll = () => {
    const max = document.documentElement.scrollHeight - window.innerHeight;

    scroll.y = window.scrollY;
    scroll.progress = max > 0 ? Math.min(1, window.scrollY / max) : 0;
};

/**
 * Pasang listener sekali saja, berapa pun komponen yang memakainya.
 * Listener dilepas otomatis ketika pemakai terakhir di-unmount.
 */
export function usePointerScroll() {
    onMounted(() => {
        subscribers += 1;

        if (subscribers === 1) {
            window.addEventListener('pointermove', onPointerMove, { passive: true });
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }
    });

    onBeforeUnmount(() => {
        subscribers -= 1;

        if (subscribers === 0) {
            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('scroll', onScroll);
        }
    });

    return { pointer, scroll };
}

export { pointer, scroll };
