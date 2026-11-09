<template>
    <div class="st-phone small"><Ico name="mobile" />
        <a v-if="mobile" class="plain mono" :href="'tel:' + mobile.replace(/[^\d+]/g, '')" :title="'Mobile – call ' + mobile">{{ mobile }}</a>
        <span v-else class="muted">No mobile number</span>
    </div>
</template>

<script>
import Ico from "./Ico.vue";

/*
 * Actions › the student's mobile number (user, 6 Nov 2026: "show mobile number from student_contacts"):
 * student_contacts.mobile of their newest contact row, the number SMS goes to; a tel: link.
 */
export default {
    components: { Ico },
    props: { mobile: { type: String, default: "" } },
};
</script>
