<template>
    <span :class="'pill ' + (att.st === 'ok' ? 'ok' : att.st === 'warn' ? 'warn' : 'bad')">{{ att.st === 'ok' ? '✓ ' : '! ' }}{{ att.text }}</span>
    <div class="small num" style="margin-top: 4px"><b>{{ att.pct }}%</b> · {{ att.a.attended }} of {{ att.a.held }} sessions</div>
    <div v-if="att.a.attended" class="att-split" :aria-label="'Attended: ' + att.a.physical + ' physical, ' + att.a.online + ' online' + (att.a.other ? ', ' + att.a.other + ' other' : '')">
        <span class="att-bar" aria-hidden="true">
            <span class="att-p" :style="{ width: (att.a.physical / att.a.attended * 100) + '%' }"></span><span class="att-o" :style="{ width: (att.a.online / att.a.attended * 100) + '%' }"></span><span class="att-x" :style="{ width: (att.a.other / att.a.attended * 100) + '%' }"></span>
        </span>
        <span class="att-legend small num">
            <span><i class="att-dot att-p" aria-hidden="true"></i>Physical (P) <b>{{ att.a.physical }}</b></span>
            <span><i class="att-dot att-o" aria-hidden="true"></i>Online (O) <b>{{ att.a.online }}</b></span>
            <span v-if="att.a.other" :title="att.a.otherNames"><i class="att-dot att-x" aria-hidden="true"></i>Other <b>{{ att.a.other }}</b> <span class="muted">({{ att.a.otherNames }})</span></span>
        </span>
    </div>
    <div class="muted small">Last attended {{ att.last ? fmtIso(att.last) : 'never' }}</div>
</template>

<script>
import { fmtIso } from "./helpers";

/*
 * A student's attendance from the register (Checks::attendance): status, % and sessions, the attended
 * sessions split into physical (P), online (O) and other statuses that count, last attended.
 * Used by Bulk registration › Pre-confirm and Bulk attendance › Match students.
 */
export default {
    props: { att: { type: Object, required: true } },
    setup() {
        return { fmtIso };
    },
};
</script>
