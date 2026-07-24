import { createIcons, icons } from "lucide";

(function () {
    "use strict";

    const page = document.getElementById("siteSettingsPage");

    if (!page) {
        return;
    }

    const dropdowns = Array.from(page.querySelectorAll("[data-ss-dropdown]"));

    function closeDropdowns(except) {
        dropdowns.forEach((dropdown) => {
            if (dropdown !== except) {
                dropdown.removeAttribute("data-open");
                dropdown.querySelector("[data-ss-dropdown-toggle]")?.setAttribute("aria-expanded", "false");
            }
        });
    }

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector("[data-ss-dropdown-toggle]");

        toggle?.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = dropdown.hasAttribute("data-open");
            closeDropdowns(dropdown);

            if (isOpen) {
                dropdown.removeAttribute("data-open");
                toggle.setAttribute("aria-expanded", "false");
                return;
            }

            dropdown.setAttribute("data-open", "true");
            toggle.setAttribute("aria-expanded", "true");
        });
    });

    document.addEventListener("click", (event) => {
        if (!event.target.closest("[data-ss-dropdown]")) {
            closeDropdowns();
        }
    });

    page.querySelector("[data-ss-sidebar-toggle]")?.addEventListener("click", () => {
        page.setAttribute("data-sidebar-open", "true");
    });

    page.querySelector("[data-ss-sidebar-close]")?.addEventListener("click", () => {
        page.removeAttribute("data-sidebar-open");
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        closeDropdowns();
        page.removeAttribute("data-sidebar-open");
    });

    page.querySelectorAll(".ss-upload input[type='file']").forEach((input) => {
        const upload = input.closest(".ss-upload");
        const filename = upload?.querySelector("[data-ss-upload-name]");
        const defaultText = filename?.textContent || "No file selected";

        input.addEventListener("change", () => {
            filename.textContent = input.files?.[0]?.name || defaultText;
        });
    });

    page.querySelectorAll(".ss-upload__preview img").forEach((image) => {
        image.dataset.originalSrc = image.getAttribute("src") || "";
    });

    page.querySelector("#companySettingsForm")?.addEventListener("reset", () => {
        window.setTimeout(() => {
            page.querySelectorAll(".ss-upload input[type='file']").forEach((input) => {
                const filename = input.closest(".ss-upload")?.querySelector("[data-ss-upload-name]");
                if (filename) {
                    filename.textContent = "No file selected";
                }
            });

            page.querySelectorAll(".ss-upload__preview img").forEach((image) => {
                image.setAttribute("src", image.dataset.originalSrc || image.dataset.placeholder || "");
            });
        }, 0);
    });

    // Shared hover tooltip for truncated table content. Any element carrying
    // [data-ss-tooltip] gets a floating panel on hover/focus — it lives on <body>
    // so it is never clipped by the table's overflow.
    (function initTooltips() {
        let tip = null;
        let current = null;

        const ensureTip = () => {
            if (!tip) {
                tip = document.createElement("div");
                tip.className = "ss-tooltip";
                tip.setAttribute("role", "tooltip");
                tip.innerHTML = '<div class="ss-tooltip__title"></div><div class="ss-tooltip__body"></div><span class="ss-tooltip__arrow"></span>';
                document.body.appendChild(tip);
            }

            return tip;
        };

        const hide = () => {
            current = null;
            if (tip) {
                tip.removeAttribute("data-open");
            }
        };

        const show = (target) => {
            const text = target.getAttribute("data-ss-tooltip");

            if (!text) {
                return;
            }

            current = target;

            const node = ensureTip();
            const title = target.getAttribute("data-ss-tooltip-title") || "";

            node.querySelector(".ss-tooltip__title").textContent = title;
            node.querySelector(".ss-tooltip__title").style.display = title ? "block" : "none";
            node.querySelector(".ss-tooltip__body").textContent = text;
            node.setAttribute("data-open", "true");

            const anchor = target.getBoundingClientRect();
            const box = node.getBoundingClientRect();
            const gap = 10;
            const margin = 12;

            // Prefer above the anchor, flip below when there is not enough room.
            let placement = "top";
            let top = anchor.top - box.height - gap;

            if (top < margin) {
                placement = "bottom";
                top = anchor.bottom + gap;
            }

            let left = anchor.left + anchor.width / 2 - box.width / 2;
            left = Math.max(margin, Math.min(left, window.innerWidth - box.width - margin));

            node.setAttribute("data-placement", placement);
            node.style.top = `${Math.round(top)}px`;
            node.style.left = `${Math.round(left)}px`;

            const arrowOffset = Math.max(14, Math.min(anchor.left + anchor.width / 2 - left, box.width - 14));
            node.querySelector(".ss-tooltip__arrow").style.left = `${Math.round(arrowOffset)}px`;
        };

        document.addEventListener("mouseover", (event) => {
            const target = event.target.closest("[data-ss-tooltip]");

            if (target && target !== current) {
                show(target);
            }
        });

        document.addEventListener("mouseout", (event) => {
            const target = event.target.closest("[data-ss-tooltip]");

            if (target && target === current && !target.contains(event.relatedTarget)) {
                hide();
            }
        });

        document.addEventListener("focusin", (event) => {
            const target = event.target.closest("[data-ss-tooltip]");
            if (target) {
                show(target);
            }
        });

        document.addEventListener("focusout", hide);
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                hide();
            }
        });
        window.addEventListener("scroll", hide, true);
        window.addEventListener("resize", hide);
    })();

    createIcons({
        icons,
        "stroke-width": 1.7,
        nameAttr: "data-lucide",
    });
})();
