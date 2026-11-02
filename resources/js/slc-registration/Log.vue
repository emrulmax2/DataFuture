<template>
    <div class="pagehead"><div><h1>Activity</h1><p>Every upload, identification, record change, action and confirmation, with the user who did it.</p></div></div>
    <section class="card">
        <div v-if="log.length === 0" class="empty">No activity yet.</div>
        <ul v-else class="log"><li v-for="(e, k) in log" :key="k"><span class="t">{{ e.at }}</span><span class="u">{{ e.user }}</span><span style="flex: 1 1 300px; min-width: 0">{{ e.text }}</span></li></ul>
    </section>
</template>

<script>
import { computed, inject } from "vue";

export default {
    setup() {
        const { store } = inject("slr");
        return { log: computed(() => store.state.log) };
    },
};
</script>
