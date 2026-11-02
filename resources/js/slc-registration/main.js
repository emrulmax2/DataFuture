import { createApp } from "vue";
import App from "./App.vue";
import MultipleAgreements from "./MultipleAgreements.vue";
import { createStore } from "./store";

/*
 * /slc-registration — SLC bulk registration confirmation (user's "SLC Registration Check"
 * prototype, 2 Nov 2026), ported to Vue. The page's first payload is embedded as JSON
 * (#slr-boot); after that every action answers with a fresh one. Routes come from Ziggy (@routes).
 */
const el = document.getElementById("slc-registration-app");
const bootEl = document.getElementById("slr-boot");
if (el && bootEl) {
    const boot = JSON.parse(bootEl.textContent || "{}");
    if (boot.page === "multiple") {
        createApp(MultipleAgreements, { boot }).mount(el); // its own page, opened from the Dashboard
    } else {
        createApp(App).provide("slr", createStore(boot)).mount(el);
    }
}
