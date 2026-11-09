<template>
    <div class="cm-links"><button type="button" class="btn-link small" @click="$emit('open', 'email')"><Ico name="mail" />Email</button><button type="button" class="btn-link small" @click="$emit('open', 'sms')"><Ico name="sms" />SMS</button></div>
</template>

<script>
import Ico from "./Ico.vue";

/* Actions › a student's Email and SMS buttons (user, 6 Nov 2026), opening CommsDialog. They sit in
   each table's last column, under the row's own buttons. */
export default {
    components: { Ico },
    emits: ["open"],
};
</script>
