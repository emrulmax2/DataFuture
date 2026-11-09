<template>
        <div class="wrap">
            <a class="brand" :href="user.dashboard" aria-label="London Churchill College – dashboard">
                <img class="brand-logo" :src="logo" alt="London Churchill College">
            </a>
            <nav class="modules" aria-label="SLC processes">
                <a :href="user.dashboard">Dashboard</a>
                <a v-for="[k, label, live] in MODULES" :key="k" :href="hrefOf(k)" :aria-current="module === k ? 'page' : undefined" @click="select($event, k)">
                    {{ label }}<span v-if="!live" class="soon">Planned</span>
                </a>
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
            </div>
        </div>
</template>

<script>
import { MODULES } from "./helpers";

/*
 * The SLC header row (user, 5 Nov 2026): the system logo, Dashboard (the site's home page), SLC
 * (this page's dashboard), the bulk tabs and the signed-in user. Each tab is a link to
 * /slc-registration on that tab; on the main page a plain click switches in place instead.
 */
export default {
    props: {
        user: { type: Object, required: true },
        logo: { type: String, required: true },
        module: { type: String, default: "" },
        standalone: { type: Boolean, default: false },
    },
    emits: ["select"],
    setup(props, { emit }) {
        const hrefOf = (k) => route("slc.registration") + (k === "dashboard" ? "" : "?module=" + k);
        const select = (e, k) => {
            if (props.standalone || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return; // follow the link
            e.preventDefault();
            emit("select", k);
        };
        return { MODULES, hrefOf, select };
    },
};
</script>
