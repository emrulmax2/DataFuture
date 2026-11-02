<template>
    <div class="pagehead"><div><h1>Course mapping</h1><p>Links SLC course codes to LCC courses, intakes and the awarding body qualification. The code is the SLC code on the course creation’s venue (Course Creation). An unmapped code shows as a course code error.</p></div></div>
    <section v-if="courses.unmapped.length > 0" class="card"><div class="card-h"><h2>Codes in this upload with no mapping</h2><span class="pill bad">{{ courses.unmapped.length }}</span></div>
        <div class="tablebox"><table class="tbl" style="min-width: 640px"><thead><tr><th scope="col">SLC code</th><th scope="col">SLC course name</th><th scope="col">Map to</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="u in courses.unmapped" :key="u.code"><th scope="row" class="mono">{{ u.code }}</th><td>{{ u.name }}</td>
                <td><label class="sr" :for="'map-' + u.code">LCC course for {{ u.code }}</label>
                    <select :id="'map-' + u.code" v-model="pick[u.code]" class="input" style="min-width: 220px">
                        <option value="">Choose course…</option>
                        <option v-for="o in courses.options" :key="o.id" :value="o.id">{{ o.label }}{{ o.code ? ' (now ' + o.code + ')' : '' }}</option>
                    </select></td>
                <td><button type="button" class="btn btn-sm btn-primary" :disabled="!pick[u.code] || store.busy" @click="save(u.code)">Save</button></td></tr></tbody></table></div>
    </section>
    <section class="card"><div class="card-h"><h2>Mapped codes</h2><span v-if="store.state.import" class="muted small">In this upload</span></div>
        <div v-if="courses.mapped.length === 0" class="empty">{{ store.state.import ? 'None of the codes in this upload are mapped yet.' : 'Upload a file to see its course codes.' }}</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 820px"><thead><tr><th scope="col">SLC code</th><th scope="col">LCC course</th><th scope="col">Intake</th><th scope="col">Start</th><th scope="col">Course fee = TFL</th><th scope="col">Awarding body qualification</th></tr></thead>
            <tbody><tr v-for="(c, k) in courses.mapped" :key="c.code + k"><th scope="row" class="mono">{{ c.code }}</th><td>{{ c.course }}<div class="muted small">{{ c.venue }}</div></td><td class="mono">{{ c.intake }}</td><td>{{ fmtIso(c.start) }}</td><td class="num">{{ money(c.fee) }}</td><td>{{ c.ab || '–' }}</td></tr></tbody></table></div>
    </section>
</template>

<script>
import { computed, inject, reactive } from "vue";
import { fmtIso, money } from "./helpers";

export default {
    setup() {
        const { store, post } = inject("slr");
        const pick = reactive({});
        const courses = computed(() => store.state.courses);
        const save = async (code) => {
            const data = await post("slc.registration.course.map", { code, venue_id: pick[code] });
            if (data) delete pick[code];
        };
        return { store, courses, pick, save, fmtIso, money };
    },
};
</script>
