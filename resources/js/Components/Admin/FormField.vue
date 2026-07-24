<script setup>
import ImagePicker from './ImagePicker.vue';
import TagInput from './TagInput.vue';

/**
 * Satu field form yang bentuknya ditentukan `field.type`.
 * Dipakai halaman koleksi generik & editor konten halaman.
 */
const props = defineProps({
    field: { type: Object, required: true },
    modelValue: { type: [String, Number, Array, Boolean, null], default: '' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const update = (value) => emit('update:modelValue', value);

const inputClass =
    'w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100';
</script>

<template>
    <div>
        <!-- Gambar punya label sendiri di dalam komponennya -->
        <ImagePicker v-if="props.field.type === 'image'" :model-value="props.modelValue ?? ''"
            :label="props.field.label" :hint="props.field.hint ?? ''" :preview-url="props.field.preview ?? ''"
            @update:model-value="update" />

        <template v-else>
            <label class="block text-sm font-semibold text-slate-700">{{ props.field.label }}</label>
            <p v-if="props.field.hint" class="mt-0.5 text-xs text-slate-500">{{ props.field.hint }}</p>

            <textarea v-if="props.field.type === 'textarea'" :value="props.modelValue" rows="4"
                :class="[inputClass, 'mt-2 resize-y']" @input="update($event.target.value)"></textarea>

            <select v-else-if="props.field.type === 'select'" :value="props.modelValue" :class="[inputClass, 'mt-2']"
                @change="update($event.target.value)">
                <option v-for="option in props.field.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>

            <TagInput v-else-if="props.field.type === 'tags'" :model-value="props.modelValue ?? []" class="mt-2"
                @update:model-value="update" />

            <label v-else-if="props.field.type === 'boolean'" class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" :checked="!!props.modelValue" class="h-4 w-4 rounded border-slate-300 text-teal-600"
                    @change="update($event.target.checked)">
                Aktif
            </label>

            <input v-else :value="props.modelValue" :type="props.field.type === 'number' ? 'number' : 'text'"
                :class="[inputClass, 'mt-2']" @input="update($event.target.value)">
        </template>

        <p v-if="props.error" class="mt-1 text-xs font-semibold text-rose-600">{{ props.error }}</p>
    </div>
</template>
