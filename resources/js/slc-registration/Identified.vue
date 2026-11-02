<template>
    <div class="card"><div class="card-h">
        <div><h2 style="font-size: 18px"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a></h2><div class="muted small">SSN <span class="mono">{{ i.r.ssn }}</span> saved to {{ i.s.id }} by {{ i.link.by }}</div></div>
        <div class="row">
            <button type="button" class="btn btn-sm" :disabled="!!i.dec || store.busy" @click="post('slc.registration.unlink', { row_id: i.id })">Unlink</button>
            <button type="button" class="btn btn-sm btn-primary" @click="go('data', i.id)">Open data check</button>
        </div>
    </div><div class="card-b"><SlcCard :r="i.r" /></div></div>
</template>

<script>
import { inject } from "vue";
import SlcCard from "./SlcCard.vue";

export default {
    components: { SlcCard },
    props: { i: { type: Object, required: true } },
    setup() {
        const { store, post, go } = inject("slr");
        return { store, post, go };
    },
};
</script>
