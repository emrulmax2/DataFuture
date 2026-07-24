import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

Dropzone.autoDiscover = false;
$(".dropzone").each(function () {
    let options = {
        autoProcessQueue: false,
        accept: (file, done) => {
            console.log("Uploaded");
            done();
        },
    };

    if ($(this).data("single")) {
        options.maxFiles = 1;
    }

    if ($(this).data("file-types")) {
        options.accept = (file, done) => {
            if ($(this).data("file-types").split("|").indexOf(file.type) === -1) {
                alert("Error! Files of this type are not accepted");
                done("Error! Files of this type are not accepted");
            } else {
                console.log("Uploaded");
                done();
            }
        };
    }

    var dz = new Dropzone(this, options);

    dz.on("maxfilesexceeded", (file) => {
        alert("No more files please!");
    });
    dz.on("complete", function(file) {
        dz.removeFile(file);
    });        
});

function setupStudentOptionModals() {
    const page = document.querySelector(".ss-student-options-page");

    if (!page) {
        return;
    }

    const codeFields = [];
    const activeFields = [];

    function safeIdPart(value) {
        return String(value || "field").replace(/[^A-Za-z0-9_-]/g, "-");
    }

    function setUniqueId(control, form, name) {
        if (!control) {
            return "";
        }

        const nextId = `${safeIdPart(form.id || "student-option-form")}-${safeIdPart(name)}`;
        control.id = nextId;

        return nextId;
    }

    function fieldParts(key) {
        return {
            areaSelector: key === "hesa" ? ".hesa_code_area" : ".df_code_area",
            checkboxName: key === "hesa" ? "is_hesa" : "is_df",
            inputName: key === "hesa" ? "hesa_code" : "df_code",
            label: key === "hesa" ? "Hesa Code" : "DF Code",
            placeholder: key === "hesa" ? "Hesa code" : "DF code",
        };
    }

    function normaliseTitle(text) {
        const value = String(text || "").trim();

        if (value.startsWith("Add New ") || !value.startsWith("Add ")) {
            return value;
        }

        return value.replace(/^Add\s+/, "Add New ");
    }

    function legacySwitchContainer(checkbox) {
        const formCheck = checkbox.closest(".form-check");
        const parent = formCheck?.parentElement;

        return parent
            && !parent.classList.contains("modal-body")
            && !parent.classList.contains("modal-footer")
            ? parent
            : formCheck;
    }

    function hideLegacySwitch(element) {
        if (!element) {
            return;
        }

        element.classList.add("ss-student-option-hidden-switch");
        element.setAttribute("aria-hidden", "true");
        element.style.setProperty("display", "none", "important");
    }

    function buttonLabel(button, fallback) {
        const text = Array.from(button.childNodes)
            .filter((node) => node.nodeType === Node.TEXT_NODE)
            .map((node) => node.textContent.trim())
            .join(" ")
            .replace(/\s+/g, " ")
            .trim();

        return text || fallback;
    }

    function footerButtonIcon(icon) {
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        const paths = icon === "x"
            ? ["M18 6 6 18", "m6 6 12 12"]
            : ["m20 6-11 11-5-5"];

        svg.setAttribute("class", "ss-button-icon");
        svg.setAttribute("aria-hidden", "true");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("fill", "none");
        svg.setAttribute("stroke", "currentColor");
        svg.setAttribute("stroke-width", "2.3");
        svg.setAttribute("stroke-linecap", "round");
        svg.setAttribute("stroke-linejoin", "round");

        paths.forEach((pathValue) => {
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");

            path.setAttribute("d", pathValue);
            svg.appendChild(path);
        });

        return svg;
    }

    function applyFooterButtonStyle(button, variant) {
        const isSubmit = variant === "submit";
        const styles = {
            minHeight: "38px",
            display: "inline-flex",
            alignItems: "center",
            justifyContent: "center",
            gap: "8px",
            margin: "0",
            padding: "9px 20px",
            borderRadius: "9px",
            fontSize: "13.5px",
            fontWeight: "800",
            lineHeight: "1",
            textDecoration: "none",
            whiteSpace: "nowrap",
            minWidth: isSubmit ? "96px" : "120px",
            border: isSubmit ? "1px solid #1b2f63" : "1px solid #f0cfcf",
            background: isSubmit ? "#1b2f63" : "#fbe9e9",
            color: isSubmit ? "#ffffff" : "#c0392b",
            boxShadow: isSubmit ? "0 12px 22px -14px rgba(22, 38, 79, 0.8)" : "none",
        };

        Object.entries(styles).forEach(([property, value]) => {
            button.style.setProperty(property.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`), value, "important");
        });
    }

    function enhanceFooterButton(button, icon, fallback, keepSpinner = false, variant = "cancel") {
        const label = buttonLabel(button, fallback);
        const spinner = keepSpinner ? button.querySelector("svg") : null;
        const labelElement = document.createElement("span");

        button.innerHTML = "";

        if (spinner) {
            spinner.classList.add("ss-spinner");
            spinner.style.setProperty("display", "none", "important");
            button.appendChild(spinner);
        }

        labelElement.textContent = label;
        button.appendChild(footerButtonIcon(icon));
        button.appendChild(labelElement);
        applyFooterButtonStyle(button, variant);
    }

    function enhanceModalChrome(modal, form) {
        const dialog = modal.querySelector(".modal-dialog");
        const content = modal.querySelector(".modal-content");
        const header = modal.querySelector(".modal-header");
        const body = modal.querySelector(".modal-body");
        const footer = modal.querySelector(".modal-footer");

        modal.classList.add("ss-modal", "ss-student-option-modal");
        dialog?.classList.add("ss-settings-modal__dialog");
        content?.classList.add("ss-settings-modal", "ss-compact-settings-modal");
        body?.classList.add("ss-settings-modal__body");
        footer?.classList.add("ss-settings-modal__footer");
        form.setAttribute("autocomplete", "off");

        if (dialog?.classList.contains("modal-lg")) {
            dialog.classList.add("ss-settings-modal__dialog--wide");
        }

        if (header && header.dataset.ssEnhanced !== "true") {
            header.dataset.ssEnhanced = "true";
            header.classList.add("ss-settings-modal__header");

            const title = header.querySelector("h2");
            const close = header.querySelector("[data-tw-dismiss]");
            const titleWrap = document.createElement("div");
            const accent = document.createElement("span");

            titleWrap.className = "ss-settings-modal__title";
            titleWrap.appendChild(accent);

            if (title) {
                title.textContent = normaliseTitle(title.textContent);
                titleWrap.appendChild(title);
            }

            header.prepend(titleWrap);

            if (close) {
                close.classList.add("ss-modal-close");
                close.setAttribute("aria-label", "Close modal");

                if (close.tagName.toLowerCase() === "a") {
                    close.removeAttribute("href");
                    close.setAttribute("role", "button");
                }

                header.appendChild(close);
            }
        }

        modal.querySelectorAll(".modal-body .form-control, .modal-body .form-select").forEach((input) => {
            input.classList.add("ss-modal-input");
        });

        if (footer) {
            footer.querySelectorAll("button[data-tw-dismiss]").forEach((button) => {
                button.classList.add("ss-btn", "ss-btn--danger-soft", "ss-student-option-footer-cancel");
                enhanceFooterButton(button, "x", "Cancel", false, "cancel");
            });

            footer.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.classList.add("ss-btn", "ss-btn--primary", "ss-student-option-footer-submit");
                enhanceFooterButton(button, "check", button.id?.toLowerCase().includes("update") ? "Update" : "Save", true, "submit");
            });
        }
    }

    function markHiddenSwitch(checkbox) {
        hideLegacySwitch(legacySwitchContainer(checkbox));
    }

    function syncCodeField(item) {
        const enabled = item.checkbox.checked;

        item.checkbox.setAttribute("aria-checked", enabled ? "true" : "false");
        item.area.setAttribute("data-enabled", enabled ? "true" : "false");
        item.area.style.display = "";
        item.area.style.opacity = "";
        item.input.disabled = !enabled;
    }

    function enhanceCodeField(form, key) {
        const parts = fieldParts(key);
        const area = form.querySelector(parts.areaSelector);
        const checkbox = form.querySelector(`input[name="${parts.checkboxName}"]`);
        const input = form.querySelector(`input[name="${parts.inputName}"]`);

        if (!area || !checkbox || !input || area.dataset.ssEnhanced === "true") {
            return;
        }

        const checkboxId = setUniqueId(checkbox, form, parts.checkboxName);
        const sourceLabel = checkbox.closest(".form-check")?.querySelector("label");

        if (sourceLabel) {
            sourceLabel.setAttribute("for", checkboxId);
        }

        markHiddenSwitch(checkbox);

        area.dataset.ssEnhanced = "true";
        area.classList.add("ss-modal-field", "ss-toggle-field", "ss-student-option-code-field");
        area.style.display = "";
        area.style.opacity = "";

        const label = area.querySelector("label.form-label") || document.createElement("label");
        label.classList.add("form-label");
        label.setAttribute("for", input.id || `${checkboxId}-${parts.inputName}`);
        label.textContent = parts.label;

        if (!label.parentElement) {
            area.prepend(label);
        }

        input.classList.add("ss-modal-input");
        input.placeholder = input.placeholder || parts.placeholder;
        input.autocomplete = "off";

        const wrapper = document.createElement("div");
        wrapper.className = "ss-toggle-input";

        const switchLabel = document.createElement("label");
        switchLabel.className = "ss-toggle-input__switch";
        switchLabel.setAttribute("for", checkboxId);
        switchLabel.setAttribute("aria-label", `Enable ${parts.label}`);
        switchLabel.innerHTML = `
            <span class="ss-toggle-input__icon ss-toggle-input__icon--on"><i data-lucide="check"></i></span>
            <span class="ss-toggle-input__icon ss-toggle-input__icon--off"><i data-lucide="x"></i></span>
        `;

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(switchLabel);
        wrapper.appendChild(input);

        const error = area.querySelector(`.error-${parts.inputName}`);
        if (error) {
            area.appendChild(error);
        }

        const item = { area, checkbox, input };
        codeFields.push(item);
        checkbox.addEventListener("change", () => {
            window.setTimeout(() => syncCodeField(item), 0);
        });
        syncCodeField(item);
    }

    function syncActiveField(item) {
        const enabled = item.checkbox.checked;

        item.checkbox.setAttribute("aria-checked", enabled ? "true" : "false");
        item.field.setAttribute("data-enabled", enabled ? "true" : "false");
        item.field.classList.toggle("is-active", enabled);
        item.field.classList.toggle("is-inactive", !enabled);
        item.toggle?.setAttribute("aria-pressed", enabled ? "true" : "false");
        item.title.textContent = enabled ? "Active" : "Inactive";
        item.copy.textContent = enabled
            ? "Available for new option setup"
            : "Not available for new option setup";
    }

    function enhanceActiveField(form, modal) {
        const checkbox = form.querySelector('input[name="active"]');
        const modalBody = modal.querySelector(".modal-body");

        if (!checkbox || !modalBody || modal.dataset.ssActiveEnhanced === "true") {
            return;
        }

        const checkboxId = setUniqueId(checkbox, form, "active");
        const sourceLabel = checkbox.closest(".form-check")?.querySelector("label");

        if (sourceLabel) {
            sourceLabel.setAttribute("for", checkboxId);
        }

        const legacyContainer = legacySwitchContainer(checkbox);
        modal.dataset.ssActiveEnhanced = "true";

        const field = document.createElement("div");
        field.className = "ss-modal-field ss-student-option-active-field";
        field.setAttribute("data-enabled", checkbox.checked ? "true" : "false");
        field.classList.add(checkbox.checked ? "is-active" : "is-inactive");
        field.innerHTML = `
            <label for="${checkboxId}">Active Status</label>
            <label class="ss-status-toggle" for="${checkboxId}" aria-pressed="${checkbox.checked ? "true" : "false"}">
                <span class="ss-status-toggle__control">
                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                </span>
                <span class="ss-status-toggle__copy">
                    <strong>Active</strong>
                    <small>Available for new option setup</small>
                </span>
            </label>
        `;

        modalBody.appendChild(field);
        checkbox.classList.remove("form-check-input", "m-0");
        checkbox.setAttribute("autocomplete", "off");
        field.querySelector(".ss-status-toggle").prepend(checkbox);
        hideLegacySwitch(legacyContainer);

        const item = {
            checkbox,
            field,
            toggle: field.querySelector(".ss-status-toggle"),
            title: field.querySelector("strong"),
            copy: field.querySelector("small"),
        };

        activeFields.push(item);
        checkbox.addEventListener("change", () => {
            window.setTimeout(() => syncActiveField(item), 0);
        });
        syncActiveField(item);
    }

    page.querySelectorAll(".modal:not(#successModal):not(#confirmModal)").forEach((modal) => {
        const form = modal.querySelector("form");

        if (!form) {
            return;
        }

        enhanceModalChrome(modal, form);
        enhanceCodeField(form, "hesa");
        enhanceCodeField(form, "df");
        enhanceActiveField(form, modal);
    });

    const syncEnhancedFields = () => {
        codeFields.forEach(syncCodeField);
        activeFields.forEach(syncActiveField);
    };

    syncEnhancedFields();
    window.setInterval(syncEnhancedFields, 250);

    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
}

setupStudentOptionModals();

(function(){
    function refreshIcons() {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    }

    $('.optionBoxTitle').on('click', function(e){
        e.preventDefault();

        var $title = $(this);
        var $box = $title.closest('.optionBox');
        var $boxBody = $title.closest('.optionBoxHeader').siblings('.optionBoxBody');

        $boxBody.slideToggle();
        $box.toggleClass('active');
        $title.attr('aria-expanded', $box.hasClass('active') ? 'true' : 'false');

        refreshIcons();
    });

    $('.optionBoxTitle').on('keydown', function(e){
        if (e.key !== 'Enter' && e.key !== ' ') {
            return;
        }

        e.preventDefault();
        $(this).trigger('click');
    });

    $('.ss-option-accordion__chevron').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();

        $(this).closest('.optionBoxHeader').find('.optionBoxTitle').trigger('click');
    });
})();
