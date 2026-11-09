<template>
    <div class="card">
        <div class="card-h">
            <div><h2 style="font-size: 18px">{{ titleCase(r.title + ' ' + r.first + ' ' + r.last) }}</h2><div class="muted small">What SLC sent · SSN <span class="mono">{{ r.ssn }}</span> is not on any student record</div></div>
            <span :class="'pill ' + (i.cands.length ? 'info' : 'bad')">{{ i.cands.length ? plural(i.cands.length, 'possible student') : 'No likely student found' }}</span>
        </div>
        <div class="card-b"><SlcCard :r="r" /></div>
    </div>
    <article v-for="(c, idx) in i.cands" :key="c.s.sid" :class="'cand' + (idx === 0 && c.pct >= 85 ? ' top' : '')">
        <div class="row" style="justify-content: space-between; align-items: flex-start">
            <div class="row" style="flex-wrap: nowrap; min-width: 0">
                <span :class="'score ' + (c.pct >= 85 ? 'hi' : c.pct >= 60 ? 'mid' : 'lo')">{{ c.pct }}%</span>
                <div style="min-width: 0">
                    <div style="font-weight: 700; font-size: 16px"><a class="plain" :href="c.s.url" target="_blank" rel="noopener">{{ c.s.title }} {{ c.s.first }} {{ c.s.last }}</a><StatusTag :status="c.s.status" /></div>
                    <div class="muted small">{{ c.s.id }} · {{ c.s.course.name || 'No active course' }}{{ c.s.course.intake ? ', ' + c.s.course.intake : '' }} · DOB {{ fmtIso(c.s.dob) }}</div>
                    <div class="muted small">{{ c.s.address || '–' }} · {{ c.s.postcode || '–' }}</div>
                    <div class="small" style="font-weight: 600; margin-top: 2px">{{ c.pct >= 85 ? 'Strong match' : c.pct >= 60 ? 'Possible match' : 'Weak match' }}</div>
                </div>
            </div>
            <button type="button" :class="'btn ' + (idx === 0 && c.pct >= 85 ? 'btn-primary' : '')" @click="pending = c.s.sid">This is the student</button>
        </div>
        <ul class="chips">
            <li v-for="(ch, ci) in c.chips" :key="ch.label" :class="'pill ' + (ch.st === 'ok' ? 'ok' : ch.st === 'part' ? 'warn' : 'bad') + (ch.st !== 'ok' ? ' has-tip' : '')"
                :tabindex="ch.st !== 'ok' ? 0 : undefined" :aria-describedby="ch.st !== 'ok' ? 'tip-' + c.s.sid + '-' + ci : undefined">
                {{ ch.st === 'ok' ? '✓' : ch.st === 'part' ? '~' : '✗' }} {{ ch.label }}
                <!-- the values behind a partial or failed match, on hover / focus / tap -->
                <div v-if="ch.st !== 'ok'" :id="'tip-' + c.s.sid + '-' + ci" role="tooltip" :class="'tip' + (ci >= c.chips.length / 2 ? ' tip-end' : '')">
                    <div class="tip-h"><b>{{ ch.label }}</b><span :class="'pill ' + (ch.st === 'part' ? 'warn' : 'bad')">{{ ch.st === 'part' ? 'Partial match' : 'No match' }}</span></div>
                    <div class="tip-row tip-slc"><span class="tip-k">SLC gives us</span><span class="tip-v">{{ ch.slc || '–' }}</span></div>
                    <div class="tip-row tip-ours"><span class="tip-k">We hold</span><span class="tip-v">{{ ch.ours || '–' }}</span></div>
                    <div v-if="ch.note" class="tip-note">{{ ch.note }}</div>
                </div>
            </li>
        </ul>
        <div v-if="pending === c.s.sid" class="confirmrow">
            <span style="flex: 1 1 260px">Save SSN <b class="mono">{{ r.ssn }}</b> to <b>{{ c.s.id }} {{ c.s.first }} {{ c.s.last }}</b>? This updates the student record.</span>
            <button type="button" class="btn btn-primary btn-sm" :disabled="store.busy" @click="link(c.s.sid)">Save SSN</button>
            <button type="button" class="btn btn-sm" @click="pending = null">Cancel</button>
        </div>
    </article>
    <div class="card"><div class="card-b stack">
        <div class="stack" style="gap: 6px">
            <label class="lbl" for="allq">None of these? Search all students</label>
            <input id="allq" v-model="q" class="input" type="search" placeholder="Name, student ID, postcode or DOB (yyyy-mm-dd)">
        </div>
        <div v-for="s in results" :key="s.sid" class="stack" style="gap: 8px; padding-top: 8px; border-top: 1px solid var(--line-soft)">
            <div class="row" style="justify-content: space-between">
                <span><b><a class="plain" :href="s.url" target="_blank" rel="noopener">{{ s.title }} {{ s.first }} {{ s.last }}</a></b><StatusTag :status="s.status" /> <span class="muted">· {{ s.id }} · DOB {{ fmtIso(s.dob) }} · {{ s.postcode || '–' }}</span></span>
                <span v-if="s.ssn" class="pill muted">Has SSN <span class="mono">{{ s.ssn }}</span></span>
                <button v-else type="button" class="btn btn-sm" @click="pending = s.sid">This is the student</button>
            </div>
            <div v-if="pending === s.sid" class="confirmrow">
                <span style="flex: 1 1 260px">Save SSN <b class="mono">{{ r.ssn }}</b> to <b>{{ s.id }} {{ s.first }} {{ s.last }}</b>? This updates the student record.</span>
                <button type="button" class="btn btn-primary btn-sm" :disabled="store.busy" @click="link(s.sid)">Save SSN</button>
                <button type="button" class="btn btn-sm" @click="pending = null">Cancel</button>
            </div>
        </div>
        <div v-if="q.trim().length >= 2 && searched && results.length === 0" class="muted">No students match “{{ q }}”.</div>
        <div class="row" style="justify-content: flex-end; padding-top: 6px">
            <div class="row">
                <button type="button" class="btn" :disabled="store.busy" @click="post('slc.registration.decide', { row_id: i.id, d: 'hold', reason: 'student not identified' })">Hold – can’t identify</button>
                <button type="button" class="btn btn-bad" :disabled="store.busy" @click="post('slc.registration.decide', { row_id: i.id, d: 'notreg' })">Not registered</button>
            </div>
        </div>
    </div></div>
</template>

<script>
import { computed, inject, ref, watch } from "vue";
import { fmtIso, plural, titleCase } from "./helpers";
import SlcCard from "./SlcCard.vue";
import StatusTag from "./StatusTag.vue";

export default {
    components: { StatusTag, SlcCard },
    props: { i: { type: Object, required: true } },
    setup(props) {
        const { store, post, get } = inject("slr");
        const r = computed(() => props.i.r);
        const pending = ref(null);
        const q = ref("");
        const results = ref([]);
        const searched = ref(false);
        let timer = null;
        let seq = 0;

        watch(q, (v) => {
            clearTimeout(timer);
            searched.value = false;
            if (v.trim().length < 2) { results.value = []; return; }
            timer = setTimeout(async () => {
                const mine = ++seq;
                const data = await get("slc.registration.students", { q: v.trim() });
                if (mine !== seq) return;
                results.value = (data && data.students) || [];
                searched.value = true;
            }, 250);
        });

        async function link(studentId) {
            const data = await post("slc.registration.link", { row_id: props.i.id, student_id: studentId });
            if (data) pending.value = null;
        }

        return { store, post, r, pending, q, results, searched, link, fmtIso, plural, titleCase };
    },
};
</script>
