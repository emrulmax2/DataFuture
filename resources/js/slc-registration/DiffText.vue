<template><span><template v-for="(g, n) in segs" :key="n"><span v-if="g.bad" class="diff">{{ g.v }}</span><template v-else>{{ g.v }}</template></template></span></template>

<script>
/*
 * A Data check value with the part that doesn't match the other side in the error colour
 * (user, 5 Nov 2026: names, dates and addresses). "words" compares word by word, ignoring case
 * and punctuation; "date" compares the day, month and year of a leading dd/mm/yyyy; "whole" colours
 * the whole value (amounts, Bulk attendance 6 Nov 2026). When the words
 * are the same but in another order, the whole value is coloured.
 */
const norm = (t) => String(t).toLowerCase().replace(/[^\p{L}\p{N}]+/gu, "");
const DATE = /^(\d{1,2})\/(\d{1,2})\/(\d{4})/;

export default {
    props: {
        text: { type: [String, Number], default: "" },
        other: { type: [String, Number], default: "" },
        kind: { type: String, default: "words" },
        active: { type: Boolean, default: true },
    },
    computed: {
        segs() {
            const t = String(this.text ?? "");
            const o = String(this.other ?? "");
            if (!this.active || norm(t) === norm(o)) return [{ v: t, bad: false }];
            if (this.kind === "whole") return [{ v: t, bad: norm(t) !== "" }]; // an amount: all of it, not the differing digits
            if (this.kind === "date") {
                const a = t.match(DATE);
                const b = o.match(DATE);
                if (!a || !b) return [{ v: t, bad: norm(t) !== "" }];
                const bad = [1, 2, 3].map((k) => Number(a[k]) !== Number(b[k]));
                return [{ v: a[1], bad: bad[0] }, { v: "/", bad: false }, { v: a[2], bad: bad[1] }, { v: "/", bad: false }, { v: a[3], bad: bad[2] }, { v: t.slice(a[0].length), bad: false }];
            }
            const pool = {};
            o.split(/[\s,\-/]+/).map(norm).filter(Boolean).forEach((w) => { pool[w] = (pool[w] || 0) + 1; });
            const segs = t.split(/([\s,\-/]+)/).map((v, n) => {
                const w = n % 2 ? "" : norm(v);
                if (!w) return { v, bad: false };
                if (pool[w]) { pool[w]--; return { v, bad: false }; }
                return { v, bad: true };
            });
            // the same words in another order: nothing stands out on either side, so the whole value is coloured
            // (when the other side only has extra words, this side has nothing that differs)
            if (!segs.some((g) => g.bad) && !Object.values(pool).some((n) => n > 0)) segs.forEach((g, n) => { if (n % 2 === 0 && norm(g.v)) g.bad = true; });
            return segs;
        },
    },
};
</script>
