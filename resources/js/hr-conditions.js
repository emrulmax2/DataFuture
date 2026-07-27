import { createIcons, icons } from "lucide";

(function () {
    const form = document.getElementById("hrConditionForm");

    if (!form) {
        return;
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const csrfToken = $('meta[name="csrf-token"]').attr("content");

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.7,
            nameAttr: "data-lucide",
        });
    };

    const setBusy = (busy) => {
        const buttons = form.querySelectorAll(".updateHRC");

        buttons.forEach((button) => {
            button.disabled = busy;
            const spinner = button.querySelector(".ss-spinner");

            if (spinner) {
                spinner.style.cssText = busy ? "display: inline-block;" : "display: none;";
            }
        });
    };

    const showSuccess = (title, description) => {
        $("#successModal .successModalTitle").text(title);
        $("#successModal .successModalDesc").text(description);
        successModal.show();
    };

    refreshIcons();

    $("#hrConditionForm").on("submit.hrConditions", function (event) {
        event.preventDefault();
        setBusy(true);

        axios({
            method: "post",
            url: route("hr.condition.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy(false);

            if (response.status == 200) {
                showSuccess("Success!", "HR attendance conditions successfully updated.");
            }
        }).catch((error) => {
            setBusy(false);
            console.log(error);
        });
    });
})();
