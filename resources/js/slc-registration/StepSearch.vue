<template>
    <div class="step-search">
        <label class="step-search__box" :for="id">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span class="sr-only">Search students on this step</span>
            <input :id="id" type="search" :value="modelValue" placeholder="Search name, email or registration no." autocomplete="off" @input="$emit('update:modelValue', $event.target.value)" @keydown.esc="$emit('update:modelValue', '')">
        </label>
        <span v-if="modelValue.trim()" class="muted small" aria-live="polite">{{ shown }} of {{ total }} {{ total === 1 ? 'line' : 'lines' }}</span>
    </div>
</template>

<script>
/* Bulk registration steps 1–4 › a search box for that step's list only (user, 13 Nov 2026). */
export default {
    props: {
        modelValue: { type: String, default: "" },
        id: { type: String, required: true },
        shown: { type: Number, default: 0 },
        total: { type: Number, default: 0 },
    },
    emits: ["update:modelValue"],
};
</script>
