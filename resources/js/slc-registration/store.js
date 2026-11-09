import { reactive, computed, watch, nextTick } from "vue";
import { derive } from "./helpers";

/*
 * The page's one store. `state` is the server's payload (RegistrationState); every action posts
 * and puts the fresh payload back, with the message to flash. UI-only state (current view, the
 * selected line, the attendance tick boxes) lives here too, as the prototype's useState did.
 */
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

const MODULE_KEYS = ["dashboard", "registration", "attendance", "coc", "payment"];

/*
 * Every tab is a page in the browser's history (user, 5 Nov 2026: "on back load the last pages I
 * browsed"): the header tabs, the registration stages and the attendance / payment steps each get
 * their own address (?module=registration&view=data), so Back and Forward step through them and
 * come back to where the page was scrolled. A reload or a shared link opens the same tab.
 */
const VIEWS = {
    registration: ["dashboard", "identify", "data", "actions", "precheck", "batches", "courses", "log", "clear"],
    attendance: ["overview", "match", "check", "codes", "cocs", "batches", "clear"],
    payment: ["overview", "check", "uploads", "clear"],
};
const FIRST = { registration: "dashboard", attendance: "overview", payment: "overview" };
const VIEW_KEY = { registration: "view", attendance: "attView", payment: "payView" };
// steps that became part of another: Bulk attendance › Attendance is in Attendance & codes (6 Nov 2026)
const MERGED = { attendance: { register: "codes" } };

/** The tab an address asks for: the module, and its stage / step when it has them. */
function fromUrl() {
    const q = new URLSearchParams(window.location.search);
    const module = MODULE_KEYS.includes(q.get("module")) ? q.get("module") : "dashboard";
    const out = { module };
    const view = (MERGED[module] || {})[q.get("view")] || q.get("view");
    if (VIEWS[module]) out[VIEW_KEY[module]] = VIEWS[module].includes(view) ? view : FIRST[module];
    if (module === "attendance") out.attShow = q.get("show") === "matched" ? "matched" : "open";
    return out;
}

/** The address of a tab (the module's first stage and the default filter are left out). */
function toUrl(n) {
    const q = new URLSearchParams();
    if (n.module !== "dashboard") q.set("module", n.module);
    const v = VIEW_KEY[n.module] ? n[VIEW_KEY[n.module]] : null;
    if (v && v !== FIRST[n.module]) q.set("view", v);
    if (n.module === "attendance" && n.attView === "match" && n.attShow !== "open") q.set("show", n.attShow);
    const qs = q.toString();
    return window.location.pathname + (qs ? "?" + qs : "");
}

