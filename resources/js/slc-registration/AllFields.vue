<template>
    <div class="tablebox allf-box"><table class="tbl" style="min-width: 560px">
        <thead><tr><th scope="col">Field</th><th scope="col">SLC</th><th scope="col">Ours</th><th scope="col">Result</th></tr></thead>
        <tbody><tr v-for="c in checks" :key="c.key">
            <th scope="row">{{ c.label }}</th>
            <td><DiffText :text="c.slc" :other="c.ours" :kind="DIFF[c.key]" :active="!c.ok && !!DIFF[c.key]" /></td>
            <td><DiffText :text="c.ours" :other="c.slc" :kind="DIFF[c.key]" :active="!c.ok && !!DIFF[c.key]" /></td>
            <td>
                <span :class="'pill ' + (c.ok ? 'ok' : 'bad')">{{ c.ok ? '✓ Match' : '✗ Error' }}</span>
                <div v-if="!c.ok && c.route" class="muted small allf-route">{{ ROUTES[c.route].label }}{{ c.remembered ? ' · remembered' : '' }}</div>
            </td>
        </tr></tbody>
    </table></div>
</template>

<script>
import { DIFF, ROUTES } from "./helpers";
import DiffText from "./DiffText.vue";

/*
 * Bulk registration: every field of a line, SLC against ours, with the result and (for a difference)
 * what was decided — the Data check card's "View all fields" and Pre-confirm's (user, 5 Nov 2026).
 */
export default {
    components: { DiffText },
    props: { checks: { type: Array, required: true } },
    setup() {
        return { DIFF, ROUTES };
    },
};
</script>
