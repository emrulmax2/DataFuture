<template>
        <div class="wrap">
            <a class="brand" :href="user.dashboard" aria-label="London Churchill College – dashboard">
                <img class="brand-logo" :src="logo" alt="London Churchill College">
                <span class="brand-sep" aria-hidden="true"></span>
                <span class="brand-name">SLC</span>
            </a>
            <nav class="modules" aria-label="SLC processes">
                <button v-for="[k, label, live] in MODULES" :key="k" type="button" :aria-current="module === k ? 'page' : undefined" @click="select(k)">
                    {{ label }}<span v-if="!live" class="soon">Planned</span>
                </button>
            </nav>
            <div class="userbox">
                <div class="user" :title="user.email">
                    <span class="avatar" aria-hidden="true">
                        <img v-if="user.avatar" :src="user.avatar" alt="">
                        <template v-else>{{ user.initials }}</template>
                    </span>
                    <span class="user-text">
                        <span class="user-name">{{ user.name }}</span>
                        <span class="user-role">{{ user.role }}</span>
                    </span>
                </div>
                <a class="btn-back" :href="user.dashboard"><Ico name="back" />Back to dashboard</a>
            </div>
        </div>
</template>

<script>
import { MODULES } from "./helpers";
import Ico from "./Ico.vue";

/*
 * The SLC header row: the system logo, the four tabs, the signed-in user and Back to dashboard.
 * On the main page a tab switches in place; on a page of its own (standalone) it goes back to
 * /slc-registration on that tab.
 */
export default {
    components: { Ico },
    props: {
        user: { type: Object, required: true },
        logo: { type: String, required: true },
        module: { type: String, default: "" },
        standalone: { type: Boolean, default: false },
    },
    emits: ["select"],
    setup(props, { emit }) {
        const select = (k) => {
            if (props.standalone) {
                window.location.href = route("slc.registration") + "?module=" + k;
                return;
            }
            emit("select", k);
        };
        return { MODULES, select };
    },
};
</script>
