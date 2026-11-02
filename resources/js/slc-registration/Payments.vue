<template>
    <!-- Overview: step 1 (upload) and where things stand -->
    <template v-if="view === 'overview'">
        <div class="pagehead">
            <div><span class="eyebrow">Bulk payment upload</span><h1>SLC payment upload</h1>
                <p>Upload the remittance file SLC sends with each payment run. Each payment is matched to the student, their SLC agreement for the year and the instalment it pays; the payments you tick become money receipts on the student’s Accounts tab.</p></div>
        </div>

        <div v-if="pay.legacy.waiting" class="notice warn"><Ico name="info" />
            <span style="flex: 1">{{ plural(pay.legacy.waiting, 'payment') }} ({{ money(pay.legacy.total) }}) from earlier uploads {{ pay.legacy.waiting === 1 ? 'is' : 'are' }} still waiting in Reports › Accounts › Payments Upload. Bring {{ pay.legacy.waiting === 1 ? 'it' : 'them' }} in to sort and record here, as if the file had just been uploaded.</span>
            <div class="row" style="gap: 8px">
                <button type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="bringIn">Bring into this tab</button>
                <a class="btn btn-sm" :href="pay.legacy.url" target="_blank" rel="noopener">Open in Reports ↗</a>
            </div>
        </div>

        <section class="card" aria-labelledby="pay-up">
            <div class="card-h"><h2 id="pay-up">Step 1 · Upload the remittance file</h2><span class="muted small">The .dat SLC sends, or a .csv / .txt saved from it</span></div>
            <div class="card-b stack">
                <div :class="'drop' + (over ? ' over' : '')" @dragover.prevent="over = true" @dragleave="over = false" @drop.prevent="onDrop">
                    <div class="stack" style="gap: 4px">
                        <strong>Drop the remittance file here, or choose a file</strong>
                        <span class="muted small">Nothing is recorded on upload: you check the payments first. A payment already on a student’s account shows as already recorded.</span>
                    </div>
                    <div class="row">
                        <label class="btn btn-primary" for="pay-file" :aria-disabled="store.busy"><Ico name="up" />{{ store.busy && uploading ? 'Uploading…' : 'Choose file' }}</label>
                        <input id="pay-file" type="file" accept=".dat,.csv,.txt,text/plain,text/csv" hidden @change="onPick">
                    </div>
                </div>
                <div v-if="up" class="row small" style="gap: 6px 18px">
                    <span><span class="muted">File</span> {{ up.fileName }}</span>
                    <span><span class="muted">From</span> <span class="mono">{{ up.hei }}</span> · {{ up.heiName }}</span>
                    <span><span class="muted">Remittance date</span> <b>{{ fmtIso(up.date) }}</b></span>
                    <span><span class="muted">Payments</span> <b class="num">{{ up.lineCount }}</b> · <b class="num">{{ money(up.lineTotal) }}</b></span>
                    <span v-if="up.totalsOk" class="pill ok">✓ Matches the header</span>
                    <span v-else class="pill bad" :title="'Header: ' + up.headerCount + ' payments, ' + money(up.headerTotal)">Header says {{ up.headerCount }} · {{ money(up.headerTotal) }}</span>
                    <span><span class="muted">Uploaded</span> {{ up.at }} by {{ up.by }}</span>
                    <span v-if="up.fromReports" class="pill muted">Brought in from Reports</span>
                    <template v-if="!up.newest"><span class="pill warn">Earlier upload</span><button type="button" class="btn-link small" :disabled="store.busy" @click="openUpload(pay.uploads[0].id)">Open the newest</button></template>
                    <span v-if="up.errors.length" class="pill bad" :title="up.errors.join('\n')">{{ plural(up.errors.length, 'line') }} skipped</span>
                </div>
            </div>
        </section>

        <ol v-if="up" class="pipeline" aria-label="Where payments are">
            <li><button type="button" class="stage t-ok" @click="$emit('go', 'check', 'all')"><span class="s-n">STEP 1</span><span class="s-l">Uploaded</span><span class="s-v">{{ c.all }}</span><span class="s-m">{{ money(c.sum.all) }} · remittance {{ fmtIso(up.date) }}{{ c.refunds ? ' · ' + plural(c.refunds, 'refund') : '' }}</span></button></li>
            <li><button type="button" :class="'stage ' + (c.all - c.matched ? 't-warn' : 't-ok')" @click="$emit('go', 'check', c.all - c.matched ? 'error' : 'all')"><span class="s-n">STEP 2</span><span class="s-l">Matched to a student</span><span class="s-v">{{ c.matched }}</span><span class="s-m">{{ c.all - c.matched }} SSN{{ c.all - c.matched === 1 ? '' : 's' }} not on any student</span></button></li>
            <li><button type="button" :class="'stage ' + (c.error ? 't-bad' : c.ready + c.look ? 't-info' : 't-ok')" @click="$emit('go', 'check', c.ready ? 'ready' : c.look ? 'look' : 'error')"><span class="s-n">STEP 3</span><span class="s-l">To record</span><span class="s-v">{{ c.ready + c.look }}</span><span class="s-m">{{ c.ready }} ready · {{ c.look }} need a look · {{ c.error }} can’t be recorded yet</span></button></li>
            <li><button type="button" class="stage t-ok" @click="$emit('go', 'check', 'recorded')"><span class="s-n">STEP 4</span><span class="s-l">Recorded</span><span class="s-v">{{ c.recorded + c.done }}</span><span class="s-m">{{ c.recorded }} here ({{ money(c.sum.recorded) }}) · {{ c.done }} already on the account · {{ c.skipped }} skipped</span></button></li>
        </ol>

        <section class="card"><div class="card-h"><h2>How it works</h2></div>
            <ol class="plan"><li v-for="([t, d], k) in PAY_STEPS" :key="t"><span class="dot">{{ k + 1 }}</span><div style="flex: 1"><b>{{ t }}</b><div class="muted small">{{ d }}</div></div><span class="pill ok">Ready</span></li></ol>
        </section>
        <section class="card"><div class="card-h"><h2>What a receipt gets</h2><span class="muted small">As Reports › Accounts › Payments Upload records them</span></div>
            <div class="tablebox"><table class="tbl pay-rules"><tbody>
                <tr><th scope="row">Invoice no</th><td>The next number after the highest in use (a refund gets <span class="mono">R-</span> in front)</td></tr>
                <tr><th scope="row">Payment</th><td>Bank · dated the remittance date · entered today · “Course Fee”, or “Refund” for a negative amount</td></tr>
                <tr><th scope="row">Agreement</th><td>The student’s SLC agreement for the year of study, with the line’s course code; one you choose is saved as a forced entry</td></tr>
                <tr><th scope="row">Instalment</th><td>The agreement’s unpaid instalment with the same amount, else the one due nearest the remittance date; it then points at the receipt</td></tr>
                <tr><th scope="row">Remark</th><td><span class="mono">&lt;date time&gt; Bulk Upload</span></td></tr>
            </tbody></table></div>
        </section>
    </template>

    <PayCheck v-else-if="view === 'check'" :initial="show" @go="(v, s) => $emit('go', v, s)" />

    <!-- Uploads -->
    <template v-else>
        <div class="pagehead">
            <div><span class="eyebrow">Bulk payment upload</span><h1>Uploads</h1>
                <p>The remittance files uploaded on this tab, kept as they were sent, and every remittance recorded in bulk before — here or in Reports › Accounts › Payments Upload — by remittance date. The newest file is the one you work on in Check &amp; record; uploading a file again picks up where it was left.</p></div>
        </div>
        <section class="card">
            <div class="card-h"><h2>Files uploaded on this tab</h2><span class="muted small">Newest first · Open puts an upload in Check &amp; record (for you only)</span></div>
            <div v-if="!pay.uploads.length" class="empty">No remittance file uploaded on this tab yet.</div>
            <div v-else class="tablebox"><table class="tbl" style="min-width: 760px">
                <thead><tr><th scope="col">File</th><th scope="col">Remittance date</th><th scope="col">Payments</th><th scope="col">Recorded here</th><th scope="col">Uploaded</th><th scope="col"></th></tr></thead>
                <tbody><tr v-for="(u, k) in pay.uploads" :key="u.id">
                    <th scope="row">{{ u.fileName }} <span v-if="up && u.id === up.id" class="pill info">Open</span> <span v-if="k === 0" class="pill muted">Newest</span></th>
                    <td>{{ fmtIso(u.date) }}</td>
                    <td class="num">{{ u.lines }} · {{ money(u.total) }}</td>
                    <td class="num">{{ u.recorded }} · {{ money(u.recordedTotal) }}</td>
                    <td class="muted">{{ u.by }} · {{ u.at }}</td>
                    <td><div class="acts">
                        <button v-if="!up || u.id !== up.id" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="openUpload(u.id)">Open</button>
                        <button v-else type="button" class="btn btn-sm" @click="$emit('go', 'check', 'all')">Check &amp; record</button>
                        <a class="btn btn-sm" :href="u.fileUrl"><Ico name="down" />File</a>
                    </div></td>
                </tr></tbody>
            </table></div>
        </section>
        <PayHistory :legacy-url="pay.legacy.url" />
    </template>
