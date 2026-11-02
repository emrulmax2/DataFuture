<template>
    <div v-if="ch.route" class="acts">
        <span :class="'pill ' + ROUTES[ch.route].tone">{{ ROUTES[ch.route].label }}{{ ch.route === 'coc' ? ' · ' + ATT_COC_TYPE[ch.key] : '' }}</span>
        <button v-if="ch.route === 'contact'" type="button" class="btn-link small" @click="copy(contactMessage(ch.key, ch, i.s))">{{ copied ? 'Copied' : 'Copy message' }}</button>
        <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.route.clear', { row_id: i.id, key: ch.key })">Change</button>
    </div>
    <div v-else class="routes" role="group" :aria-label="'Action for ' + ch.label + (withName ? ' – ' + i.s.first + ' ' + i.s.last : '')">
        <button v-for="k in ATT_FIELD_ROUTES[ch.key]" :key="k" type="button" :class="'route' + (ch.sugg === k ? ' sugg' : '')" :disabled="store.busy" @click="post('slc.registration.att.route', { row_id: i.id, key: ch.key, route: k })">
            {{ routeLabel(ch.key, k) }}<span v-if="ch.sugg === k" class="tag">Suggested</span>
        </button>
        <a v-if="ch.key === 'registration'" class="route" :href="i.s.slcUrl" target="_blank" rel="noopener">Open SLC tab ↗</a>
    </div>
</template>

<script>
import { inject, ref } from "vue";
import { ATT_COC_TYPE, ATT_FIELD_ROUTES, ROUTES, contactMessage } from "./helpers";

/*
 * Bulk attendance › Data check: what happens to one difference — the decision taken (with Change) or
 * the choices (AttendanceActions::setRoute / clearRoute). Used when grouped by student and by issue.
 */
export default {
    props: { i: { type: Object, required: true }, ch: { type: Object, required: true }, withName: { type: Boolean, default: false } },
    setup() {
        const { store, post } = inject("slr");
        const routeLabel = (key, k) => (k === "update" && key === "dob" ? "ID checked – update ours" : k === "accept" && key === "registration" ? "No action for now" : ROUTES[k].label);
        const copied = ref(false);
        const copy = (text) => { try { navigator.clipboard.writeText(text).then(() => { copied.value = true; }, () => { copied.value = false; }); } catch (e) { copied.value = false; } };

        return { store, post, routeLabel, copied, copy, contactMessage, ROUTES, ATT_FIELD_ROUTES, ATT_COC_TYPE };
    },
};
</script>
