import axios from "axios";
import { createElement, createIcons, icons, Eye, FileCode2, List } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const app = document.getElementById("errorLogApp");

    if (!app) {
        return;
    }

    const LEVEL_ORDER = ["EMERGENCY", "ALERT", "CRITICAL", "ERROR", "WARNING", "NOTICE", "INFO", "DEBUG"];
    // Laravel logs the id from whichever guard was signed in, so it may be a
    // student or applicant login rather than a staff user.
    const NOT_STAFF = "no staff user with this id (may be a student or applicant login)";
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    const state = {
        file: document.getElementById("elFile").value,
        mode: "groups",
        levels: new Set(),
        env: "",
        q: "",
        from: "",
        to: "",
        fp: "",
        fpLabel: "",
    };

    let table = null;
    let envsFor = "";

    const modalEl = document.getElementById("errorLogEntryModal");
    const modal = tailwind.Modal.getOrCreateInstance(modalEl);
    const entryBody = document.getElementById("elEntryBody");

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.7,
            nameAttr: "data-lucide",
        });
    };

    const escapeHtml = (value) => {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    // Table cells get their icons inline: rows Tabulator draws after
    // renderComplete would otherwise keep empty <i data-lucide> tags.
    const iconSvg = (Icon) => {
        const icon = createElement(Icon);
        icon.setAttribute("stroke-width", "1.9");
        icon.setAttribute("aria-hidden", "true");
        return icon.outerHTML;
    };

    const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    const levelLabel = (level) => {
        const value = String(level || "");
        return value.charAt(0) + value.slice(1).toLowerCase();
    };

    const levelPill = (level) => {
        return `<span class="el-level el-level--${escapeHtml(String(level).toLowerCase())}"><span></span>${escapeHtml(levelLabel(level))}</span>`;
    };

    const shortClass = (name) => {
        const parts = String(name || "").split("\\");
        return parts[parts.length - 1];
    };

    const splitPath = (path) => {
        const value = String(path || "");
        const cut = value.lastIndexOf("/");
        return cut === -1 ? ["", value] : [value.slice(0, cut + 1), value.slice(cut + 1)];
    };

    const copyText = (text, button) => {
        const done = () => {
            if (!button) {
                return;
            }
            const original = button.innerHTML;
            button.innerHTML = '<i data-lucide="check"></i> Copied';
            refreshIcons();
            setTimeout(() => {
                button.innerHTML = original;
                refreshIcons();
            }, 1400);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
            return;
        }

        const area = document.createElement("textarea");
        area.value = text;
        area.setAttribute("readonly", "");
        area.style.position = "fixed";
        area.style.opacity = "0";
        document.body.appendChild(area);
        area.select();
        document.execCommand("copy");
        area.remove();
        done();
    };

    /* ---------------------------------------------------------------- *
     * Indexing: the server reads new log lines in time-boxed steps.
     * ---------------------------------------------------------------- */
    const progress = document.getElementById("elProgress");
    const progressBar = document.getElementById("elProgressBar");
    const progressPct = document.getElementById("elProgressPct");

    const updateFileMeta = (file) => {
        if (!file) {
            return;
        }
        document.getElementById("elFileTitle").textContent = file.name;
        document.getElementById("elFileMeta").textContent = `${file.size_human} · updated ${file.modified_human}`;
        const option = document.querySelector(`#elFile option[value="${CSS.escape(file.name)}"]`);
        if (option) {
            option.textContent = `${file.name} (${file.size_human})`;
        }
    };

    const sync = async (file) => {
        let shown = false;
        const showTimer = setTimeout(() => {
            shown = true;
            document.getElementById("elProgressFile").textContent = file;
            progress.hidden = false;
        }, 350);

        try {
            for (let attempt = 0; attempt < 400; attempt++) {
                const { data } = await axios.post(route("error.logs.sync"), { file }, { headers: { "X-CSRF-TOKEN": csrf } });

                updateFileMeta(data.file);
                if (data.busy) {
                    await wait(700);
                    continue;
                }

                progressBar.style.width = `${data.progress}%`;
                progressPct.textContent = `${data.progress}%`;
                if (data.done) {
                    break;
                }
            }
        } finally {
            clearTimeout(showTimer);
            if (shown) {
                progress.hidden = true;
            }
            progressBar.style.width = "0%";
        }
    };

    /* ---------------------------------------------------------------- *
     * Level chips and the environment list come back with every page.
     * ---------------------------------------------------------------- */
    const levelsEl = document.getElementById("elLevels");
    const envSelect = document.getElementById("elEnv");

    const renderFacets = (facets) => {
        if (!facets) {
            return;
        }

        const counts = facets.levels || {};
        const present = LEVEL_ORDER.filter((level) => counts[level] || state.levels.has(level));
        const all = Object.values(counts).reduce((sum, value) => sum + value, 0);

        let html = `<button type="button" class="el-chip el-chip--all ${state.levels.size === 0 ? "is-active" : ""}" data-level="" aria-pressed="${state.levels.size === 0}">
                All <span class="el-chip__count">${all.toLocaleString()}</span>
            </button>`;
        present.forEach((level) => {
            const active = state.levels.has(level);
            html += `<button type="button" class="el-chip el-chip--${level.toLowerCase()} ${active ? "is-active" : ""}" data-level="${level}" aria-pressed="${active}">
                    <span class="el-chip__dot"></span>${escapeHtml(levelLabel(level))} <span class="el-chip__count">${(counts[level] || 0).toLocaleString()}</span>
                </button>`;
        });
        html += `<span class="el-levels__total">${Number(facets.matched || 0).toLocaleString()} of ${Number(facets.total || 0).toLocaleString()} entries</span>`;
        levelsEl.innerHTML = html;

        if (envsFor !== state.file) {
            envsFor = state.file;
            const selected = state.env;
            envSelect.innerHTML = '<option value="">All environments</option>';
            Object.entries(facets.envs || {}).forEach(([env, count]) => {
                const option = document.createElement("option");
                option.value = env;
                option.textContent = `${env} (${count})`;
                option.selected = env === selected;
                envSelect.appendChild(option);
            });
        }
    };

    levelsEl.addEventListener("click", (event) => {
        const chip = event.target.closest(".el-chip");
        if (!chip) {
            return;
        }

        const level = chip.dataset.level;
        if (!level) {
            state.levels.clear();
        } else if (state.levels.has(level)) {
            state.levels.delete(level);
        } else {
            state.levels.add(level);
        }
        buildTable();
    });

    /* ---------------------------------------------------------------- *
     * The table: grouped (one row per distinct problem) or every entry.
     * ---------------------------------------------------------------- */
    const messageFormatter = (cell) => {
        const data = cell.getData();
        const meta = [];

        if (data.class) {
            meta.push(`<span class="el-msg__class" title="${escapeHtml(data.class)}">${escapeHtml(shortClass(data.class))}</span>`);
        }
        if (data.where) {
            meta.push(`<span class="el-msg__where" title="${escapeHtml(data.where)}">${iconSvg(FileCode2)}${escapeHtml(data.where)}</span>`);
        }

        return `<div class="el-msg">
                <div class="el-msg__text">${escapeHtml(data.message) || '<em class="el-muted">(no message)</em>'}</div>
                ${meta.length ? `<div class="el-msg__meta">${meta.join("")}</div>` : ""}
            </div>`;
    };

    const whenFormatter = (ago, date, since) =>
        `<span class="el-when"><strong>${escapeHtml(ago)}</strong><small>${escapeHtml(date)}</small>${since ? `<small class="el-when__since">first ${escapeHtml(since)}</small>` : ""}</span>`;

    const envFormatter = (cell) => {
        return String(cell.getValue() || "")
            .split(",")
            .map((env) => env.trim())
            .filter(Boolean)
            .map((env) => `<span class="el-env el-env--${env === "production" ? "prod" : "other"}">${escapeHtml(env)}</span>`)
            .join(" ");
    };

    const columns = () => {
        if (state.mode === "groups") {
            return [
                { title: "Level", field: "level", width: 108, formatter: (cell) => levelPill(cell.getValue()) },
                { title: "What happened", field: "message", minWidth: 320, headerSort: false, cssClass: "el-cell-wrap", formatter: messageFormatter },
                {
                    title: "Count",
                    field: "count",
                    width: 88,
                    hozAlign: "right",
                    formatter: (cell) => `<span class="el-count">×${Number(cell.getValue()).toLocaleString()}</span>`,
                },
                {
                    title: "Last seen",
                    field: "last_seen",
                    width: 168,
                    formatter: (cell) => {
                        const data = cell.getData();
                        return whenFormatter(data.last_seen_ago, data.last_seen, data.first_seen !== data.last_seen ? data.first_seen : "");
                    },
                },
                { title: "Env", field: "envs", width: 118, headerSort: false, cssClass: "el-cell-wrap", formatter: envFormatter },
                {
                    title: "",
                    field: "fp",
                    width: 96,
                    headerSort: false,
                    hozAlign: "right",
                    formatter: () => `<span class="el-row-actions">
                            <button type="button" class="el-icon-btn" data-action="open" title="Open the latest occurrence">${iconSvg(Eye)}</button>
                            <button type="button" class="el-icon-btn" data-action="occurrences" title="List every occurrence">${iconSvg(List)}</button>
                        </span>`,
                },
            ];
        }

        return [
            { title: "Time", field: "time", width: 160, formatter: (cell) => whenFormatter(cell.getData().ago, cell.getValue(), "") },
            { title: "Level", field: "level", width: 108, formatter: (cell) => levelPill(cell.getValue()) },
            { title: "What happened", field: "message", minWidth: 320, headerSort: false, cssClass: "el-cell-wrap", formatter: messageFormatter },
            {
                title: "User",
                field: "user_name",
                width: 140,
                headerSort: false,
                cssClass: "el-cell-wrap",
                formatter: (cell) => {
                    const data = cell.getData();
                    if (!data.user_id) {
                        return '<span class="el-muted">—</span>';
                    }
                    if (!data.user_name) {
                        return `<span class="el-user el-muted" title="${escapeHtml(NOT_STAFF)}">#${escapeHtml(data.user_id)}</span>`;
                    }
                    return `<span class="el-user">${escapeHtml(data.user_name)}<small>#${escapeHtml(data.user_id)}</small></span>`;
                },
            },
            { title: "Env", field: "env", width: 112, formatter: envFormatter },
            {
                title: "Seen",
                field: "occurrences",
                width: 78,
                headerSort: false,
                hozAlign: "right",
                formatter: (cell) => {
                    const count = Number(cell.getValue());
                    if (count < 2 || state.fp) {
                        return `<span class="el-muted">×${count}</span>`;
                    }
                    return `<button type="button" class="el-count el-count--link" data-action="occurrences" title="List every occurrence">×${count.toLocaleString()}</button>`;
                },
            },
            {
                title: "",
                field: "offset",
                width: 62,
                headerSort: false,
                hozAlign: "right",
                formatter: () => `<button type="button" class="el-icon-btn" data-action="open" title="Open">${iconSvg(Eye)}</button>`,
            },
        ];
    };

    const params = () => ({
        file: state.file,
        mode: state.mode,
        levels: Array.from(state.levels).join(","),
        env: state.env,
        q: state.q,
        from: state.from,
        to: state.to,
        fp: state.fp,
    });

    const buildTable = () => {
        let initialSort = [];
        let pageSize = 25;

        if (table) {
            if (table.__elMode === state.mode) {
                initialSort = table.getSorters().map((sorter) => ({ column: sorter.field, dir: sorter.dir }));
            }
            pageSize = table.getPageSize() || pageSize;
            table.destroy();
        }

        table = new Tabulator("#errorLogTable", {
            ajaxURL: route("error.logs.list"),
            ajaxParams: params(),
            ajaxFiltering: true,
            ajaxSorting: true,
            pagination: "remote",
            paginationSize: pageSize,
            paginationSizeSelector: [25, 50, 100, 200],
            layout: "fitColumns",
            responsiveLayout: false,
            initialSort,
            placeholder: state.fp || state.q || state.levels.size || state.env || state.from || state.to ? "Nothing matches these filters" : "This log is empty",
            columns: columns(),
            ajaxResponse(url, sent, response) {
                renderFacets(response.facets);
                return response;
            },
            rowClick(event, row) {
                const action = event.target.closest("[data-action]");
                const data = row.getData();

                if (action && action.dataset.action === "occurrences") {
                    showOccurrences(data.fp, data.message);
                    return;
                }
                openEntry(data.offset);
            },
            renderComplete() {
                refreshIcons();
            },
        });
        table.__elMode = state.mode;
    };

    /* ---------------------------------------------------------------- *
     * View mode, filters, the "every occurrence of" scope.
     * ---------------------------------------------------------------- */
    const scope = document.getElementById("elScope");

    const setMode = (mode) => {
        state.mode = mode;
        document.querySelectorAll(".el-mode__btn").forEach((button) => {
            const active = button.dataset.mode === mode;
            button.classList.toggle("is-active", active);
            button.setAttribute("aria-pressed", active ? "true" : "false");
        });
    };

    const setScope = (fp, label) => {
        state.fp = fp || "";
        state.fpLabel = label || "";
        scope.hidden = !state.fp;
        document.getElementById("elScopeText").textContent = state.fpLabel.length > 140 ? `${state.fpLabel.slice(0, 140)}…` : state.fpLabel;
    };

    const showOccurrences = (fp, label) => {
        setScope(fp, label);
        setMode("entries");
        buildTable();
    };

    document.querySelectorAll(".el-mode__btn").forEach((button) => {
        button.addEventListener("click", () => {
            if (state.mode === button.dataset.mode) {
                return;
            }
            if (button.dataset.mode === "groups") {
                setScope("", "");
            }
            setMode(button.dataset.mode);
            buildTable();
        });
    });

    document.getElementById("elScopeClear").addEventListener("click", () => {
        setScope("", "");
        setMode("groups");
        buildTable();
    });

    document.getElementById("elFilterForm").addEventListener("submit", (event) => {
        event.preventDefault();
        state.q = document.getElementById("elQuery").value.trim();
        state.env = envSelect.value;
        state.from = document.getElementById("elFrom").value;
        state.to = document.getElementById("elTo").value;
        buildTable();
    });

    envSelect.addEventListener("change", () => {
        state.env = envSelect.value;
        buildTable();
    });

    document.getElementById("elReset").addEventListener("click", () => {
        document.getElementById("elQuery").value = "";
        document.getElementById("elFrom").value = "";
        document.getElementById("elTo").value = "";
        envSelect.value = "";
        Object.assign(state, { q: "", env: "", from: "", to: "" });
        state.levels.clear();
        setScope("", "");
        buildTable();
    });

    document.getElementById("elFile").addEventListener("change", async (event) => {
        state.file = event.target.value;
        state.env = "";
        state.levels.clear();
        setScope("", "");
        document.getElementById("elDownload").href = route("error.logs.download", { file: state.file });
        await sync(state.file);
        buildTable();
    });

    document.getElementById("elRefresh").addEventListener("click", async (event) => {
        const button = event.currentTarget;
        button.disabled = true;
        try {
            await sync(state.file);
            buildTable();
        } finally {
            button.disabled = false;
        }
    });

    /* ---------------------------------------------------------------- *
     * One entry: summary, stack trace with framework frames folded,
     * context and the raw text.
     * ---------------------------------------------------------------- */
    const frameHtml = (frame) => {
        let location = "";
        if (frame.file) {
            const [dir, base] = splitPath(frame.file);
            location = `<code class="el-frame__file">${escapeHtml(dir)}<strong>${escapeHtml(base)}</strong>:${escapeHtml(frame.line)}</code>`;
        }
        const view = frame.view ? `<span class="el-frame__view"><i data-lucide="corner-down-right"></i>${escapeHtml(frame.view)}</span>` : "";

        return `<li class="el-frame el-frame--${escapeHtml(frame.kind)}">
                <span class="el-frame__n">#${escapeHtml(frame.n)}</span>
                <div class="el-frame__body">${location}${view}<code class="el-frame__call">${escapeHtml(frame.call)}</code></div>
            </li>`;
    };

    const framesHtml = (frames) => {
        let html = "";
        let run = [];
        const flush = () => {
            if (run.length === 0) {
                return;
            }
            if (run.length <= 2) {
                html += run.map(frameHtml).join("");
            } else {
                html += `<li class="el-fold">
                        <button type="button" data-action="unfold"><i data-lucide="chevrons-up-down"></i><span>${run.length} framework frames</span></button>
                        <ol class="el-frames el-frames--folded" hidden>${run.map(frameHtml).join("")}</ol>
                    </li>`;
            }
            run = [];
        };

        frames.forEach((frame) => {
            if (frame.kind === "app" || frame.kind === "view") {
                flush();
                html += frameHtml(frame);
            } else {
                run.push(frame);
            }
        });
        flush();

        return `<ol class="el-frames">${html}</ol>`;
    };

    const exceptionHtml = (exception, index) => {
        const location = exception.file
            ? `<div class="el-exc__loc"><i data-lucide="file-code-2"></i><code>${escapeHtml(exception.file)}:${escapeHtml(exception.line)}</code>${
                  exception.view ? `<span class="el-frame__view"><i data-lucide="corner-down-right"></i>${escapeHtml(exception.view)}</span>` : ""
              }</div>`
            : "";
        const code = exception.code && exception.code !== "0" ? `<span class="el-exc__code">code ${escapeHtml(exception.code)}</span>` : "";

        return `<section class="el-exc ${index > 0 ? "el-exc--previous" : ""}">
                <div class="el-exc__head">
                    <span class="el-exc__label">${index === 0 ? "Exception" : "Previous exception"}</span>
                    <code class="el-exc__class">${escapeHtml(exception.class || "Unparsed")}</code>${code}
                </div>
                <p class="el-exc__message">${escapeHtml(exception.message)}</p>
                ${location}
                ${exception.frames.length ? framesHtml(exception.frames) : '<p class="el-muted">No stack trace in this entry.</p>'}
            </section>`;
    };

    const fact = (label, value) => (value ? `<div><dt>${label}</dt><dd>${value}</dd></div>` : "");

    const renderEntry = (entry) => {
        document.getElementById("elEntryTitle").innerHTML = `${levelPill(entry.level)}<span class="el-modal__time">${escapeHtml(entry.time)}</span><span class="el-env el-env--${
            entry.env === "production" ? "prod" : "other"
        }">${escapeHtml(entry.env)}</span>`;
        document.getElementById("elEntryDownload").href = entry.download_url;

        const group = entry.group || {};
        const seen =
            group.count > 1
                ? `<strong>×${Number(group.count).toLocaleString()}</strong> in this file · first ${escapeHtml(group.first_seen)} · last ${escapeHtml(group.last_seen)}
                   <button type="button" class="el-link" data-action="occurrences">Show every occurrence</button>`
                : "Only once in this file";
        let user = "";
        if (entry.user_id) {
            user = entry.user_name
                ? `${escapeHtml(entry.user_name)} <span class="el-muted">#${escapeHtml(entry.user_id)}</span>`
                : `#${escapeHtml(entry.user_id)} <span class="el-muted">· ${escapeHtml(NOT_STAFF)}</span>`;
        }

        const hasTrace = entry.exceptions.length > 0;
        const tabs = [];
        if (hasTrace) {
            const frameCount = entry.exceptions.reduce((sum, exception) => sum + exception.frames.length, 0);
            tabs.push({ id: "trace", label: `Stack trace <span>${frameCount}</span>` });
        }
        tabs.push({ id: "context", label: `Context <span>${entry.context.length}</span>` });
        tabs.push({ id: "raw", label: "Raw" });
        const first = tabs[0].id;

        const contextHtml = entry.context.length
            ? `<table class="el-context"><tbody>${entry.context
                  .map((row) => `<tr><th>${escapeHtml(row.key)}</th><td><pre>${escapeHtml(row.value)}</pre></td></tr>`)
                  .join("")}</tbody></table>`
            : '<p class="el-muted">This entry has no context.</p>';

        entryBody.innerHTML = `
            <section class="el-detail__summary">
                <div class="el-detail__message">
                    <p>${escapeHtml(entry.message) || '<em class="el-muted">(no message)</em>'}</p>
                    <button type="button" class="el-icon-btn" data-action="copy-message" title="Copy message"><i data-lucide="copy"></i></button>
                </div>
                <dl class="el-facts">
                    ${fact("Where", entry.where ? `<code>${escapeHtml(entry.where)}</code>` : "")}
                    ${fact("Exception", entry.class ? `<code>${escapeHtml(entry.class)}</code>` : "")}
                    ${fact("User", user)}
                    ${fact("When", `${escapeHtml(entry.time)} <span class="el-muted">· ${escapeHtml(entry.ago)}</span>`)}
                    ${fact("Seen", seen)}
                    ${fact("Size", `${escapeHtml(entry.size_human)}`)}
                </dl>
            </section>
            <div class="el-tabs">
                ${tabs
                    .map(
                        // No role="tab": tw-starter's tab.js claims every [role=tab] on the page.
                        (tab) =>
                            `<button type="button" class="el-tab ${tab.id === first ? "is-active" : ""}" data-tab="${tab.id}" aria-pressed="${tab.id === first}">${tab.label}</button>`
                    )
                    .join("")}
                ${hasTrace ? '<button type="button" class="el-link el-tabs__extra" data-action="unfold-all">Show framework frames</button>' : ""}
            </div>
            ${hasTrace ? `<div class="el-panel" data-panel="trace">${entry.exceptions.map(exceptionHtml).join("")}</div>` : ""}
            <div class="el-panel" data-panel="context" ${first === "context" ? "" : "hidden"}>${contextHtml}</div>
            <div class="el-panel" data-panel="raw" hidden>
                <div class="el-raw__bar">
                    <span>${entry.raw_truncated ? `Showing the first ${escapeHtml(Math.round(entry.raw.length / 1024))} KB of ${escapeHtml(entry.size_human)}. Download the entry for all of it.` : escapeHtml(entry.size_human)}</span>
                    <button type="button" class="ss-btn ss-btn--light ss-btn--tool" data-action="copy-raw"><i data-lucide="copy"></i> Copy</button>
                </div>
                <pre class="el-raw">${escapeHtml(entry.raw)}</pre>
            </div>`;

        entryBody.__entry = entry;
        refreshIcons();
    };

    const openEntry = async (offset) => {
        entryBody.__entry = null;
        document.getElementById("elEntryTitle").textContent = "Log entry";
        entryBody.innerHTML = '<div class="el-loading"><span></span>Opening entry…</div>';
        modal.show();

        try {
            const { data } = await axios.get(route("error.logs.entry"), { params: { file: state.file, offset } });
            renderEntry(data);
        } catch (error) {
            const message = error.response?.data?.message || "This entry could not be opened.";
            entryBody.innerHTML = `<div class="el-empty el-empty--inline"><i data-lucide="alert-triangle"></i><p>${escapeHtml(message)}</p></div>`;
            refreshIcons();
        }
    };

    entryBody.addEventListener("click", (event) => {
        const tab = event.target.closest(".el-tab");
        if (tab) {
            entryBody.querySelectorAll(".el-tab").forEach((item) => {
                item.classList.toggle("is-active", item === tab);
                item.setAttribute("aria-pressed", item === tab ? "true" : "false");
            });
            entryBody.querySelectorAll(".el-panel").forEach((panel) => {
                panel.hidden = panel.dataset.panel !== tab.dataset.tab;
            });
            const extra = entryBody.querySelector(".el-tabs__extra");
            if (extra) {
                extra.hidden = tab.dataset.tab !== "trace";
            }
            return;
        }

        const action = event.target.closest("[data-action]");
        if (!action) {
            return;
        }

        const entry = entryBody.__entry;
        if (action.dataset.action === "unfold") {
            const list = action.nextElementSibling;
            list.hidden = !list.hidden;
            action.classList.toggle("is-open", !list.hidden);
        } else if (action.dataset.action === "unfold-all") {
            const folds = entryBody.querySelectorAll(".el-frames--folded");
            const open = Array.from(folds).some((list) => list.hidden);
            folds.forEach((list) => {
                list.hidden = !open;
                list.previousElementSibling.classList.toggle("is-open", open);
            });
            action.textContent = open ? "Hide framework frames" : "Show framework frames";
        } else if (action.dataset.action === "copy-message" && entry) {
            copyText(entry.message, null);
            action.classList.add("is-done");
            setTimeout(() => action.classList.remove("is-done"), 1200);
        } else if (action.dataset.action === "copy-raw" && entry) {
            copyText(entry.raw, action);
        } else if (action.dataset.action === "occurrences" && entry) {
            modal.hide();
            showOccurrences(entry.fp, entry.message);
        }
    });

    window.addEventListener("resize", () => {
        if (table) {
            table.redraw();
        }
    });

    (async () => {
        await sync(state.file);
        buildTable();
    })();
})();
