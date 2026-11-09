<template>
    <div class="pagehead">
        <div><span class="eyebrow">Stage 5</span><h1>Batches</h1>
            <p>Students confirmed Yes collect in the batch awaiting download. Download it as the SIS import file, upload it in SIS, then mark it uploaded and record the SLC registration of the students SIS accepted (View students → Bulk SLC Registration).</p></div>
    </div>
    <section v-if="!awaitingList.length" class="card">
        <div class="card-h"><h2>Awaiting download</h2></div>
        <div class="empty">No confirmations waiting. Confirm students in pre-confirm to start a batch.</div>
    </section>
    <!-- one waiting batch per academic year (usually just one) -->
    <section v-for="aw in awaitingList" :key="aw.id" class="card">
        <div class="card-h"><h2>Awaiting download · {{ aw.id }}<span v-if="awaitingList.length > 1" class="muted small" style="font-weight: 400"> · {{ yearOf(aw) }}</span></h2>
            <button type="button" class="btn btn-primary" :disabled="store.busy" @click="downloadAwaiting(aw)"><Ico name="down" />Download SIS file ({{ aw.items.length }})</button>
        </div>
        <div class="tablebox"><table class="tbl" style="min-width: 640px">
            <thead><tr><th scope="col">Student</th><th scope="col">SSN</th><th scope="col">Confirmed by</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="it in aw.items" :key="it.rowId"><th scope="row">{{ it.name }}<StatusTag :status="it.status" /><div class="muted small" style="font-weight: 400">{{ it.sid }}</div></th><td class="mono">{{ it.r.ssn }}</td><td class="muted">{{ it.by }} · {{ it.at }}</td><td><button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.undo', { row_id: it.rowId })">Remove</button></td></tr></tbody>
        </table></div>
    </section>

    <section class="card">
        <div class="card-h"><h2>All batches</h2></div>
        <div v-if="batches.length === 0" class="empty">No batches yet.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 760px">
            <thead><tr><th scope="col">Batch</th><th scope="col">Students</th><th scope="col">Status</th><th scope="col">Downloaded</th><th scope="col">Uploaded to SIS</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="b in [...batches].reverse()" :key="b.id">
                <th scope="row" class="mono">{{ b.id }}</th><td class="num">{{ b.items.length }}</td>
                <td><span :class="'pill ' + (b.status === 'awaiting' ? 'warn' : b.status === 'downloaded' ? 'info' : 'ok')">{{ b.status === 'awaiting' ? 'Awaiting download' : b.status === 'downloaded' ? 'Downloaded' : 'Uploaded to SIS' }}</span></td>
                <td class="muted">{{ b.downloadedBy ? b.downloadedBy + ' · ' + b.downloadedAt : '–' }}</td>
                <td class="muted">{{ b.uploadedBy ? b.uploadedBy + ' · ' + b.uploadedAt : '–' }}</td>
                <td><div class="acts">
                    <button type="button" class="btn btn-sm" @click="openStudents(b.id)">View students</button>
                    <button v-if="b.file" type="button" class="btn btn-sm" @click="viewId = b.id">View file</button>
                    <button v-if="b.status === 'downloaded'" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="markUploaded(b.id)">Mark uploaded</button>
                </div></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="sb" ref="panel" class="card" aria-labelledby="bs-h">
        <div class="card-h">
            <div><h2 id="bs-h">Students in {{ sb.id }}</h2><div class="muted small">{{ statusLine(sb) }} · {{ recorded }} of {{ sb.items.length }} SLC registrations recorded</div></div>
            <div class="row">
                <template v-if="sb.status === 'uploaded' && picked.length">
                    <span class="muted small">{{ plural(picked.length, 'student') }} selected</span>
                    <button type="button" class="btn btn-primary" :disabled="store.busy" @click="bulkRegister(false)">Bulk SLC Registration ({{ picked.length }})</button>
                </template>
                <button type="button" class="btn btn-sm" @click="closeStudents">Close</button>
            </div>
        </div>
        <div v-if="sb.status !== 'uploaded'" class="card-b" style="padding-bottom: 0"><div class="notice">
            <Ico name="info" /><span>{{ sb.status === 'awaiting' ? 'Download the SIS file and upload it in SIS first.' : 'Once the file is in SIS, mark the batch uploaded.' }} Then tick the students SIS accepted and use Bulk SLC Registration to record their SLC registration.</span>
        </div></div>
        <div class="tablebox"><table class="tbl" style="min-width: 960px">
            <thead><tr>
                <th scope="col" style="width: 44px"><input class="pick" type="checkbox" :checked="allPicked" :indeterminate.prop="somePicked" :disabled="!canPick || !pickable.length" aria-label="Select all students not yet recorded" @change="toggleAll($event.target.checked)"></th>
                <th scope="col">Student</th><th scope="col">SSN</th><th scope="col">SLC course</th><th scope="col">Confirmed by</th><th scope="col">SLC registration &amp; agreement</th>
            </tr></thead>
            <tbody><tr v-for="it in sb.items" :key="it.rowId" :class="picked.includes(it.rowId) ? 'is-picked' : ''">
                <td><input v-model="picked" class="pick" type="checkbox" :value="it.rowId" :disabled="!canPick || !isPickable(it)" :aria-label="'Select ' + it.name"></td>
                <th scope="row"><a v-if="it.url" class="plain" :href="it.url" target="_blank" rel="noopener">{{ it.name }}</a><template v-else>{{ it.name }}</template><StatusTag :status="it.status" /><div class="muted small" style="font-weight: 400">{{ it.sid }}</div></th>
                <td class="mono">{{ it.r.ssn }}</td>
                <td>{{ it.course }}</td>
                <td class="muted">{{ it.by }} · {{ it.at }}</td>
                <td>
                    <span :class="'pill ' + recordLabel(it.record).tone">{{ recordLabel(it.record).text }}</span>
                    <div class="muted small" style="margin-top: 3px">{{ recordLabel(it.record).note }}</div>
                </td>
            </tr></tbody>
        </table></div>
    </section>

    <div v-if="modal" class="mx-back" @click.self="modal = null">
        <div class="mx-box" role="dialog" aria-modal="true" aria-labelledby="mx-h">
            <div class="mx-h"><h2 id="mx-h">{{ modal.existing.length === 1 ? '1 student already has' : modal.existing.length + ' students already have' }} SLC records for this year</h2></div>
            <div class="mx-b">
                <p>A registration or an agreement for the same year is already on {{ modal.existing.length === 1 ? 'this student’s' : 'these students’' }} SLC / Accounts tab. Tick the ones you give permission for: a new registration and a new agreement will be created with all the details, and the earlier ones are kept. Students left unticked are skipped and can be done later.</p>
                <div class="tablebox"><table class="tbl">
                    <thead><tr>
                        <th scope="col" style="width: 44px"><input class="pick" type="checkbox" :checked="allAllowed" :indeterminate.prop="allowed.length > 0 && !allAllowed" aria-label="Give permission for all" @change="allowed = $event.target.checked ? modal.existing.map((e) => e.rowId) : []"></th>
                        <th scope="col">Student</th><th scope="col">Year</th><th scope="col">Registration</th><th scope="col">Agreement</th>
                    </tr></thead>
                    <tbody><tr v-for="e in modal.existing" :key="e.rowId" :class="allowed.includes(e.rowId) ? 'is-picked' : ''">
                        <td><input v-model="allowed" class="pick" type="checkbox" :value="e.rowId" :aria-label="'Create new records for ' + e.name"></td>
                        <th scope="row">{{ e.name }}<StatusTag :status="e.status" /><div class="muted small" style="font-weight: 400">{{ e.sid }}</div></th>
                        <td>Year {{ e.year }}</td>
                        <td>{{ e.regDate ? fmtIso(e.regDate) : '–' }}<div v-if="e.regBy" class="muted small">{{ e.regBy }}</div></td>
                        <td>{{ e.agrDate ? fmtIso(e.agrDate) : '–' }}</td>
                    </tr></tbody>
                </table></div>
                <p v-if="modal.others > 0" class="muted small">{{ plural(modal.others, 'other ticked student') }} {{ modal.others === 1 ? 'has' : 'have' }} nothing for this year and will be registered as usual.</p>
            </div>
            <div class="mx-f">
                <button type="button" class="btn" @click="modal = null">Cancel</button>
                <button ref="mxGo" type="button" class="btn btn-primary" :disabled="store.busy || !(modal.others + allowed.length)" @click="continueWithPermission">{{ modal.others + allowed.length ? 'Create for ' + plural(modal.others + allowed.length, 'student') : 'Tick a student to continue' }}</button>
            </div>
        </div>
    </div>

    <section v-if="shown && shown.file" class="card">
        <div class="card-h"><h2 class="mono">{{ shown.file.name }}</h2>
            <div class="row">
                <button type="button" class="btn btn-sm" :disabled="store.busy" @click="downloadAgain(shown)"><Ico name="down" />Download again</button>
                <button type="button" class="btn btn-sm" :disabled="!texts[shown.id]" @click="copy(texts[shown.id])">{{ copied || 'Copy text' }}</button>
            </div>
        </div>
        <div class="card-b stack">
            <span v-if="msg" class="muted">{{ msg }}</span>
            <pre class="file" aria-label="File preview">{{ texts[shown.id] || 'Loading…' }}</pre>
            <div class="notice warn"><Ico name="info" /><span>Each row is the student’s original SIS line with field 1 set to today and field 29 set to <span class="mono">Y</span>. The <span class="mono">TT</span> + 6-digit count footer is assumed: check it against SLC’s import technical specification before the first live upload.</span></div>
        </div>
    </section>
