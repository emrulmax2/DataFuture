/*
 * Policy Assessments: the Role part of the two Assign modals (Assignments page
 * and the employee profile tab), and how a role is shown in the HR lists.
 * Imported by the page scripts; not a Vite input.
 *
 * HR assigns by role. The modal posts `role_ids[]` from the role picker
 * (partials/results-role-picker.blade.php: checkbox cards, or one searchable
 * multi-select when there are many roles) together with `policy_ids[]` from the
 * policy picker. Ticking a role adds its policies to the policy picker; HR can
 * still add or remove policies, and what is left in the picker is exactly what
 * is assigned.
 *
 * Unticking a role takes away the policies that role put in, but never one HR
 * added by hand and never one another ticked role also provides. To tell them
 * apart the picker remembers which policies HR added by hand (`manual`): a
 * policy that is added or removed while a role is being ticked or unticked is
 * the role's doing, anything else is HR's.
 *
 * Which policies a role covers, and how many of them are not ready for each
 * exam level, come from the server (data-roles on the picker, from
 * PolicyAssignmentController::assignOptions()); nothing is worked out here.
 *
 * Role names are typed by HR: they only ever go in through textContent,
 * TomSelect's escaping or escapeHtml().
 */
import { escapeHtml, isLevel } from "./levels";
import { examName } from "./assign-level-picker";

const DEFAULT_LEVEL = "beginner";

const plural = (count, one, many) => `${count} ${count === 1 ? one : many}`;

