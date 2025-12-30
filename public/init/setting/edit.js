$(document).ready(function () {
	 // Custom email validator
    $.validator.addMethod("validemail", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
    }, "Please enter a valid email address.");

    // Custom phone validator: numbers only, length 10 to 15 digits
    $.validator.addMethod("validphone", function (value, element) {
        return this.optional(element) || /^[0-9]{10,15}$/.test(value);
    }, "Please enter a valid phone number (10 to 15 digits).");

    // Validate the form
    $('#form-edit-setting').validate({
        rules: {
            contact_email: {
                required: true,
                email: true,
                validemail: true,
                maxlength: 150
            },
            support_email: {
                required: false,
                email: true,
                validemail: true,
                maxlength: 150
            },
            contact_phone: {
                required: true,
                validphone: true,
                maxlength: 15
            },
            support_contact: {
                required: false,
                validphone: true,
                maxlength: 15
            }
        },
        messages: {
            contact_email: {
                required: "Contact email is required.",
                email: "Please enter a valid email address.",
                validemail: "Please enter a valid email address.",
                maxlength: "Contact email cannot exceed 150 characters."
            },
            support_email: {
              
                email: "Please enter a valid email address.",
                validemail: "Please enter a valid email address.",
                maxlength: "Support email cannot exceed 150 characters."
            },
            contact_phone: {
                required: "Contact phone is required.",
                validphone: "Please enter a valid phone number.",
                maxlength: "Contact phone cannot exceed 15 digits."
            },
            support_contact: {
                
                validphone: "Please enter a valid phone number.",
                maxlength: "Support contact cannot exceed 15 digits."
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
    function updated_setting(formData) {
        var formUrl = `${baseUrl}/setting/${id}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#update-setting").prop("disabled", true);
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
                        } else {
                            element.after(errorMessage);
                        }
                    });
                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-edit-setting").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/setting`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#form-edit-setting").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#form-edit-setting").removeAttr("disabled");
            },
        });
    }

    $("#update-setting").on("click", function (e) {
        if ($("#form-edit-setting").valid()) { // Triggers validation
            var formData = new FormData($("#form-edit-setting")[0]);
            console.log("submit the form when button clicked");
            e.preventDefault();
            updated_setting(formData);
        }
    });
	
	
	$('#logo_image').on('change', function (e) {
        const file = e.target.files[0];

        // Clear previous error message
        $('#error_message').hide().text('');

        if (file) {
            const fileType = file.type;
            const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

            // Validate file type
            if (!validImageTypes.includes(fileType)) {
                $('#error_message').text("Please upload a valid image file (jpg, jpeg, png).").show();
                $(this).val(''); // Clear the input
                $('#image_preview').hide(); // Hide preview if invalid
                return;
            }

            // Show image preview
            const reader = new FileReader();
            const img = new Image(); // Create a new Image object

            reader.onload = function (event) {
                img.src = event.target.result; // Set the image source

                img.onload = function () {
                    // Check dimensions after the image has loaded
                    const width = img.width;
                    const height = img.height;

                    $('#preview_img').attr('src', img.src);
					$('#logo_preview').removeClass('d-none').show();
					$('#remove_image').hide();
					$('#error_message').hide();
                    // Validate dimensions (for landscape image)
                    /*if (width !== 512 || height !== 512) {
                        $('#error_message').text('The entity icon must be exactly 512x512 pixels in size.').show();
                        setTimeout(function() {
							$('#error_message').hide();
						}, 1000);
						$('#image_preview').addClass('d-none'); // Hide preview if dimensions are wrong
                        $('#preview_img').val(''); // Reset file input
                    } else {
                        $('#error_message').hide(); // Hide error message
                        $('#preview_img').attr('src', img.src); // Set preview image
                        $('#image_preview').removeClass('d-none').show(); // Show image preview
                    }*/
                };
            };

            reader.readAsDataURL(file); // Read the file
        } else {
            // Hide preview if no file is selected
            $('#logo_preview').addClass('d-none').hide();
        }
    });
	
	
	$('#remove_image').on('click', function () {
        let id = $('#preview_img').data('id'); // Get the vehicle ID from the data-id attribute
        let imageSrc = $('#preview_img').attr('src'); // Get the current src of the image

        // Check if the image source is a URL (for existing image) or just '#' (for new image)
        if (imageSrc && imageSrc !== '#' && imageSrc.includes('storage-bucket')) {
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Delete!",
                content: "Are you sure you want to delete this logo?",
                theme: "modern",
                draggable: false,
                type: "red",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: '/setting/delete/logo/' + id, 
                            type: 'GET',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    // Reset the file input and image preview
                                    $('#logo_image').val(''); // Clear the file input
                                    $('#preview_img').attr('src', '#'); // Reset the image source to #
                                    $('#image_preview').addClass('d-none'); // Hide the preview div with d-none
                                    toast('Image deleted successfully.', 'success');
									setTimeout(() => {
										window.location.href = `${baseUrl}/setting`
									}, 2000);
                                } else {
                                    toast('Failed to delete the image.', 'error');
                                }
                            },
                            error: function (xhr, status, error) {
                                toast('An error occurred: ' + xhr.responseText, 'error');
                            }
                        });
                    },
                    cancel: function () {
                        // Cancel button action (optional)
                    }
                }
            });
        } else {
            // This is a new image that hasn't been uploaded yet
            $('#logo_image').val(''); // Clear the file input
            $('#preview_img').attr('src', '#'); // Reset the image source to #
            $('#logo_preview').addClass('d-none'); // Hide the preview div with d-none
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