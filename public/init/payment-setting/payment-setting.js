$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

    // Validate the form
    $('#form-payment-setting').validate({
        rules: {
            payment_mode: {
                required: true
            },
            test_secret_key: {
                required: true
            },
            test_client_id: {
                required: true
            },
            live_secret_key: {
                required: true
            },
            live_client_id: {
                required: true
            },
            webhook_secret_key: {
                required: true
            },
            webhook_secret_live_key: {
                required: true
            },
            payment_gateway: {
                required: true,
                maxlength: 50
            }
        },
        messages: {
            payment_mode: {
                required: "Payment mode is required."
            },
            test_secret_key: {
                required: "Test secret key is required."
            },
            test_client_id: {
                required: "Test client ID is required."
            },
            live_secret_key: {
                required: "Live secret key is required."
            },
            live_client_id: {
                required: "Live client ID is required."
            },
            webhook_secret_key: {
                 required: "Webhook secret key is required."
            },
            webhook_secret_live_key: {
               required: "Webhook live secret key is required."
            },
            payment_gateway: {
                required: "Payment gateway is required.",
                maxlength: "Payment gateway cannot exceed 50 characters."
            }
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        submitHandler: function (form) {
            // Handled by submit button click
        }
    });

    // Save/Update payment setting
    function savePaymentSetting(formData) {
        var formUrl = `${baseUrl}/payment-setting/store`;

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#submit-payment-setting").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".error .text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (i, v) {
                        let errorMessage = '<span class="backend-error text-danger">' + v[0] + '</span>';
                        let element = $("[name='" + i + "']");
                        if (element.hasClass('select2-hidden-accessible')) {
                            element.next('.select2-container').after(errorMessage);
                        } else {
                            element.after(errorMessage);
                        }
                    });
                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-payment-setting").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/payment-setting`;
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#submit-payment-setting").prop("disabled", false);
            },
        });
    }

    // Submit button click event
    $("#submit-payment-setting").on("click", function (e) {
        e.preventDefault();

        if ($("#form-payment-setting").valid()) {
            var formData = new FormData($("#form-payment-setting")[0]);
            savePaymentSetting(formData);
        }
    });
});
