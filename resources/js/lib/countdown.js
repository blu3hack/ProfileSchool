import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Hitung mundur menuju satu waktu tertentu (dipakai kartu & detail agenda).
 *
 * Satu detak dibagi ke semua pemakai: berapa pun jumlah kartu agenda di
 * halaman, hanya ada satu `setInterval` yang berjalan. Sisa waktu selalu
 * dihitung ulang dari `Date.now()` sehingga tidak menumpuk selisih ketika
 * tab sempat dijeda browser.
 */

/** Waktu bersama yang diperbarui tiap detik selama masih ada pemakainya. */
const now = ref(Date.now());

let timer = null;
let subscribers = 0;

const tick = () => (now.value = Date.now());

const start = () => {
    if (timer !== null) {
        return;
    }

    tick();
    timer = window.setInterval(tick, 1000);
};

const stop = () => {
    if (timer !== null) {
        window.clearInterval(timer);
        timer = null;
    }
};

/** Tab tersembunyi tidak perlu berdetak; saat kembali, langsung disamakan. */
const onVisibilityChange = () => {
    if (document.hidden) {
        stop();
    } else if (subscribers > 0) {
        start();
    }
};

const subscribe = () => {
    subscribers += 1;

    if (subscribers === 1) {
        document.addEventListener('visibilitychange', onVisibilityChange);
    }

    start();
};

const unsubscribe = () => {
    subscribers = Math.max(0, subscribers - 1);

    if (subscribers === 0) {
        document.removeEventListener('visibilitychange', onVisibilityChange);
        stop();
    }
};

const pad = (value) => String(value).padStart(2, '0');

/**
 * @param {string|(() => string)} target waktu tujuan (ISO-8601)
 * @param {{ endsAt?: string|(() => string) }} options waktu selesai acara —
 *        bila diisi, status `ongoing` menyala selama acara berlangsung.
 */
export function useCountdown(target, options = {}) {
    const read = (value) => {
        const raw = typeof value === 'function' ? value() : value;

        if (!raw) {
            return null;
        }

        const time = new Date(raw).getTime();

        return Number.isNaN(time) ? null : time;
    };

    const startTime = computed(() => read(target));
    const endTime = computed(() => read(options.endsAt));

    /** Sisa milidetik menuju waktu mulai; 0 bila sudah lewat/tidak valid. */
    const remaining = computed(() => {
        if (startTime.value === null) {
            return 0;
        }

        return Math.max(0, startTime.value - now.value);
    });

    const seconds = computed(() => Math.floor(remaining.value / 1000));

    const parts = computed(() => ({
        days: Math.floor(seconds.value / 86400),
        hours: Math.floor((seconds.value % 86400) / 3600),
        minutes: Math.floor((seconds.value % 3600) / 60),
        seconds: seconds.value % 60,
    }));

    /** Sudah mulai, tapi belum melewati waktu selesai. */
    const ongoing = computed(() => {
        if (startTime.value === null || remaining.value > 0) {
            return false;
        }

        const end = endTime.value ?? startTime.value + 86400000;

        return now.value <= end;
    });

    const finished = computed(() => remaining.value === 0 && !ongoing.value);

    /** Empat blok siap tampil: [{ label, value }]. */
    const units = computed(() => [
        { key: 'days', label: 'Hari', value: pad(parts.value.days) },
        { key: 'hours', label: 'Jam', value: pad(parts.value.hours) },
        { key: 'minutes', label: 'Menit', value: pad(parts.value.minutes) },
        { key: 'seconds', label: 'Detik', value: pad(parts.value.seconds) },
    ]);

    onMounted(subscribe);
    onBeforeUnmount(unsubscribe);

    return { parts, units, remaining, ongoing, finished };
}
