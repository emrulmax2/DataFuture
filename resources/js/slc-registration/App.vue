<template>
    <header class="topbar">
        <TopBar :user="store.user" :logo="store.logo" :module="store.module" @select="setModule" />
        <div v-if="store.module === 'registration'" class="subbar"><div class="wrap">
            <nav class="nav" aria-label="Registration stages">
                <button v-for="[k, label, step, n] in nav" :key="k" type="button" :aria-current="store.view === k ? 'page' : undefined" @click="go(k)">
                    <span v-if="step" class="step">{{ step }}</span>{{ label }}<span v-if="n" class="count num">{{ n }}</span>
                </button>
            </nav>
        </div></div>
        <div v-if="store.module === 'payment'" class="subbar"><div class="wrap">
            <nav class="nav" aria-label="Payment upload steps">
                <button v-for="[k, label, step, n] in payNav" :key="k" type="button" :aria-current="payView === k ? 'page' : undefined" @click="payGo(k)">
                    <span v-if="step" class="step">{{ step }}</span>{{ label }}<span v-if="n" class="count num">{{ n }}</span>
                </button>
            </nav>
        </div></div>
        <div v-if="store.module === 'attendance'" class="subbar"><div class="wrap">
            <nav class="nav" aria-label="Attendance steps">
                <button v-for="[k, label, step, n] in attNav" :key="k" type="button" :aria-current="attView === k ? 'page' : undefined" @click="attGo(k)">
                    <span v-if="step" class="step">{{ step }}</span>{{ label }}<span v-if="n" class="count num">{{ n }}</span>
                </button>
            </nav>
        </div></div>
    </header>
    <main class="wrap">
        <div v-if="store.flash" :key="store.flash.k" :class="'notice ' + (store.flash.tone === 'bad' ? 'bad' : 'ok')" role="status">
            <Ico name="info" /><span style="flex: 1">{{ store.flash.text }}</span>
            <button type="button" class="btn-link" @click="store.flash = null">Dismiss</button>
        </div>
        <Dashboard v-if="store.module === 'dashboard'" />
        <Attendance v-else-if="store.module === 'attendance'" :view="attView" :show="attShow" @go="attGo" @show="(s) => (attShow = s)" />
        <Payments v-else-if="store.module === 'payment'" :view="payView" :show="payShow" @go="payGo" />
        <Planned v-else-if="store.module !== 'registration'" :module="store.module" @open-actions="openActions" />
        <template v-else>
            <Overview v-if="store.view === 'dashboard'" />
            <template v-else-if="['identify', 'data', 'actions', 'precheck'].includes(store.view) && !store.state.import">
                <div class="card empty">Upload the SIS Registration Confirmation Report on the Overview first.
                    <div style="margin-top: 10px"><button type="button" class="btn btn-primary" @click="go('dashboard')">Open overview</button></div>
                </div>
            </template>
            <Identify v-else-if="store.view === 'identify'" />
            <DataCheck v-else-if="store.view === 'data'" />
            <Actions v-else-if="store.view === 'actions'" />
            <PreConfirm v-else-if="store.view === 'precheck'" />
            <Batches v-else-if="store.view === 'batches'" />
            <Courses v-else-if="store.view === 'courses'" />
            <Log v-else-if="store.view === 'log'" />
        </template>
    </main>
</template>

<script>
import { computed, inject, ref } from "vue";
import { MODULES } from "./helpers";
import Ico from "./Ico.vue";
import TopBar from "./TopBar.vue";
import Overview from "./Overview.vue";
import Identify from "./Identify.vue";
import DataCheck from "./DataCheck.vue";
import Actions from "./Actions.vue";
import PreConfirm from "./PreConfirm.vue";
import Batches from "./Batches.vue";
import Courses from "./Courses.vue";
import Planned from "./Planned.vue";
import Log from "./Log.vue";
import Dashboard from "./Dashboard.vue";
import Attendance from "./Attendance.vue";
import Payments from "./Payments.vue";

export default {
    components: { Ico, TopBar, Overview, Identify, DataCheck, Actions, PreConfirm, Batches, Courses, Planned, Log, Dashboard, Attendance, Payments },
    setup() {
        const ctx = inject("slr");
        const { store, go, cocItems, contactItems, awaitingCount } = ctx;
        const nav = computed(() => {
            const c = store.state.counts;
            return [
                ["dashboard", "Overview", null, null],
                ["identify", "Identify", "1", c.identify],
                ["data", "Data check", "2", c.data],
                ["actions", "Actions", "3", cocItems.value.length + contactItems.value.length + c.held],
                ["precheck", "Pre-confirm", "4", c.precheck],
                ["batches", "Batches", "5", awaitingCount.value],
                ["courses", "Course mapping", null, null],
                ["log", "Activity", null, null],
            ];
        });
        const setModule = (k) => { store.module = k; window.scrollTo(0, 0); };
        // Bulk attendance: Overview (step 1, upload) and steps 2–7
        const attView = ref("overview");
        const attShow = ref("open");
        const attNav = computed(() => {
            const c = store.state.attendance.counts;
            return [["overview", "Overview", null, null], ["match", "Match students", "2", c.unmatched], ["check", "Data check", "3", c.data],
                ["register", "Attendance", "4", c.noAttendance], ["codes", "Codes", "5", c.codeReady], ["cocs", "CoCs", "6", c.cocToSubmit], ["batches", "Batches", "7", c.awaiting]];
        });
        const attGo = (v, show) => { attView.value = v; if (show) attShow.value = show; window.scrollTo(0, 0); };
        window.addEventListener("slr-att-go", (e) => attGo(e.detail.view, e.detail.show)); // from the Dashboard card
        // Bulk payment upload: Overview (upload), Check & record, Uploads
        const payView = ref("overview");
        const payShow = ref("ready");
        const payNav = computed(() => {
            const c = store.state.payments.counts;
            return [["overview", "Overview", null, null], ["check", "Check & record", "2", c.ready + c.look + c.error], ["uploads", "Uploads", null, null]];
        });
        const payGo = (v, show) => { payView.value = v; if (show) payShow.value = show; window.scrollTo(0, 0); };
        window.addEventListener("slr-pay-go", (e) => payGo(e.detail.view, e.detail.show)); // from the Dashboard card
        const openActions = () => { store.module = "registration"; go("actions"); };
        return { store, go, nav, MODULES, setModule, openActions, attView, attShow, attNav, attGo, payView, payShow, payNav, payGo };
    },
};
</script>
