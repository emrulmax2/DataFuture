<template>
    <dl class="dl">
        <div v-for="[k, v] in items" :key="k"><dt>{{ k }}</dt><dd :class="k === 'SSN' ? 'mono' : ''">{{ v || '–' }}</dd></div>
    </dl>
</template>

<script>
import { computed } from "vue";
import { fmtSlc, money, titleCase } from "./helpers";

export default {
    props: { r: { type: Object, required: true } },
    setup(props) {
        const items = computed(() => {
            const r = props.r;
            return [["Title", r.title], ["First name", r.first + (r.other ? " " + r.other : "")], ["Last name", r.last], ["Gender", r.gender], ["Date of birth", fmtSlc(r.dob)], ["Course", r.slcCode + " · Year " + r.year], ["Course name", r.courseName], ["Start date", fmtSlc(r.start)], ["Address", titleCase(r.address)], ["Postcode", r.postcode], ["SSN", r.ssn], ["TFL", money(r.tfl)]];
        });
        return { items };
    },
};
</script>
