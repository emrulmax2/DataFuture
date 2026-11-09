<template>
    <div v-if="list.length" class="hist-list">
        <div v-for="(h, k) in list" :key="k" class="hist" :title="'Decided on ' + h.file">
            <span :class="'pill ' + tone(h)">{{ tag(h) }}</span>
            <span><b>{{ h.label }}</b> – {{ historyLine(h) }}{{ h.byName ? ' · ' + h.byName : '' }}{{ h.when ? ' ' + h.when : '' }}{{ h.then ? ' · SLC then sent ' + h.then : '' }}{{ nowText(h) }}</span>
        </div>
    </div>
</template>

<script>
import { computed } from "vue";
import { historyLine } from "./helpers";

// still to decide on the new line
const OPEN = ["changed", "ours"];

/*
 * Decisions from an earlier upload that no longer apply as they were: SLC's value now matches
 * (Resolved), it changed (Earlier — decide again), we now hold another value under a No action
 * (Earlier — decide again), or a "student not identified" hold ended (Released). Written by
 * App\Services\SlcRegistration\CarryOver; nothing is dropped.
 */
export default {
    props: {
        entries: { type: Array, default: () => [] },
        field: { type: String, default: null },    // only this field's entries
        resolved: { type: Boolean, default: false }, // only what no longer needs deciding
    },
    setup(props) {
        const list = computed(() => props.entries.filter((h) => (props.field === null || h.field === props.field) && (!props.resolved || !OPEN.includes(h.now))));
        const tag = (h) => ({ matches: "Resolved", released: "Released", changed: "Earlier" }[h.now] || "Earlier");
        const tone = (h) => (h.now === "matches" || h.now === "released" ? "ok" : OPEN.includes(h.now) ? "warn" : "muted");
        const nowText = (h) => (h.now === "matches" ? " · now matches" : h.now === "changed" ? " · SLC now sends " + h.nowValue : h.now === "ours" ? " · we now hold " + h.nowValue : h.now === "released" ? " · " + h.nowValue : "");
        return { list, tag, tone, nowText, historyLine };
    },
};
</script>
