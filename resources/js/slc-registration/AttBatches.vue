<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 7</span><h1>Attendance batches</h1>
            <p>Confirmed codes collect in the batch awaiting download, one per term. Download it as the SIS attendance import file, upload it in SIS, then mark it uploaded.</p></div>
    </div>
    <section v-if="!awaiting.length" class="card">
        <div class="card-h"><h2>Awaiting download</h2></div>
        <div class="empty">No codes waiting. Confirm codes on step 5 to start a batch.</div>
    </section>
    <section v-for="aw in awaiting" :key="aw.id" class="card">
        <div class="card-h"><h2>Awaiting download · {{ aw.id }} <span class="muted small" style="font-weight: 400">· {{ termName(aw.term) }}</span></h2>
            <button type="button" class="btn btn-primary" :disabled="store.busy" @click="download(aw)"><Ico name="down" />Download SIS file ({{ aw.items.length }})</button>
        </div>
        <div class="card-b" style="padding-bottom: 0"><ul class="chips"><li v-for="c in codeCounts(aw)" :key="c.code" class="pill muted"><span class="mono">{{ c.code }}</span> × {{ c.count }}</li></ul></div>
        <div class="tablebox"><table class="tbl" style="min-width: 720px">
            <thead><tr><th scope="col">Student</th><th scope="col">SSN</th><th scope="col">Code</th><th scope="col">Confirmed by</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="it in aw.items" :key="it.rowId">
                <th scope="row"><a v-if="it.url" class="plain" :href="it.url" target="_blank" rel="noopener">{{ it.name }}</a><template v-else>{{ it.name }}</template><div class="muted small" style="font-weight: 400">{{ it.sid }}</div></th>
                <td class="mono">{{ it.ssn }}</td>
                <td><span :class="'pill ' + tone(it.code)"><span class="mono">{{ it.code }}</span> {{ meaning(it.code) }}</span></td>
                <td class="muted">{{ it.by }} · {{ it.at }}</td>
                <td><button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.undo', { row_id: it.rowId })">Remove</button></td>
            </tr></tbody>
        </table></div>
    </section>

    <section class="card">
        <div class="card-h"><h2>All batches</h2></div>
        <div v-if="!batches.length" class="empty">No batches yet.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 820px">
            <thead><tr><th scope="col">Batch</th><th scope="col">Term</th><th scope="col">Students</th><th scope="col">Status</th><th scope="col">Downloaded</th><th scope="col">Uploaded to SIS</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="b in [...batches].reverse()" :key="b.id">
                <th scope="row" class="mono">{{ b.id }}</th><td>{{ termName(b.term) }}</td><td class="num">{{ b.items.length }}</td>
                <td><span :class="'pill ' + (b.status === 'awaiting' ? 'warn' : b.status === 'downloaded' ? 'info' : 'ok')">{{ b.status === 'awaiting' ? 'Awaiting download' : b.status === 'downloaded' ? 'Downloaded' : 'Uploaded to SIS' }}</span></td>
                <td class="muted">{{ b.downloadedBy ? b.downloadedBy + ' · ' + b.downloadedAt : '–' }}</td>
                <td class="muted">{{ b.uploadedBy ? b.uploadedBy + ' · ' + b.uploadedAt : '–' }}</td>
                <td><div class="acts">
                    <button type="button" class="btn btn-sm" @click="openStudents(b.id)">View students</button>
                    <button v-if="b.file" type="button" class="btn btn-sm" @click="viewId = b.id">View file</button>
                    <button v-if="b.status === 'downloaded'" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.att.batch.uploaded', { code: b.id })">Mark uploaded</button>
                </div></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="sb" ref="panel" class="card" aria-labelledby="ab-h">
        <div class="card-h"><div><h2 id="ab-h">Students in {{ sb.id }}</h2><div class="muted small">{{ termName(sb.term) }} · {{ statusLine(sb) }}</div></div>
            <button type="button" class="btn btn-sm" @click="studentsOf = null">Close</button></div>
        <div class="tablebox"><table class="tbl" style="min-width: 760px">
            <thead><tr><th scope="col">Student</th><th scope="col">SSN</th><th scope="col">Code</th><th scope="col">Confirmed by</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="it in sb.items" :key="it.rowId">
                <th scope="row"><a v-if="it.url" class="plain" :href="it.url" target="_blank" rel="noopener">{{ it.name }}</a><template v-else>{{ it.name }}</template><div class="muted small" style="font-weight: 400">{{ it.sid }}</div></th>
                <td class="mono">{{ it.ssn }}</td>
                <td><span :class="'pill ' + tone(it.code)"><span class="mono">{{ it.code }}</span> {{ meaning(it.code) }}</span></td>
                <td class="muted">{{ it.by }} · {{ it.at }}</td>
                <td><a v-if="it.slcUrl" class="btn-link small" :href="it.slcUrl" target="_blank" rel="noopener">SLC tab ↗</a></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="shown && shown.file" class="card">
        <div class="card-h"><h2 class="mono">{{ shown.file.name }}</h2>
            <div class="row">
                <button type="button" class="btn btn-sm" :disabled="store.busy" @click="download(shown)"><Ico name="down" />Download again</button>
                <button type="button" class="btn btn-sm" :disabled="!texts[shown.id]" @click="copy(texts[shown.id])">{{ copied || 'Copy text' }}</button>
            </div>
        </div>
        <div class="card-b stack">
            <span v-if="msg" class="muted">{{ msg }}</span>
            <pre class="file" aria-label="File preview">{{ texts[shown.id] || 'Loading…' }}</pre>
            <div class="notice warn"><Ico name="info" /><span>Each row is the student’s line exactly as SIS sent it, with field 21 set to the confirmed code. The header is SIS’s header with the date set to the download day, the student count and the three amount totals recounted for these lines. Both are assumptions: check them against SLC’s attendance import specification before the first live upload.</span></div>
        </div>
    </section>