</template>

<script>
import { computed, inject, ref } from "vue";
import { PAY_STEPS, fmtIso, money, plural } from "./helpers";
import Ico from "./Ico.vue";
import PayCheck from "./PayCheck.vue";
import PayHistory from "./PayHistory.vue";

/*
 * /slc-registration › Bulk payment upload (user, 2 Nov 2026): Reports › Accounts › Payments Upload
 * rebuilt here. Overview (upload, PaymentActions::import), Check & record (PayCheck.vue), Uploads.
 */
export default {
    components: { Ico, PayCheck, PayHistory },
    props: { view: { type: String, default: "overview" }, show: { type: String, default: "ready" } },
    emits: ["go"],
    setup(props, { emit }) {
        const { store, post } = inject("slr");
        const pay = computed(() => store.state.payments);
        const up = computed(() => pay.value.upload);
        const c = computed(() => pay.value.counts);
        const over = ref(false);
        const uploading = ref(false);
        async function readFile(f) {
            if (!f || store.busy) return;
            const fd = new FormData();
            fd.append("file", f);
            uploading.value = true;
            await post("slc.registration.pay.upload", fd);
            uploading.value = false;
        }
        const onDrop = (e) => { over.value = false; readFile(e.dataTransfer.files[0]); };
        const openUpload = async (id) => { const data = await post("slc.registration.pay.open", { id }); if (data && data.flash && data.flash.tone !== "bad") emit("go", "check", "all"); };
        const bringIn = async () => { const data = await post("slc.registration.pay.bring.in", {}); if (data && data.flash && data.flash.tone !== "bad") emit("go", "check", "all"); };
        const onPick = (e) => { readFile(e.target.files[0]); e.target.value = ""; };

        return { store, pay, up, c, over, uploading, onDrop, onPick, bringIn, openUpload, PAY_STEPS, fmtIso, money, plural };
    },
};
</script>
