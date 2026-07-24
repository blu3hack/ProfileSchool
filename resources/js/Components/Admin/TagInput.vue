<script setup>
import { ref } from 'vue';

/** Input daftar teks pendek: tag berita, poin pilar keunggulan, dll. */
const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Ketik lalu tekan Enter' },
});

const emit = defineEmits(['update:modelValue']);

const draft = ref('');

const add = () => {
    const value = draft.value.trim();

    if (!value || props.modelValue.includes(value)) {
        draft.value = '';

        return;
    }

    emit('update:modelValue', [...props.modelValue, value]);
    draft.value = '';
};

const remove = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, i) => i !== index));
};

/** Backspace pada input kosong menghapus tag terakhir. */
const onBackspace = () => {
    if (!draft.value && props.modelValue.length) {
        remove(props.modelValue.length - 1);
    }
};
</script>

<template>
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 focus-within:border-teal-500 focus-within:ring-2 focus-within:ring-teal-100">
        <span v-for="(tag, index) in props.modelValue" :key="`${tag}-${index}`"
            class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
            {{ tag }}
            <button type="button" class="text-teal-500 transition hover:text-rose-500" :aria-label="`Hapus ${tag}`"
                @click="remove(index)">
                ✕
            </button>
        </span>

        <input v-model="draft" type="text" :placeholder="props.placeholder"
            class="min-w-[10rem] flex-1 border-0 bg-transparent py-1 text-sm outline-none placeholder:text-slate-400"
            @keydown.enter.prevent="add" @keydown.,.prevent="add" @keydown.backspace="onBackspace" @blur="add">
    </div>
</template>
