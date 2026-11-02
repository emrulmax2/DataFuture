<template>
    <div class="pagehead">
        <div><span class="eyebrow">Stage 1</span><h1>Identify students without an SSN</h1>
            <p>SLC sent an SSN that isn’t on any datafuture record. The system suggests the likely student from name, date of birth, gender, address and course. Check it, then save the SSN to their record.</p></div>
        <div class="filters" role="group" aria-label="Show">
            <button type="button" class="chip" :aria-pressed="show === 'open'" @click="show = 'open'">To identify <span class="num">{{ counts.identify }}</span></button>
            <button type="button" class="chip" :aria-pressed="show === 'done'" @click="show = 'done'">Identified <span class="num">{{ counts.linked }}</span></button>
        </div>
    </div>
    <div v-if="items.length === 0" class="card empty">
        {{ show === 'open' ? 'Every student in this upload is identified. Go to the data check.' : 'No students identified yet.' }}
        <div v-if="show === 'open'" style="margin-top: 10px"><button type="button" class="btn btn-primary" @click="go('data')">Open data check</button></div>
    </div>
    <div v-else class="split">
        <aside class="side card" aria-label="Students"><ul class="queue">
            <li v-for="i in items" :key="i.id"><button type="button" :aria-current="cur && cur.id === i.id ? 'true' : undefined" @click="store.selected = i.id">
                <span style="min-width: 0"><div class="q-name">{{ titleCase(i.r.first + ' ' + i.r.last) }}</div><div class="q-sub mono">{{ i.r.ssn }} · {{ fmtSlc(i.r.dob) }}</div></span>
                <span v-if="i.s" class="pill ok">{{ i.s.id }}</span>
                <span v-else :class="'pill ' + (i.cands.length ? 'info' : 'bad')">{{ i.cands.length ? i.cands[0].pct + '% best' : 'Not found' }}</span>
            </button></li>
        </ul></aside>
        <section class="main" aria-label="Student">
            <template v-if="cur">
                <Identified v-if="cur.s" :key="'i' + cur.id" :i="cur" />
                <FindStudent v-else :key="'f' + cur.id" :i="cur" />
            </template>
        </section>
    </div>
</template>

<script>
import { computed, inject, ref } from "vue";
import { fmtSlc, titleCase } from "./helpers";
import Identified from "./Identified.vue";
import FindStudent from "./FindStudent.vue";

export default {
    components: { Identified, FindStudent },
    setup() {
        const { store, list, go } = inject("slr");
        const show = ref("open");
        const counts = computed(() => store.state.counts);
        const items = computed(() => list.value.filter((i) => (show.value === "open" ? i.stage === "identify" : i.link && i.link.method === "linked")));
        const cur = computed(() => items.value.find((i) => i.id === store.selected) || items.value[0]);
        return { store, go, show, counts, items, cur, fmtSlc, titleCase };
    },
};
</script>
