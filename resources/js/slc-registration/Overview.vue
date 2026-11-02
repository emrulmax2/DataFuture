<template>
    <div class="pagehead">
        <div>
            <h1>SLC registration confirmation</h1>
            <p>Upload the SIS Registration Confirmation Report, identify students, sort out data errors, check awarding body registration and attendance, then confirm Yes into a batch for SIS.</p>
        </div>
    </div>

    <section class="card" aria-labelledby="imp">
        <div class="card-h"><h2 id="imp">Upload from SIS</h2><span class="muted small">SIS → Registration Worklist → Filter and Export → Registration Confirmation Report</span></div>
        <div class="card-b stack">
            <div :class="'drop' + (over ? ' over' : '')" @dragover.prevent="over = true" @dragleave="over = false" @drop.prevent="onDrop">
                <div class="stack" style="gap: 4px">
                    <strong>Drop the .txt export here, or choose a file</strong>
                    <span class="muted small">The file is kept with the upload for reference. Nothing is sent to SLC from here.</span>
                </div>
                <div class="row">
                    <label class="btn btn-primary" for="sis-file" :aria-disabled="store.busy"><Ico name="up" />{{ store.busy && uploading ? 'Uploading…' : 'Choose file' }}</label>
                    <input id="sis-file" type="file" accept=".txt,text/plain" hidden @change="onPick">
                </div>
            </div>
            <div v-if="imp" class="row small" style="gap: 6px 18px">
                <span><span class="muted">File</span> {{ imp.fileName }}</span>
                <span><span class="muted">Header</span> <span class="mono">{{ imp.header.raw }}</span> <span class="muted">(AY {{ imp.header.ay }} · HEI {{ imp.header.hei }})</span></span>
                <span><span class="muted">Students</span> <b class="num">{{ counts.all }}</b> · {{ counts.ssn }} matched by SSN · {{ counts.linked }} identified</span>
                <span><span class="muted">Uploaded</span> {{ imp.at }} by {{ imp.by }}</span>
                <span v-if="imp.errors.length" class="pill bad" :title="imp.errors.join('\n')">{{ plural(imp.errors.length, 'line') }} skipped</span>
            </div>
        </div>
    </section>

    <template v-if="imp">
        <ol class="pipeline" aria-label="Where students are in the process">
            <li v-for="[k, n, l, v, m, t] in stages" :key="k"><button type="button" :class="'stage t-' + t" @click="go(k)">
                <span class="s-n">STAGE {{ n }}</span><span class="s-l">{{ l }}</span><span class="s-v">{{ v }}</span><span class="s-m">{{ m }}</span>
            </button></li>
        </ol>
        <section class="kpis" aria-label="Error outcomes">
            <div class="kpi t-bad"><span class="k-label">Errors found</span><span class="k-val">{{ counts.errors }}</span><span class="k-note">Across all linked students</span></div>
            <div class="kpi t-info"><span class="k-label">Our record updated</span><span class="k-val">{{ store.state.updates.length }}</span><span class="k-note">Changes made in datafuture</span></div>
            <div class="kpi t-bad"><span class="k-label">CoC to issue</span><span class="k-val">{{ cocItems.length }}</span><span class="k-note">Raise in SIS</span></div>
            <div class="kpi t-warn"><span class="k-label">Contact student</span><span class="k-val">{{ contactItems.length }}</span><span class="k-note">Student updates SLC</span></div>
            <div class="kpi"><span class="k-label">Not registered</span><span class="k-val">{{ counts.notreg }}</span><span class="k-note">Mark N manually in SIS</span></div>
            <div class="kpi t-ok"><span class="k-label">Batches downloaded</span><span class="k-val">{{ batches.filter((b) => b.status !== 'awaiting').length }}</span><span class="k-note">{{ batches.filter((b) => b.status === 'uploaded').length }} uploaded to SIS</span></div>
        </section>
    </template>
</template>

<script>
import { computed, inject, ref } from "vue";
import { plural } from "./helpers";
import Ico from "./Ico.vue";

export default {
    components: { Ico },
    setup() {
        const { store, post, go, cocItems, contactItems, awaitingCount } = inject("slr");
        const over = ref(false);
        const uploading = ref(false);
        const imp = computed(() => store.state.import);
        const counts = computed(() => store.state.counts);
        const batches = computed(() => store.state.batches);

        async function readFile(f) {
            if (!f || store.busy) return;
            const fd = new FormData();
            fd.append("file", f);
            uploading.value = true;
            const data = await post("slc.registration.upload", fd);
            uploading.value = false;
            if (data) store.selected = null;
        }
        const onDrop = (e) => { over.value = false; readFile(e.dataTransfer.files[0]); };
        const onPick = (e) => { readFile(e.target.files[0]); e.target.value = ""; };

        const stages = computed(() => {
            const c = counts.value;
            return [
                ["identify", "1", "Identify student", c.identify, "No SSN on our system", c.identify ? "warn" : "ok"],
                ["data", "2", "Data check", c.data, "Students with errors to sort", c.data ? "bad" : "ok"],
                ["actions", "3", "Actions – on hold", c.action + c.held, "CoC, student contact or awarding body", c.action + c.held ? "warn" : "ok"],
                ["precheck", "4", "Pre-confirm", c.precheck, "Awarding body and attendance", "info"],
                ["batches", "5", "Awaiting download", awaitingCount.value, "Confirmed Yes, in batch", "ok"],
            ];
        });

        return { store, go, over, uploading, imp, counts, batches, stages, cocItems, contactItems, onDrop, onPick, plural };
    },
};
</script>
