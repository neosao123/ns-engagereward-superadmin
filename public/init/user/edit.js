$(document).ready(function () {


    $(document).on('click', '.toggle-password', function () {
        const target = $(this).data('target');
        const input = $(target);
        const svg = $(this).find('svg');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            svg.attr('data-icon', 'eye'); // toggle icon to open eye
        } else {
            input.attr('type', 'password');
            svg.attr('data-icon', 'eye-slash'); // toggle icon to closed eye
        }
    });
	  // update User details
    function update_user(formData) {
       var formUrl = `${baseUrl}/users/${id}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                //$("#update-employee").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".error .text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (i, v) {
                        let errorMessage = '<span class="backend-error text-danger">' + v[0] + '</span>';
                        let element = $("[name='" + i + "']");
                        if (element.hasClass('select2-hidden-accessible')) {
                            // Handle select2 error messages
                            element.next('.select2-container').after(errorMessage);
                        } else if (element.closest('.input-group').length) {
                            element.closest('.input-group').after(errorMessage);
                        }else {
                            element.after(errorMessage);
                        }
                    });
                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-update-user").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/users`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#user-update").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#user-update").removeAttr("disabled");
            },
        });
    }

    $("#user-update").on("click", function (e) {
        if ($("#form-update-user").valid()) { // Triggers validation
            var formData = new FormData($("#form-update-user")[0]);
            update_user(formData);
        }
    });


	// Select2 - Role
    $('#role').select2({
        placeholder: 'Select Role',
        allowClear: true,
       // minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/users/fetch/role",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

	//file validation
	$("#file").on("change", function () {
        var filePath = $(this).val();
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (!allowedExtensions.exec(filePath)) {
            $(this).val(null);
            return false;
        } else {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function (event) {
                    $("#showImage").attr("src", event.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });
});