</template>

<script>
import { computed, inject, nextTick, reactive, ref, watch } from "vue";
import Ico from "./Ico.vue";

/*
 * Bulk attendance › step 7 (user, 2 Nov 2026): the batches of confirmed codes, downloaded as the SIS
 * attendance import file (AttendanceActions::download / fileText / markUploaded), as Bulk
 * registration's Batches.
 */
const TERMS = { AUT: "Autumn", SPR: "Spring", SUM: "Summer" };

export default {
    components: { Ico },
    setup() {
        const { store, post, get } = inject("slr");
        const att = computed(() => store.state.attendance);
        const batches = computed(() => att.value.batches || []);
        const awaiting = computed(() => batches.value.filter((b) => b.status === "awaiting"));
        const codes = computed(() => att.value.codes || []);
        const meaning = (c) => (codes.value.find((x) => x.code === c) || {}).meaning || "";
        const tone = (c) => (c === "A" ? "ok" : (codes.value.find((x) => x.code === c) || {}).coc ? "bad" : "warn");
        const termName = (t) => TERMS[t] || t || "";
        const codeCounts = (b) => {
            const n = {};
            b.items.forEach((it) => { n[it.code] = (n[it.code] || 0) + 1; });
            return Object.keys(n).sort().map((code) => ({ code, count: n[code] }));
        };
        const statusLine = (b) => (b.status === "uploaded" ? "Uploaded to SIS · " + b.uploadedBy + " " + b.uploadedAt : b.status === "downloaded" ? "Downloaded · " + b.downloadedBy + " " + b.downloadedAt + " · not yet marked uploaded" : "Awaiting download");

        const msg = ref("");
        const copied = ref("");
        const viewId = ref(null);
        const texts = reactive({});
        const shown = computed(() => batches.value.find((b) => b.id === viewId.value) || [...batches.value].reverse().find((b) => b.file));
        function save(file) {
            try {
                const blob = new Blob([file.text], { type: "text/plain;charset=utf-8" });
                const a = document.createElement("a");
                a.href = URL.createObjectURL(blob);
                a.download = file.name;
                document.body.appendChild(a);
                a.click();
                setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 0);
                msg.value = "Saved " + file.name + ".";
            } catch (e) {
                msg.value = "Download isn’t available here. Use Copy text instead.";
            }
        }
        async function download(b) {
            msg.value = "";
            const data = await post("slc.registration.att.batch.download", { code: b.id });
            if (data && data.file) { texts[b.id] = data.file.text; viewId.value = b.id; save(data.file); }
        }
        function copy(text) {
            try { navigator.clipboard.writeText(text).then(() => { copied.value = "Copied"; }, () => { copied.value = "Select the text below and copy it"; }); } catch (e) { copied.value = "Select the text below and copy it"; }
        }
        watch(shown, async (b) => {
            copied.value = "";
            if (b && b.file && !texts[b.id]) {
                const data = await get("slc.registration.att.batch.file", { code: b.id });
                if (data) texts[b.id] = data.text;
            }
        }, { immediate: true });

        const studentsOf = ref(null);
        const panel = ref(null);
        const sb = computed(() => batches.value.find((b) => b.id === studentsOf.value) || null);
        async function openStudents(code) {
            studentsOf.value = code;
            await nextTick();
            if (panel.value) panel.value.scrollIntoView({ behavior: "smooth", block: "start" });
        }

        return { store, post, batches, awaiting, meaning, tone, termName, codeCounts, statusLine, msg, copied, viewId, texts, shown, download, copy, studentsOf, panel, sb, openStudents };
    },
};
</script>
