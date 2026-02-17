
$(document).ready(function () {
    // Validate the form
    $('#form-edit-app-setting').validate({
        rules: {
            setting_value: {
                required: true,
            }
        },
        messages: {
            setting_value: {
                required: "Version value is required.",
            }
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        submitHandler: function (form) {
           // form.submit();
        }
    });

    // Update setting 
    function updated_app_setting(formData) {
        var formUrl = `${baseUrl}/app-settings/update/${id}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#update-app-setting").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".error .text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (i, v) {
                        let errorMessage = '<span class="backend-error text-danger">' + v[0] + '</span>';
                        let element = $("[name='" + i + "']");
                        element.after(errorMessage);
                    });
                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-edit-app-setting").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/app-settings/list`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#update-app-setting").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#update-app-setting").removeAttr("disabled");
            },
        });
    }

    $("#update-app-setting").on("click", function (e) {
        if ($("#form-edit-app-setting").valid()) { // Triggers validation
            var formData = new FormData($("#form-edit-app-setting")[0]);
            e.preventDefault();
            updated_app_setting(formData);
        }
    });

});
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

});
