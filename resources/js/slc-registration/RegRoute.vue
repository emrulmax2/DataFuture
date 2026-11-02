<template>
    <div v-if="ch.route" class="acts">
        <span :class="'pill ' + ROUTES[ch.route].tone">{{ ROUTES[ch.route].label }}{{ ch.route === 'coc' ? ' · ' + COC_TYPE[ch.key] : '' }}</span>
        <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.route.clear', { row_id: i.id, key: ch.key })">Change</button>
        <span v-if="ch.carried" class="carried small">Carried over from an earlier upload{{ ch.state ? ' · ' + stateText(ch.state) : '' }}</span>
    </div>
    <div v-else class="routes" role="group" :aria-label="'Action for ' + ch.label + (withName ? ' – ' + i.s.first + ' ' + i.s.last : '')">
        <button v-for="k in FIELD_ROUTES[ch.key]" :key="k" type="button" :class="'route' + (ch.sugg === k ? ' sugg' : '')" :disabled="store.busy" @click="post('slc.registration.route', { row_id: i.id, key: ch.key, route: k })">
            {{ k === 'update' && ch.key === 'dob' ? 'ID checked – update ours' : ROUTES[k].label }}<span v-if="ch.sugg === k" class="tag">Suggested</span>
        </button>
    </div>
    <RowHistory :entries="i.history" :field="ch.key" />
</template>

<script>
import { inject } from "vue";
import { COC_TYPE, FIELD_ROUTES, ROUTES } from "./helpers";
import RowHistory from "./RowHistory.vue";

/*
 * Bulk registration › Data check: what happens to one error — the decision taken (with Change and
 * where it was carried from) or the choices (RegistrationActions::setRoute / clearRoute), and the
 * field's history. Used when grouped by student and by issue.
 */
export default {
    components: { RowHistory },
    props: { i: { type: Object, required: true }, ch: { type: Object, required: true }, withName: { type: Boolean, default: false } },
    setup() {
        const { store, post } = inject("slr");
        const stateText = (st) => ({ issued: "CoC submitted " + st.at, contacted: "contacted " + st.at, done: "updated with SLC " + st.at }[st.state] || "");

        return { store, post, stateText, ROUTES, FIELD_ROUTES, COC_TYPE };
    },
};
</script>
