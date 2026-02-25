$(document).ready(function () {
    $(document).on("change", 'input[type="checkbox"]:not(.group-select-all)', function () {
        var checkboxName = $(this).attr("name");
        var permissionId = $(this).data("permission-id");
        let mode = "set";
        if ($(this).is(":checked")) {
            mode = "set";
        } else {
            mode = "revoke";
        }

        $.ajax({
            type: "get",
            url: `${baseUrl}/configuration/role/${roleId}/set-permission?mode=${mode}&permissionId=${permissionId}`,
            dataType: "JSON",
            success: function (response) {
                if (response.status === 200) {
                    // Assuming toast is a global function for notifications
                    if (typeof toast === "function") {
                        toast(response.message, "success", "2", "right", "bottom");
                    } else {
                        alert(response.message);
                    }
                }
                if (response.status === 400) {
                    if (typeof toast === "function") {
                        toast(response.message, "error", "2", "right", "bottom");
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function (ex) {
                console.log(ex);
            },
        });
    });
});