/** [{ id, name, policy_ids, not_ready }] from the picker's data-roles attribute ([] when missing or broken). */
const readRoles = (box) => {
    try {
        const parsed = JSON.parse(box.getAttribute("data-roles") || "[]");

        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
};

/**
 * @param {HTMLFormElement} form         the Assign form (holds the role picker)
 * @param {TomSelect}       policySelect the policies picker
 * @param {TomSelect|null}  roleSelect   the roles multi-select, when the picker is a select (many roles)
 * @returns {{ showLevel: (level: string) => void, reset: () => void, ticked: () => string[] }}
 */
export const initRolePicker = (form, policySelect, roleSelect = null) => {
    const box = form ? form.querySelector("[data-role-picker]") : null;

    if (!box || !policySelect) {
        return { showLevel: () => {}, reset: () => {}, ticked: () => [] };
    }

    /* role id => { id, name, policyIds, notReady } */
    const roles = new Map();

    readRoles(box).forEach((role) => {
        const id = String(role?.id ?? "");

        if (id !== "") {
            roles.set(id, {
                id,
                name: String(role.name ?? ""),
                policyIds: (Array.isArray(role.policy_ids) ? role.policy_ids : []).map(String),
                notReady: role.not_ready && typeof role.not_ready === "object" ? role.not_ready : {},
            });
        }
    });

    const countNode = form.querySelector("[data-policy-count]");
    /* Policies HR put in the picker by hand: a role never takes these out. */
    const manual = new Set();
    /* True while this module is changing the pickers itself. */
    let syncing = false;

    const boxes = () => [...box.querySelectorAll('input[name="role_ids[]"]')];

    const ticked = () => {
        return roleSelect
            ? roleSelect.items.map(String)
            : boxes().filter((input) => input.checked).map((input) => String(input.value));
    };

    /* A message the server left under a picker is out of date once HR changes that picker. */
    const clearError = (field) => {
        const node = form.querySelector(`.error-${field}`);

        if (node) {
            node.textContent = "";
        }
    };

    /* One "change" for the whole batch, so the level note and the count are worked out once. */
    const announce = () => policySelect.trigger("change", policySelect.getValue());

    const sync = (work) => {
        syncing = true;

        try {
            work();
        } finally {
            syncing = false;
        }

        announce();
    };

    const tick = (id) => {
        const role = roles.get(String(id));

        if (!role) {
            return;
        }

        clearError("role_ids");
        sync(() => {
            role.policyIds.forEach((policyId) => {
                if (policySelect.options[policyId] && !policySelect.items.includes(policyId)) {
                    policySelect.addItem(policyId, true);
                }
            });
        });
    };

    const untick = (id) => {
        const role = roles.get(String(id));

        if (!role) {
            return;
        }

        clearError("role_ids");

        /* What the roles that are still ticked provide. */
        const kept = new Set();

        ticked().forEach((other) => {
            if (other !== role.id && roles.has(other)) {
                roles.get(other).policyIds.forEach((policyId) => kept.add(policyId));
            }
        });

        sync(() => {
            role.policyIds.forEach((policyId) => {
                if (policySelect.items.includes(policyId) && !manual.has(policyId) && !kept.has(policyId)) {
                    policySelect.removeItem(policyId, true);
                }
            });
        });
    };

    /* "3 not ready for an Expert exam", or "" when every policy of the role is ready. */
    const readyText = (role, level) => {
        const short = Number(role.notReady[level] || 0);

        return short > 0 ? `${short} not ready for ${examName(level)}` : "";
    };

    /* Same text as the Blade partial writes for the multi-select options. */
    const optionLabel = (role, level) => {
        const hint = readyText(role, level);
        const count = role.policyIds.length ? plural(role.policyIds.length, "policy", "policies") : "no active policies";

        return `${role.name} — ${count}${hint ? ` (${hint})` : ""}`;
    };

    const showLevel = (next) => {
        const level = isLevel(next) ? next : DEFAULT_LEVEL;

        box.querySelectorAll("[data-role-ready]").forEach((node) => {
            const role = roles.get(String(node.getAttribute("data-role-ready")));
            const text = role ? readyText(role, level) : "";

            node.textContent = text;
            node.hidden = text === "";
        });

        if (roleSelect) {
            roles.forEach((role) => {
                const data = roleSelect.options[role.id];
                const text = optionLabel(role, level);

                if (data && data.text !== text) {
                    roleSelect.updateOption(role.id, { ...data, text });
                }
            });
        }
    };

    /* "14 policies chosen", and how many of them HR added by hand once a role is ticked. */
    const refreshCount = () => {
        if (!countNode) {
            return;
        }

        const total = policySelect.items.length;
        const byHand = ticked().length ? policySelect.items.filter((policyId) => manual.has(String(policyId))).length : 0;

        countNode.textContent = total
            ? `${plural(total, "policy", "policies")} chosen${byHand ? ` · ${byHand} added by hand` : ""}`
            : "";
        countNode.hidden = total === 0;
    };

    const reset = () => {
        syncing = true;

        try {
            boxes().forEach((input) => {
                input.checked = false;
            });

            if (roleSelect) {
                roleSelect.clear(true);
            }

            policySelect.clear(true);
        } finally {
            syncing = false;
        }

        manual.clear();
        announce();
    };

    policySelect.on("item_add", (value) => {
        if (!syncing) {
            manual.add(String(value));
        }
    });
    policySelect.on("item_remove", (value) => {
        if (!syncing) {
            manual.delete(String(value));
        }
    });
    policySelect.on("change", () => {
        refreshCount();

        if (policySelect.items.length) {
            clearError("policy_ids");
        }
    });

    if (roleSelect) {
        roleSelect.on("item_add", (value) => {
            if (!syncing) {
                tick(value);
            }
        });
        roleSelect.on("item_remove", (value) => {
            if (!syncing) {
                untick(value);
            }
        });
    } else {
        box.addEventListener("change", (event) => {
            const target = event.target;

            if (target && target.name === "role_ids[]") {
                (target.checked ? tick : untick)(target.value);
            }
        });
    }

    refreshCount();

    return { showLevel, reset, ticked };
};

/**
 * The role line of the "Policies assigned" message: "Role  Lecturer" or
 * "Roles  Lecturer  Finance". `roles` comes from the server's reply
 * ([{ id, name }]); "" when no role was used.
 */
export const assignedRolesHtml = (roles) => {
    const names = (Array.isArray(roles) ? roles : [])
        .map((role) => String(role?.name ?? ""))
        .filter((name) => name !== "");

    if (!names.length) {
        return "";
    }

    return `<p class="pa-result-roles"><span>${names.length === 1 ? "Role" : "Roles"}</span>${names.map((name) => `<em class="pa-tag pa-tag--role">${escapeHtml(name)}</em>`).join("")}</p>`;
};

/**
 * The small role tag of a list row or an assignment card: the role the
 * assignment was made through ("" when the policy was picked by hand). An
 * archived role arrives as "<name> (archived)" and is drawn muted. A long name
 * is cut short by the stylesheet, so the title carries it in full.
 */
export const roleTagHtml = (data) => {
    const name = String(data?.role_name ?? "");

    if (name === "") {
        return "";
    }

    return `<em class="pa-tag pa-tag--role${Number(data.role_archived || 0) ? " is-archived" : ""}" title="Role: ${escapeHtml(name)}">${escapeHtml(name)}</em>`;
};
