<template>
    <div v-if="stack" class="box-stack"><div v-for="g in segs" :key="g.k"><span v-if="g.pre" class="muted small box-pre">{{ g.pre }}</span><DiffText :class="g.k === 'course' ? 'mono' : undefined" :text="g.v" :other="g.o" :active="g.bad" /><span v-if="g.tail" class="box-tail">{{ g.tail }}</span></div></div>
    <span v-else class="nm-full"><DiffText v-for="g in segs" :key="g.k" :text="g.v" :other="g.o" :active="g.bad" /></span>
</template>

<script>
import DiffText from "./DiffText.vue";

/*
 * Data check › Name / Address / Course row: one side's whole value (title and name, address and
 * postcode on one line; course name, then intake (· our venue), then code), each differing part in the error colour.
 */
export default {
    components: { DiffText },
    props: {
        segs: { type: Array, required: true }, // [{ k, v, o (the other side's value), bad, pre (a label, e.g. "Code"), tail (plain text after) }]
        stack: { type: Boolean, default: false },
    },
};
</script>
