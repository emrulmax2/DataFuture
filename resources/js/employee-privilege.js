import { createIcons, icons } from "lucide";
import Litepicker from "litepicker";
import { initPrivilegeUi } from "./privilege-ui";

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

    initPrivilegeUi();
})();
