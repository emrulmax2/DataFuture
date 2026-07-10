import { createIcons, icons } from "lucide";
import Litepicker from "litepicker";

(function () {
    const form = document.getElementById("employeePrivilegeForm");
    const rangePickerElement = document.getElementById("rangepicker");
    const modalApi = window.tailwind?.Modal;
    const successModalElement = document.querySelector("#successModal");
    const warningModalElement = document.querySelector("#warningModal");
    const successModal = successModalElement && modalApi ? modalApi.getOrCreateInstance(successModalElement) : null;
    const warningModal = warningModalElement && modalApi ? modalApi.getOrCreateInstance(warningModalElement) : null;

    const refreshIcons = () => createIcons({ icons });

    if (rangePickerElement) {
        new Litepicker({
            element: rangePickerElement,
            autoApply: true,
            singleMode: false,
            numberOfColumns: 2,
            numberOfMonths: 2,
            showWeekNumbers: false,
            format: "DD-MM-YYYY",
            dropdowns: {
                minYear: 1900,
                maxYear: 2050,
                months: true,
                years: true,
            },
        });
    }

    const setSubmitState = (isDisabled) => {
        $("#employeePrivilegeForm button[type=\"submit\"], button[type=\"submit\"][form=\"employeePrivilegeForm\"]").each(function () {
            $(this).prop("disabled", isDisabled);
        });
    };

    $("#employeePrivilegeForm").on("submit", function (e) {
        e.preventDefault();

        if (!form) {
            return;
        }

        setSubmitState(true);

        axios({
            method: "post",
            url: route("employee.privilege.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content") },
        }).then((response) => {
            setSubmitState(false);

            if (response.status === 200) {
                $("#successModal .successModalTitle").html("Congratulations!");
                $("#successModal .successModalDesc").html("Employee privilege successfully stored into the DB.");
                successModal?.show();

                setTimeout(function () {
                    successModal?.hide();
                    window.location.reload();
                }, 1000);
            }
        }).catch(() => {
            setSubmitState(false);
            $("#warningModal .warningModalTitle").html("Unable to save");
            $("#warningModal .warningModalDesc").html("Please check the privilege form and try again.");
            warningModal?.show();
        });
    });

    /* Login Date Range Toggle Start */
    $("#permission_remote_access_1").on("change", function () {
        $("#dateRangeWrap").fadeOut(function () {
            $(".rangepicker", this).val("").removeAttr("required");
        });

        if ($(this).prop("checked")) {
            $(this).siblings(".ra_status_label").text("Allowed");
            $("#inRangeSwitch").fadeIn("fast", function () {
                $("#permission_remote_access_2").prop("checked", false);
            });
        } else {
            $(this).siblings(".ra_status_label").text("Not Allowed");
            $("#inRangeSwitch").fadeOut("fast", function () {
                $("#permission_remote_access_2").prop("checked", false);
            });
        }
    });

    $("#permission_remote_access_2").on("change", function () {
        if ($(this).prop("checked")) {
            $("#dateRangeWrap").fadeIn(function () {
                $(".rangepicker", this).val("").attr("required", "required");
            });
        } else {
            $("#dateRangeWrap").fadeOut(function () {
                $(".rangepicker", this).val("").removeAttr("required");
            });
        }
    });
    /* Login Date Range Toggle End */

    /* Internal Links Section Start */
    $(".parentPermissionItem").on("change", function () {
        const $theChildWrap = $(this).parent(".form-check").siblings(".childrenPermissionWrap");

        if ($theChildWrap.length > 0) {
            if ($(this).prop("checked")) {
                $("input[type=\"checkbox\"]", $theChildWrap).removeAttr("disabled").prop("checked", false);
            } else {
                $("input[type=\"checkbox\"]", $theChildWrap).prop("checked", false).attr("disabled", "disabled");
            }
        }
    });
    /* Internal Links Section End */

    /* Accounts Section Start */
    $("#permission_acc_privilege_1").on("change", function () {
        if ($(this).prop("checked")) {
            $(".accountsUserTypeWrap").fadeIn("fast", function () {
                $("#permission_acc_privilege_2").val("");
            });
        } else {
            $(".accountsUserTypeWrap").fadeOut("fast", function () {
                $("#permission_acc_privilege_2").val("");
            });
        }
    });
    /* Accounts Section End */

    const initPrivilegePrototypeUi = () => {
        if (!form) {
            refreshIcons();
            return;
        }

        const sections = form.querySelector(".ep-privilege-sections");
        const railList = document.querySelector("#employeePrivilegeRail .ep-privilege-rail__list");
        const summary = document.getElementById("privilegeGlobalSummary");
        const searchInput = document.getElementById("privilegeSearchInput");
        const expandAll = document.getElementById("privilegeExpandAll");
        const collapseAll = document.getElementById("privilegeCollapseAll");
        const printButton = document.getElementById("privilegePrint");
        const printSummary = document.getElementById("privilegePrintSummary");
        const printCards = document.getElementById("privilegePrintCards");
        const printRoot = document.getElementById("privilegePrintRoot");
        const privilegePage = document.querySelector(".ep-privilege-page");

        if (!sections || !railList) {
            refreshIcons();
            return;
        }

        const cards = Array.from(sections.children).filter((child) => {
            return child.classList.contains("intro-y")
                && child.classList.contains("box")
                && !child.classList.contains("magicBox");
        });
        const noResults = document.createElement("div");
        noResults.className = "ep-privilege-empty";
        noResults.hidden = true;
        sections.appendChild(noResults);

        const slugify = (value) => value.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "") || "group";
        const normalise = (value) => (value || "").toString().toLowerCase().trim();
        const getCheckboxes = (card) => Array.from(card.querySelectorAll("input[type=\"checkbox\"][name^=\"permission\"]"));
        const getCounts = (card) => {
            const inputs = getCheckboxes(card);
            return {
                on: inputs.filter((input) => input.checked).length,
                total: inputs.length,
            };
        };

        const dispatchChange = (input) => {
            input.dispatchEvent(new Event("change", { bubbles: true }));
        };

        const setCheckbox = (input, checked) => {
            if (checked && input.disabled) {
                input.disabled = false;
            }

            input.checked = checked;
            dispatchChange(input);
        };

        const cardData = cards.map((card, index) => {
            const oldHeader = card.firstElementChild;
            const oldSaveButton = oldHeader?.querySelector("button[type=\"submit\"]");
            const title = oldHeader?.querySelector(".font-medium.text-base")?.textContent.trim() || `Privilege Group ${index + 1}`;
            const body = Array.from(card.children).find((child) => child.classList.contains("intro-y") && child !== oldHeader);
            const id = `privilege-${slugify(title)}-${index + 1}`;

            card.id = id;
            card.classList.add("ep-privilege-card");
            body?.classList.add("ep-privilege-card__body");

            card.querySelectorAll(".form-check.form-switch").forEach((row) => {
                const checkbox = row.querySelector("input[type=\"checkbox\"]");

                row.classList.add("ep-privilege-switch");

                if (checkbox && !row.querySelector(".ep-privilege-print-mark")) {
                    const printMark = document.createElement("span");
                    printMark.className = "ep-privilege-print-mark";
                    printMark.setAttribute("aria-hidden", "true");
                    row.insertBefore(printMark, checkbox);
                }

                if (!checkbox) {
                    row.classList.add("ep-privilege-switch--label");
                }
            });

            card.querySelectorAll(".childrenPermissionWrap").forEach((wrap) => {
                wrap.classList.add("ep-privilege-children");
            });

            const header = document.createElement("div");
            header.className = "ep-privilege-card__head";

            const collapse = document.createElement("button");
            collapse.type = "button";
            collapse.className = "ep-privilege-card__collapse";
            collapse.setAttribute("aria-expanded", "true");
            collapse.setAttribute("aria-controls", id);
            collapse.innerHTML = '<i data-lucide="chevron-down" class="w-4 h-4"></i>';

            const icon = document.createElement("div");
            icon.className = "ep-privilege-card__icon";
            icon.innerHTML = '<i data-lucide="lock-keyhole" class="w-4 h-4"></i>';

            const titleWrap = document.createElement("div");
            titleWrap.className = "ep-privilege-card__title-wrap";
            titleWrap.innerHTML = `
                <h2 class="ep-privilege-card__title">${title}</h2>
                <div class="ep-privilege-card__meta"></div>
            `;

            const actions = document.createElement("div");
            actions.className = "ep-privilege-card__actions";

            const toggleAll = document.createElement("button");
            toggleAll.type = "button";
            toggleAll.className = "ep-privilege-card__toggle-all";

            const saveButton = oldSaveButton || document.createElement("button");
            saveButton.type = "submit";
            saveButton.className = "ep-privilege-card__save";
            saveButton.innerHTML = '<i data-lucide="save" class="w-3.5 h-3.5"></i><span>Save</span>';

            actions.append(toggleAll, saveButton);
            header.append(collapse, icon, titleWrap, actions);
            oldHeader?.replaceWith(header);

            const railItem = document.createElement("a");
            railItem.className = "ep-privilege-rail__link";
            railItem.href = `#${id}`;
            railItem.innerHTML = `
                <span class="ep-privilege-rail__text">${title}</span>
                <span class="ep-privilege-rail__badge">0/0</span>
            `;
            railList.appendChild(railItem);

            collapse.addEventListener("click", () => {
                const isCollapsed = card.classList.toggle("is-collapsed");
                collapse.setAttribute("aria-expanded", String(!isCollapsed));
            });

            toggleAll.addEventListener("click", () => {
                const counts = getCounts(card);
                const shouldEnable = counts.total > 0 && counts.on !== counts.total;
                const parents = getCheckboxes(card).filter((input) => input.classList.contains("parentPermissionItem"));
                const childrenAndSingles = getCheckboxes(card).filter((input) => !input.classList.contains("parentPermissionItem"));

                if (shouldEnable) {
                    parents.forEach((input) => setCheckbox(input, true));
                    childrenAndSingles.forEach((input) => setCheckbox(input, true));
                } else {
                    childrenAndSingles.forEach((input) => setCheckbox(input, false));
                    parents.forEach((input) => setCheckbox(input, false));
                }

                window.setTimeout(updateCounts, 0);
            });

            return {
                card,
                title,
                meta: titleWrap.querySelector(".ep-privilege-card__meta"),
                toggleAll,
                railItem,
                railBadge: railItem.querySelector(".ep-privilege-rail__badge"),
            };
        });

        const setCardExpanded = (item, isExpanded) => {
            item.card.classList.toggle("is-collapsed", !isExpanded);
            item.card.querySelector(".ep-privilege-card__collapse")?.setAttribute("aria-expanded", String(isExpanded));
        };

        const updatePrintMarks = () => {
            form.querySelectorAll(".ep-privilege-print-mark").forEach((mark) => {
                const input = mark.parentElement?.querySelector("input[type=\"checkbox\"]");

                if (!input) {
                    return;
                }

                mark.textContent = input.checked ? "\u2713" : "\u2717";
                mark.classList.toggle("is-enabled", input.checked);
                mark.classList.toggle("is-disabled", !input.checked);
            });
        };

        const createPrintElement = (tag, className, text) => {
            const element = document.createElement(tag);

            if (className) {
                element.className = className;
            }

            if (typeof text !== "undefined" && text !== null) {
                element.textContent = text;
            }

            return element;
        };

        const getRowLabel = (row) => {
            return row.querySelector(".form-check-label")?.textContent.replace(/\s+/g, " ").trim() || "";
        };

        const formatControlLabel = (control) => {
            const name = control.getAttribute("name") || "";
            const finalName = name.match(/\[([^\]]+)\]$/)?.[1] || name || "Value";

            return finalName.replace(/_/g, " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
        };

        const createPrintMark = (isEnabled) => {
            const mark = createPrintElement("span", "print-mark", isEnabled ? "\u2713" : "\u2717");
            mark.classList.add(isEnabled ? "is-enabled" : "is-disabled");
            return mark;
        };

        const createPrintToggleRow = (label, isEnabled, options = {}) => {
            if (!label) {
                return null;
            }

            const rowClasses = [
                "tgl-row",
                options.isChild ? "tgl-row--child" : "",
                options.isParent ? "tgl-row--parent" : "",
                isEnabled ? "is-enabled" : "is-disabled",
            ].filter(Boolean).join(" ");
            const row = createPrintElement("div", rowClasses);
            const text = createPrintElement("span", "tgl-row__label", label);

            row.append(createPrintMark(isEnabled), text);
            return row;
        };

        const createPrintGroupTitle = (label) => {
            if (!label) {
                return null;
            }

            return createPrintElement("div", "ep-privilege-print-group-title", label);
        };

        const createPrintFieldRow = (label, value) => {
            if (!value) {
                return null;
            }

            const row = createPrintElement("div", "tgl-row tgl-row--field");
            row.append(
                createPrintElement("span", "print-mark print-mark--field", "\u2022"),
                createPrintElement("span", "tgl-row__label", `${label}: ${value}`),
            );
            return row;
        };

        const appendPrintChildRows = (wrap, printBlock, shouldIndent) => {
            Array.from(wrap.querySelectorAll(".form-check.form-switch")).forEach((row) => {
                const checkbox = row.querySelector("input[type=\"checkbox\"]");
                const label = getRowLabel(row);

                if (!checkbox || !label) {
                    return;
                }

                const printRow = createPrintToggleRow(label, checkbox.checked, { isChild: shouldIndent });

                if (printRow) {
                    printBlock.appendChild(printRow);
                }
            });
        };

        const appendPrintFieldRows = (sourceBlock, printBlock) => {
            Array.from(sourceBlock.querySelectorAll("select")).forEach((select) => {
                const selectedOption = select.options[select.selectedIndex];
                const value = selectedOption?.value ? selectedOption.textContent.trim() : "";
                const field = createPrintFieldRow(formatControlLabel(select), value);

                if (field) {
                    printBlock.appendChild(field);
                }
            });

            Array.from(sourceBlock.querySelectorAll("input[type=\"text\"]")).forEach((input) => {
                const value = input.value.trim();
                const field = createPrintFieldRow(formatControlLabel(input), value);

                if (field) {
                    printBlock.appendChild(field);
                }
            });
        };

        const buildPrintBlock = (sourceBlock) => {
            const printBlock = createPrintElement("div", "ep-privilege-print-block");
            let parentWasToggle = false;

            Array.from(sourceBlock.children).forEach((child) => {
                if (child.classList.contains("form-check") && child.classList.contains("form-switch")) {
                    const checkbox = child.querySelector("input[type=\"checkbox\"]");
                    const label = getRowLabel(child);

                    if (checkbox) {
                        const row = createPrintToggleRow(label, checkbox.checked, { isParent: true });

                        if (row) {
                            printBlock.appendChild(row);
                            parentWasToggle = true;
                        }
                    } else {
                        const heading = createPrintGroupTitle(label);

                        if (heading) {
                            printBlock.appendChild(heading);
                            parentWasToggle = false;
                        }
                    }

                    return;
                }

                if (child.classList.contains("childrenPermissionWrap")) {
                    appendPrintChildRows(child, printBlock, parentWasToggle);
                }
            });

            appendPrintFieldRows(sourceBlock, printBlock);

            return printBlock.childElementCount > 0 ? printBlock : null;
        };

        const buildPrivilegePrintReport = () => {
            if (!printCards) {
                return;
            }

            const fragment = document.createDocumentFragment();

            cardData.forEach((item) => {
                const counts = getCounts(item.card);
                const card = createPrintElement("section", "priv-card");
                const head = createPrintElement("div", "card-head");
                const titleWrap = createPrintElement("div", "ep-privilege-print-card-title-wrap");
                const title = createPrintElement("h2", "ep-privilege-print-card-title", item.title);
                const meta = createPrintElement("div", "ep-privilege-print-card-meta", `${counts.on} of ${counts.total} enabled`);
                const body = createPrintElement("div", "card-body");
                const blocks = Array.from(item.card.querySelectorAll(".ep-privilege-card__body > .grid > *"));

                titleWrap.append(title, meta);
                head.appendChild(titleWrap);

                blocks.forEach((block) => {
                    const printBlock = buildPrintBlock(block);

                    if (printBlock) {
                        body.appendChild(printBlock);
                    }
                });

                card.append(head, body);
                fragment.appendChild(card);
            });

            printCards.replaceChildren(fragment);
        };

        const updateCounts = () => {
            let total = 0;
            let enabled = 0;

            cardData.forEach((item) => {
                const counts = getCounts(item.card);
                total += counts.total;
                enabled += counts.on;

                item.meta.textContent = `${counts.on} of ${counts.total} enabled`;
                item.toggleAll.textContent = counts.total > 0 && counts.on === counts.total ? "Disable all" : "Enable all";
                item.railBadge.textContent = `${counts.on}/${counts.total}`;
                item.railBadge.classList.toggle("is-active", counts.on > 0);
            });

            if (summary) {
                summary.textContent = `${enabled} of ${total} permissions enabled · ${cardData.length} groups`;
            }

            if (printSummary) {
                printSummary.textContent = `${enabled} of ${total} across ${cardData.length} groups`;
            }

            updatePrintMarks();
        };

        const filterCards = () => {
            const query = normalise(searchInput?.value);
            let visibleCards = 0;

            cardData.forEach((item) => {
                const titleMatch = normalise(item.title).includes(query);
                const blocks = Array.from(item.card.querySelectorAll(".ep-privilege-card__body > .grid > *"));
                let visibleBlocks = 0;

                blocks.forEach((block) => {
                    const matches = !query || titleMatch || normalise(block.textContent).includes(query);
                    block.classList.toggle("is-filtered-out", !matches);

                    if (matches) {
                        visibleBlocks += 1;
                    }
                });

                const isVisible = !query || titleMatch || visibleBlocks > 0;
                item.card.classList.toggle("is-filtered-out", !isVisible);
                item.railItem.classList.toggle("is-filtered-out", !isVisible);

                if (isVisible) {
                    visibleCards += 1;
                }
            });

            noResults.hidden = visibleCards > 0;
            noResults.textContent = query ? `No privileges match "${searchInput.value}".` : "";
        };

        expandAll?.addEventListener("click", () => {
            cardData.forEach((item) => setCardExpanded(item, true));
        });

        collapseAll?.addEventListener("click", () => {
            cardData.forEach((item) => setCardExpanded(item, false));
        });

        let printRestoreState = null;

        const beginPrivilegePrint = () => {
            if (!printRestoreState) {
                printRestoreState = {
                    collapsedCards: cardData.filter((item) => item.card.classList.contains("is-collapsed")).map((item) => item.card.id),
                };
            }

            document.body.classList.add("ep-privilege-printing");
            privilegePage?.classList.add("is-printing");
            printRoot?.removeAttribute("aria-hidden");
            cardData.forEach((item) => setCardExpanded(item, true));
            updateCounts();
            buildPrivilegePrintReport();
        };

        const endPrivilegePrint = () => {
            if (!printRestoreState) {
                return;
            }

            const collapsedCards = new Set(printRestoreState.collapsedCards);
            cardData.forEach((item) => setCardExpanded(item, !collapsedCards.has(item.card.id)));
            document.body.classList.remove("ep-privilege-printing");
            privilegePage?.classList.remove("is-printing");
            printRoot?.setAttribute("aria-hidden", "true");
            printRestoreState = null;
        };

        const printPrivileges = () => {
            beginPrivilegePrint();

            window.setTimeout(() => {
                try {
                    window.print();
                } catch {
                    endPrivilegePrint();
                }
            }, 300);
        };

        window.addEventListener("beforeprint", beginPrivilegePrint);
        window.addEventListener("afterprint", endPrivilegePrint);

        printButton?.addEventListener("click", printPrivileges);
        searchInput?.addEventListener("input", filterCards);

        $("#employeePrivilegeForm input[type=\"checkbox\"], #employeePrivilegeForm select").on("change.privilegeUi", function () {
            window.setTimeout(updateCounts, 0);
        });

        updateCounts();
        filterCards();
        refreshIcons();
    };

    initPrivilegePrototypeUi();
})();
