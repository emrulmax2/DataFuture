<template>
    <a v-if="i.s && i.s.uploadsUrl" class="uploads-link small" :href="i.s.uploadsUrl" target="_blank" rel="noopener" :title="'Open ' + i.s.first + ' ' + i.s.last + '’s documents in a new tab'"><Ico name="doc" />Check uploads (proof of {{ what === 'id' ? 'ID' : 'address' }})</a>
</template>

<script>
import Ico from "./Ico.vue";

/*
 * Data check › a link to the student's Documents page (/student/uploads/{id}, new tab) to look at the
 * proof before deciding: proof of address on an address difference (user, 6 Nov 2026), proof of ID on
 * a name or date of birth difference (user, 6 Nov 2026, "like we did for address").
 */
export default {
    components: { Ico },
    props: {
        i: { type: Object, required: true },
        what: { type: String, default: "address" }, // address | id
    },
};
</script>
