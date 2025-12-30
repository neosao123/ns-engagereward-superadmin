$(function () {
	// Add entity - Form Validate
     // Add custom validator method
    $.validator.addMethod("atLeastOneService", function (value, element, param) {
        return $('input[name="services[]"]:checked').length > 0;
    }, "Please select at least one verification service.");

    // Validate the form
    $('#form-add-social-media-app').validate({
        rules: {
            app_name: {
                required: true
            },
            app_logo: {
                required: true
            }
        },
        messages: {
            app_name: {
                required: "The social platform name field is required."
            },
            app_logo: {
                required: "The social platform logo is required."
            }
        },
        errorPlacement: function (error, element) {
          
             error.insertAfter(element);
           
        },
        submitHandler: function (form) {
            form.submit();
        }
    });


    // Add social media app 
    function add_social_media_app(formData) {
        var formUrl = `${baseUrl}/social-media-apps`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#submit").prop("disabled", true);
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
                        $("#form-add-social-media-app").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/social-media-apps`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#submit").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#submit").removeAttr("disabled");
            },
        });
    }

    $("#submit").on("click", function (e) {
        if ($("#form-add-social-media-app").valid()) { // Triggers validation
            var formData = new FormData($("#form-add-social-media-app")[0]);
            add_social_media_app(formData);
        }
    });
	
	
	$('#app_logo').on('change', function (e) {
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
					$('#image_preview').removeClass('d-none').show();
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
            $('#image_preview').addClass('d-none').hide();
        }
    });

	
});

