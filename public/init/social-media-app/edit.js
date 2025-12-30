$(document).ready(function () {
	// Edit social media app - Form Validate
   
    // Validate the form
    $('#form-edit-social-media-app').validate({
        rules: {
            app_name: {
                required: true
            }
        },
        messages: {
            app_name: {
                required: "The social platform name field is required."
            }
        },
        errorPlacement: function (error, element) {
        
            error.insertAfter(element);
            
        },
        submitHandler: function (form) {
            //console.log("submit of form when validated");
            //form.submit();
        }
    });


    // Update app 
    function updated_app(formData) {
        var formUrl = `${baseUrl}/social-media-apps/${id}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#update-app").prop("disabled", true);
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
                        $("#form-edit-social-media-app").removeClass("was-invalid");
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
                $("#form-edit-social-media-app").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#form-edit-social-media-app").removeAttr("disabled");
            },
        });
    }

    $("#update-app").on("click", function (e) {
        if ($("#form-edit-social-media-app").valid()) { // Triggers validation
            var formData = new FormData($("#form-edit-social-media-app")[0]);
            console.log("submit the form when button clicked");
            e.preventDefault();
            updated_app(formData);
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
                $('#logo_preview').hide(); // Hide preview if invalid
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
					$('#error_message').hide();
                     $('#remove_image').hide();
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
                            url: '/social-media-apps/delete/logo/' + id, 
                            type: 'GET',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    // Reset the file input and image preview
                                    $('#app_logo').val(''); // Clear the file input
                                    $('#preview_img').attr('src', '#'); // Reset the image source to #
                                    $('#image_preview').addClass('d-none'); // Hide the preview div with d-none
                                    toast('Image deleted successfully.', 'success');
									setTimeout(() => {
										window.location.href = `${baseUrl}/social-media-apps`
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
            $('#app_logo').val(''); // Clear the file input
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