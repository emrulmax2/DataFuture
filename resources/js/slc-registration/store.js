import { reactive, computed } from "vue";
import { derive } from "./helpers";

/*
 * The page's one store. `state` is the server's payload (RegistrationState); every action posts
 * and puts the fresh payload back, with the message to flash. UI-only state (current view, the
 * selected line, the attendance tick boxes) lives here too, as the prototype's useState did.
 */
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

const MODULE_KEYS = ["dashboard", "registration", "attendance", "coc", "payment"];

export function createStore(boot) {
    const asked = new URLSearchParams(window.location.search).get("module");
    const store = reactive({
        state: boot.state,
        user: boot.user,
        logo: boot.logo,
        module: MODULE_KEYS.includes(asked) ? asked : "dashboard",
        view: "dashboard",
        selected: null,
        flash: null,
        busy: false,
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

    return { store, say, post, get, list, cocItems, contactItems, awaiting, awaitingList, awaitingCount, go };
}
