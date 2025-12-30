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

    //form validation

    $('#form-update-password').validate({
        ignore: "",
        rules: {
            old_password: {
                required: true,
            },
            new_password: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            password_confirmation: {
                required: true,
                minlength: 6,
                maxlength: 20,
                equalTo: "#new_password"
            }
        },
        messages: {
            old_password: {
                required: "The Old password is required.",
            },
            new_password: {
                required: "The New password is required.",
                minlength: "The New password must be at least 6 characters long.",
                maxlength: "The New password must not exceed 20 characters."
            },
            password_confirmation: {
                required: "The Confirm password is required.",
                minlength: "The Confirm password must be at least 6 characters long.",
                equalTo: "Password is not matched confirm password.",
                maxlength: "The Confirm password must not exceed 20 characters."
            }
        },
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");

            if (element.closest('.input-group').length) {
                element.closest('.input-group').after(error);
            } else {
                element.after(error);
            }
        },
        highlight: function (element) {
            //$(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            //$(element).removeClass("is-invalid");
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

});