</template>

<script>
import { computed, inject, nextTick, onBeforeUnmount, reactive, ref, watch } from "vue";
import { fmtIso, plural } from "./helpers";
import Ico from "./Ico.vue";
import StatusTag from "./StatusTag.vue";

export default {
    components: { StatusTag, Ico },
    setup() {
        const { store, post, get, awaitingList } = inject("slr");
        const msg = ref("");
        const copied = ref("");
        const viewId = ref(null);
        const texts = reactive({});
        const batches = computed(() => store.state.batches);
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
        const yearOf = (b) => "academic year 20" + b.header.slice(2, 4) + " · HEI " + b.header.slice(4, 8);
        async function downloadAwaiting(aw) {
            const code = aw.id;
            msg.value = "";
            const data = await post("slc.registration.batch.download", { code });
            if (data && data.file) {
                texts[code] = data.file.text;
                viewId.value = code;
                save(data.file);
            }
        }
        async function downloadAgain(b) {
            msg.value = "";
            const data = await post("slc.registration.batch.download", { code: b.id });
            if (data && data.file) { texts[b.id] = data.file.text; save(data.file); }
        }
        function copy(text) {
            try { navigator.clipboard.writeText(text).then(() => { copied.value = "Copied"; }, () => { copied.value = "Select the text below and copy it"; }); } catch (e) { copied.value = "Select the text below and copy it"; }
        }
        watch(shown, async (b) => {
            copied.value = "";
            if (b && b.file && !texts[b.id]) {
                const data = await get("slc.registration.batch.file", { code: b.id });
                if (data) texts[b.id] = data.text;
            }
        }, { immediate: true });

        /* the batch's students: View students (any batch) / after Mark uploaded; Bulk SLC Registration on the ticked */
        const studentsOf = ref(null);
        const panel = ref(null);
        const picked = ref([]);
        const sb = computed(() => batches.value.find((b) => b.id === studentsOf.value) || null);
        const canPick = computed(() => !!sb.value && sb.value.status === "uploaded");
        const isPickable = (it) => !it.record.status; // a line already recorded (or ignored) can't be ticked again
        const pickable = computed(() => (sb.value ? sb.value.items.filter(isPickable) : []));
        const allPicked = computed(() => pickable.value.length > 0 && pickable.value.every((it) => picked.value.includes(it.rowId)));
        const somePicked = computed(() => picked.value.length > 0 && !allPicked.value);
        const recorded = computed(() => (sb.value ? sb.value.items.filter((it) => it.record.status).length : 0));
        const recordLabel = (r) => {
            const when = (r.by ? r.by + ' · ' : '') + r.at;
            switch (r.status) {
                case "created": return { tone: "ok", text: "✓ Registration & agreement created", note: "Confirmed " + fmtIso(r.regDate) + " · " + when };
                case "created_anyway": return { tone: "ok", text: "✓ New registration & agreement created", note: "Earlier ones kept (with permission) · " + when };
            }
            // not yet done here: what the student already has (you're asked before new ones are created)
            if (r.hasReg && r.hasAgr) return { tone: "info", text: "Has registration & agreement", note: "Entered " + (r.regBy ? "by " + r.regBy + " · " : "") + fmtIso(r.regDate) };
            if (r.hasReg) return { tone: "info", text: "Has registration, no agreement", note: "Entered " + (r.regBy ? "by " + r.regBy + " · " : "") + fmtIso(r.regDate) };
            if (r.hasAgr) return { tone: "info", text: "Has agreement, no registration", note: "Agreement " + fmtIso(r.agrDate) };
            return { tone: "muted", text: "Not recorded", note: "" };
        };
        const toggleAll = (on) => { picked.value = on ? pickable.value.map((it) => it.rowId) : []; };
        const statusLine = (b) => (b.status === "uploaded" ? "Uploaded to SIS · " + b.uploadedBy + " " + b.uploadedAt : b.status === "downloaded" ? "Downloaded · " + b.downloadedBy + " " + b.downloadedAt + " · not yet marked uploaded" : "Awaiting download");
        async function openStudents(code) {
            studentsOf.value = code;
            picked.value = [];
            await nextTick();
            if (panel.value) panel.value.scrollIntoView({ behavior: "smooth", block: "start" });
        }
        const closeStudents = () => { studentsOf.value = null; picked.value = []; };
        async function markUploaded(code) {
            const data = await post("slc.registration.batch.uploaded", { code });
            if (data) openStudents(code);
        }
        const modal = ref(null);
        const mxGo = ref(null);
        const allowed = ref([]); // the students given permission in the window
        const allAllowed = computed(() => !!modal.value && modal.value.existing.length > 0 && modal.value.existing.every((e) => allowed.value.includes(e.rowId)));
        async function bulkRegister(confirmed = false) {
            const data = await post("slc.registration.batch.register", { code: sb.value.id, row_ids: picked.value, confirmed, overwrite_ids: confirmed ? allowed.value : [] });
            if (data && data.conflict) {
                // some ticked students already have a registration or agreement: ask, student by student
                modal.value = data.conflict;
                allowed.value = [];
                await nextTick();
                if (mxGo.value) mxGo.value.focus();
                return;
            }
            if (data) { modal.value = null; picked.value = []; allowed.value = []; }
        }
        const continueWithPermission = () => bulkRegister(true);
        const onKey = (e) => { if (e.key === "Escape" && modal.value) modal.value = null; };
        document.addEventListener("keydown", onKey);
        onBeforeUnmount(() => document.removeEventListener("keydown", onKey));
        // a line that left the batch or got recorded is no longer ticked
        watch(sb, (b) => { if (!b) return; picked.value = picked.value.filter((id) => b.items.some((it) => it.rowId === id && isPickable(it))); });

        return { modal, mxGo, allowed, allAllowed, continueWithPermission, recordLabel, studentsOf, panel, picked, sb, canPick, isPickable, pickable, allPicked, somePicked, recorded, toggleAll, statusLine, openStudents, closeStudents, markUploaded, bulkRegister, fmtIso, plural,
            store, post, awaitingList, yearOf, batches, shown, viewId, texts, msg, copied, downloadAwaiting, downloadAgain, copy };
    },
};
</script>
