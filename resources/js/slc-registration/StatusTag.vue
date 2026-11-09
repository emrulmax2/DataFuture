<template>
    <template v-if="status && status.name"><span class="stag-gap">{{ " " }}</span><span :class="'stag t-' + (status.tone || 'muted')" :title="'Student status: ' + status.name">{{ status.name }}</span></template>
</template>

<script>
/*
 * A student's current status beside their name, everywhere on /slc-registration (user, 6 Nov 2026):
 * a small button-like tag on a see-through tint of its tone (server: StudentStatus — studying green,
 * completed blue, incomplete / suspended amber, left / discarded / terminated red, other grey).
 * The gap is the name's right margin, not the tag's left one, so a tag that wraps to the next line
 * lines up with the start of the name (user, 6 Nov 2026).
 */
export default {
    props: { status: { type: Object, default: null } },
};
</script>