export function createStore(boot) {
    const store = reactive({
        state: boot.state,
        user: boot.user,
        logo: boot.logo,
        module: "dashboard",
        view: "dashboard",            // Bulk registration stage
        attView: "overview",          // Bulk attendance step
        attShow: "open",              // … its To match / Matched list
        payView: "overview",          // Bulk payment upload step
        payShow: "ready",
        selected: null,
        flash: null,
        busy: false,
        ...fromUrl(),
    });
    // Clear DB is only for users 1 and 7 (server: ModuleReset::USERS)
    if (!(boot.user && boot.user.canClear)) Object.entries(VIEW_KEY).forEach(([m, k]) => { if (store[k] === "clear") store[k] = FIRST[m]; });

    // the browser's history follows the tabs
    const tab = () => ({ module: store.module, view: store.view, attView: store.attView, attShow: store.attShow, payView: store.payView, payShow: store.payShow });
    const same = (a, b) => JSON.stringify(a) === JSON.stringify(b);
    let scrolled = window.scrollY; // where the page was before a tab's click scrolled it to the top
    window.addEventListener("scroll", () => { scrolled = window.scrollY; }, { passive: true });
    if ("scrollRestoration" in window.history) window.history.scrollRestoration = "manual";
    window.history.replaceState({ ...(window.history.state || {}), slr: tab() }, "", toUrl(tab()));
    watch(tab, (n) => {
        const url = toUrl(n);
        if (url === window.location.pathname + window.location.search) {
            // Back / Forward put this tab back, or something outside the address changed
            if (!same(window.history.state && window.history.state.slr, n)) window.history.replaceState({ ...(window.history.state || {}), slr: n }, "", url);
            return;
        }
        window.history.replaceState({ ...(window.history.state || {}), scroll: scrolled }, "");
        window.history.pushState({ slr: n, scroll: 0 }, "", url);
    });
    window.addEventListener("popstate", (e) => {
        const st = e.state || {};
        const back = st.slr ? { ...st.slr } : fromUrl();
        if (back.attView === "register") back.attView = "codes"; // a history entry from before the steps were merged
        Object.assign(store, back);
        nextTick(() => window.scrollTo(0, st.scroll || 0));
    });

    const say = (text, tone) => { store.flash = { text, tone: tone || "ok", k: Date.now() }; };

    async function request(method, name, body, params) {
        const url = route(name, params || {});
        const opts = { method, headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }, credentials: "same-origin" };
        if (body instanceof FormData) {
            opts.body = body;
        } else if (body) {
            opts.headers["Content-Type"] = "application/json";
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(url, opts);
        let data = null;
        try { data = await res.json(); } catch (e) { data = null; }
        if (res.status === 401 || res.status === 419) {
            say("Your session has expired. Reload the page and sign in again.", "bad");
            throw new Error("session");
        }
        if (res.status === 403) {
            say("You do not have access to SLC Registration.", "bad");
            throw new Error("forbidden");
        }
        return { res, data };
    }

    /** POST an action: puts the fresh state back and flashes its message. Resolves to the response data. */
    async function post(name, body) {
        if (store.busy) return null;
        store.busy = true;
        try {
            const { res, data } = await request("POST", name, body);
            if (res.status === 409 && data) return { conflict: data };
            if (data && data.state) store.state = data.state;
            if (data && data.flash) say(data.flash.text, data.flash.tone);
            else if (!res.ok) say((data && (data.message || (data.errors && Object.values(data.errors)[0][0]))) || "Something went wrong. Please try again.", "bad");
            return res.ok ? data : null;
        } catch (e) {
            if (e.message !== "session" && e.message !== "forbidden") say("The server could not be reached. Please try again.", "bad");
            return null;
        } finally {
            store.busy = false;
        }
    }

    /**
     * POST a dialog's form (Actions › Email / SMS): as post, but a 422's field errors go back to the
     * dialog to show under each field instead of being flashed. Resolves to { ok, data, errors }.
     */
    async function send(name, body) {
        if (store.busy) return { ok: false };
        store.busy = true;
        try {
            const { res, data } = await request("POST", name, body);
            if (data && data.state) store.state = data.state;
            if (res.status === 422 && data && data.errors && !data.flash) return { ok: false, errors: data.errors };
            if (data && data.flash) say(data.flash.text, data.flash.tone);
            else if (!res.ok) say((data && data.message) || "Something went wrong. Please try again.", "bad");
            return { ok: res.ok, data };
        } catch (e) {
            if (e.message !== "session" && e.message !== "forbidden") say("The server could not be reached. Please try again.", "bad");
            return { ok: false };
        } finally {
            store.busy = false;
        }
    }

    async function get(name, params) {
        const { res, data } = await request("GET", name, null, params);
        return res.ok ? data : null;
    }

    const list = computed(() => (store.state.rows || []).map((i) => ({ ...i, ...derive(i) })));
    const cocItems = computed(() => list.value.flatMap((i) => i.errors.filter((c) => c.route === "coc").map((ch) => ({ i, ch }))));
    const contactItems = computed(() => list.value.flatMap((i) => i.errors.filter((c) => c.route === "contact").map((ch) => ({ i, ch }))));
    const awaitingList = computed(() => (store.state.batches || []).filter((b) => b.status === "awaiting"));
    const awaiting = computed(() => awaitingList.value[0] || null);
    const awaitingCount = computed(() => awaitingList.value.reduce((a, b) => a + b.items.length, 0));

    const go = (v, rowId) => {
        store.view = v;
        if (rowId) store.selected = rowId;
        window.scrollTo(0, 0);
    };

    return { store, say, post, send, get, list, cocItems, contactItems, awaiting, awaitingList, awaitingCount, go };
}
