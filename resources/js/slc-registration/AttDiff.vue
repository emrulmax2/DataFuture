<template><span><DiffText :text="head(ch[side])" :other="head(ch[side === 'slc' ? 'ours' : 'slc'])" :kind="ATT_DIFF[ch.key]" :active="!ch.ok && !!ATT_DIFF[ch.key]" />{{ tail(ch[side]) }}</span></template>

<script>
import { ATT_DIFF } from "./helpers";
import DiffText from "./DiffText.vue";

/*
 * Bulk attendance › Data check: one side of a check with what doesn't match the other side in the
 * error colour (user, 6 Nov 2026, as on Bulk registration). Only the compared part is coloured: the
 * course code (not the course name after " · ", which SLC truncates), and the year / amount before a
 * note in brackets ("£6,350.00 (agreement)").
 */
const SPLIT = { course: " · ", year: " (", fee: " (", tfl: " (" };

export default {
    components: { DiffText },
    props: { ch: { type: Object, required: true }, side: { type: String, required: true } },
    setup(props) {
        const cut = (v) => {
            const t = String(v ?? "");
            const sep = SPLIT[props.ch.key];
            const at = sep ? t.indexOf(sep) : -1;
            return at > 0 ? [t.slice(0, at), t.slice(at)] : [t, ""];
        };
        return { ATT_DIFF, head: (v) => cut(v)[0], tail: (v) => cut(v)[1] };
    },
};
</script>
