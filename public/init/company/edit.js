
$(function () {

    // Initialize counter and hide remove button if only one row
    if ($('.document-row').length <= 1) {
        $('.remove-document').hide();
    }

    let documentCounter = $('.document-row').length || 1;

    // Add new document row with proper preview structure
    $('#add-document').click(function () {
        const newRow = `
		<div class="document-row mb-3 border-bottom pb-3">
			<div class="row">
			   <input type="hidden" name="documents[${documentCounter}][id]" value="#">

				<div class="col-md-4 mb-2">
					<label class="form-label">Document Type</label>
					<input type="text" class="form-control"
						   name="documents[${documentCounter}][type]"
						   placeholder="e.g. COI, PAN etc.">
				</div>

				<div class="col-md-4 mb-2">
					<label class="form-label">Document Number</label>
					<input type="text" class="form-control"
						   name="documents[${documentCounter}][number]">
				</div>

				<div class="col-md-3 mb-2">
					<label class="form-label">Document File</label>
					<input type="file" class="form-control document-file"
						   name="documents[${documentCounter}][file]"
						   accept=".pdf,.jpg,.jpeg,.png">
					<div class="file-preview mt-2">
						<div class="no-preview">
							<i class="fas fa-file-alt fa-3x text-secondary"></i>
							<p class="small mb-0">No file selected</p>
						</div>
					</div>
				</div>

				<div class="col-md-1 mb-2 d-flex align-items-end">
					<button type="button" class="btn btn-danger remove-document">
						<i class="fas fa-trash"></i>
					</button>
				</div>
			</div>
		</div>`;

        $('#documents-repeater').append(newRow);
        documentCounter++;
        updateRemoveButtons();
    });

    // Remove document row with confirmation
    $(document).on('click', '.remove-document', function () {

        debugger;
        const row = $(this).closest('.document-row');
        const docId = row.find('input[name*="[id]"]').val(); // Get document ID if exists
        const docType = row.find('input[name*="[type]"]').val() || 'document';
        const filePath = row.find('input[name*="[existing_file]"]').val(); // Get file path if exists

        if (filePath != undefined && filePath != "") {
            // Confirmation dialog
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Delete!",
                content: `Are you sure you want to delete this document?`,
                theme: "modern",
                draggable: false,
                type: "red",
                typeAnimated: true,
                buttons: {
                    confirm: {
                        text: "Delete",
                        btnClass: "btn-danger",
                        action: function () {
                            // If it's an existing document (has ID), make AJAX call
                            if (docId) {
                                $.ajax({
                                    url: baseUrl + "/company/document/" + docId,
                                    type: "DELETE",
                                    data: {
                                        '_token': csrfToken,
                                        'file_path': filePath // Send file path for server-side cleanup
                                    },
                                    beforeSend: function () {
                                        // Show loading state
                                        row.find('.remove-document').html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                                    },
                                    success: function (response) {
                                        if (response.success) {
                                            // Remove row on success
                                            row.remove();
                                            reindexDocumentRows();
                                            toast("Document deleted successfully.", "success");
                                        } else {
                                            toast(response.error || "Failed to delete document.", "error");
                                            row.find('.remove-document').html('<i class="fas fa-trash"></i>').prop('disabled', false);
                                        }
                                    },
                                    error: function (xhr) {
                                        const errorMessage = xhr.responseJSON?.message || "An error occurred while deleting the document.";
                                        toast(errorMessage, "error");
                                        row.find('.remove-document').html('<i class="fas fa-trash"></i>').prop('disabled', false);
                                    }
                                });
                            } else {
                                // For new/unsaved documents, just remove the row
                                row.remove();
                                reindexDocumentRows();
                                toast("Document removed.", "success");
                            }
                        }
                    },
                    cancel: function () {
                        // Do nothing on cancel
                    }
                }
            });
        } else {
            row.remove();
            reindexDocumentRows();
        }
    });

    // Helper function to reindex document rows
    function reindexDocumentRows() {
        let index = 0;
        $('.document-row').each(function () {
            $(this).find('input, select').each(function () {
                const name = $(this).attr('name').replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', name);
            });
            index++;
        });
        documentCounter = index;

        // Hide remove button if only one row left
        if ($('.document-row').length <= 1) {
            $('.remove-document').hide();
        } else {
            $('.remove-document').show();
        }
    }


    // File preview handler for both existing and new elements
    $(document).on('change', '.document-file', function () {
        const fileInput = $(this);
        const file = fileInput[0].files[0];
        const previewContainer = fileInput.siblings('.file-preview');

        // Clear previous previews
        previewContainer.html('');

        if (!file) {
            // Show placeholder when no file is selected
            previewContainer.html('<div class="no-preview">\
				<i class="fas fa-file-alt fa-3x text-secondary"></i>\
				<p class="small mb-0">No file selected</p>\
			</div>');
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const isValidType = validTypes.includes(file.type) ||
            (file.type === '' && ['jpg', 'jpeg', 'png', 'pdf'].includes(fileExtension));

        if (!isValidType) {
            toastr.error('Only JPG, PNG, and PDF files are allowed');
            fileInput.val('');
            previewContainer.html('<div class="no-preview">\
				<i class="fas fa-file-alt fa-3x text-secondary"></i>\
				<p class="small mb-0">No file selected</p>\
			</div>');
            return;
        }

        // Handle image preview
        if (file.type.match('image.*') || ['jpg', 'jpeg', 'png'].includes(fileExtension)) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewContainer.html('<a href="#" class="new-upload-preview">\
					<img class="img-preview img-thumbnail" \
						 src="' + e.target.result + '" \
						 style="max-height: 100px;">\
					\
				</a>');
            };
            reader.readAsDataURL(file);
        }
        // Handle PDF preview
        else if (file.type === 'application/pdf' || fileExtension === 'pdf') {
            previewContainer.html('<div class="pdf-preview">\
				<i class="fas fa-file-pdf text-danger fa-3x"></i>\
				<p class="small mb-0 filename">' + file.name + '</p>\
			\
			</div>');
        }
    });

    // Helper functions
    function updateRemoveButtons() {
        const $rows = $('.document-row');
        $rows.each(function (index) {
            const $btn = $(this).find('.remove-document');
            $btn.toggle($rows.length > 1);
        });
    }

    function reindexDocuments() {
        let newIndex = 0;
        $('.document-row').each(function () {
            $(this).find('[name]').each(function () {
                const name = $(this).attr('name')
                    .replace(/documents\[\d+\]/, `documents[${newIndex}]`);
                $(this).attr('name', name);
            });
            newIndex++;
        });
        documentCounter = newIndex;
    }


    $('#company_logo').on('change', function (e) {
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
                    $("#remove_image").hide();

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


    // Select2 - Country
    $('.country_code').select2({
        placeholder: 'Select Country',
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
            url: baseUrl + "/company/country-code",
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
                            url: '/company/delete/logo/' + id,
                            type: 'GET',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    // Reset the file input and image preview
                                    $('#company_logo').val(''); // Clear the file input
                                    $('#preview_img').attr('src', '#'); // Reset the image source to #
                                    $('#logo_preview').addClass('d-none'); // Hide the preview div with d-none
                                    toast('Image deleted successfully.', 'success');
                                    setTimeout(() => {
                                        window.location.href = `${baseUrl}/company`
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
            $('#company_logo').val(''); // Clear the file input
            $('#preview_img').attr('src', '#'); // Reset the image source to #
            $('#logo_preview').addClass('d-none'); // Hide the preview div with d-none
        }
    });

    $('#is_billing_address_same').on('change', function () {
        if ($(this).is(':checked')) {
            $('#billing_address_line_one').val($('#office_address_line_one').val());
            $('#billing_address_line_two').val($('#office_address_line_two').val());
            $('#billing_address_city').val($('#office_address_city').val());
            $('#billing_address_province_state').val($('#office_address_province_state').val());
            $('#billing_address_postal_code').val($('#office_address_postal_code').val());

            // Get the selected value and label from office select
            const officeCountryCode = $('#office_address_country_code').val();
            const officeCountryText = $('#office_address_country_code option:selected').text();

            // Check if the billing select already has this option
            if ($('#billing_address_country_code option[value="' + officeCountryCode + '"]').length === 0) {
                $('#billing_address_country_code').append(
                    $('<option>', {
                        value: officeCountryCode,
                        text: officeCountryText
                    })
                );
            }

            // Set the value and trigger change
            $('#billing_address_country_code').val(officeCountryCode).trigger('change');

        } else {
            $('#billing_address_line_one, #billing_address_line_two, #billing_address_city, #billing_address_province_state, #billing_address_postal_code').val('');

            // Reset country select2
            $('#billing_address_country_code').val(null).trigger('change');
        }
    });


    // Select2 - Country
    $('.country_code').select2({
        placeholder: 'Select Country',
        allowClear: true,
        //minimumInputLength: 1,
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
            url: baseUrl + "/company/country-code",
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


    $("#basic_info_update").on("click", function () {
        var formData = new FormData($("#basic_info_form")[0]);
        var formUrl = baseUrl + "/company/update/basic-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#basic_info_update").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove();
                    $("#basic_info_form").addClass("was-invalid");

                    // Reset tabs to first tab on error
                    $("#tab1_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab1").addClass("active");

                    $("#tab2_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab2").removeClass("active");

                    $("#tab3_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    // Show errors
                    $.each(response.errors, function (key, value) {
                        let [prefix, index, field] = key.split('.');
                        let errorField = $(`[name='${prefix}`);
                        if (errorField.hasClass('select2-hidden-accessible')) {
                            errorField.next('.select2').after(`<div class="invalid-feedback">${value[0]}</div>`);
                        } else {
                            errorField.after(`<div class="invalid-feedback">${value[0]}</div>`);
                        }
                    });
                    return false;

                } else {
                    if (response.status === 200) {
                        $("#basic_info_update").prop("disabled", true);
                        $("#basic_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        // Move to next tab (Tab 2)
                        $("#tab2_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab2").addClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        // Keep other tabs disabled
                        $("#tab3_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab3").removeClass("active");

                        $("#tab4_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#basic_info_update").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#basic_info_update").removeAttr("disabled");
            },
        });
    });

    $('#tab1_nav a').on("click", function (event) {
        $("#tab1_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab1").addClass("active");

        $("#tab2_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab3_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab3").removeClass("active");

        $("#tab4_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");
    });

    $('#tab2_nav a').on("click", function (event) {
        event.preventDefault();

        var formData = new FormData($("#basic_info_form")[0]);
        var formUrl = baseUrl + "/company/update/basic-info";

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#basic_info_update").prop("disabled", true);
            },
            success: function (response) {
                // Validation errors case
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove(); // Clear previous errors
                    $("#basic_info_form").addClass("was-invalid");

                    // Reset to tab 2 on error
                    $("#tab1_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab1").addClass("active");

                    $("#tab2_nav a").removeClass("active").removeClass("disabled");
                    $("#bootstrap-wizard-validation-tab2").removeClass("active");

                    $("#tab3_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    // Show errors
                    $.each(response.errors, function (key, value) {
                        let [prefix, index, field] = key.split('.');
                        let errorField = $(`[name='${prefix}`);
                        if (errorField.hasClass('select2-hidden-accessible')) {
                            errorField.next('.select2').after(`<div class="invalid-feedback">${value[0]}</div>`);
                        } else {
                            errorField.after(`<div class="invalid-feedback">${value[0]}</div>`);
                        }
                    });
                    return false;
                }

                // Success case
                if (response.status === 200) {
                    $("#basic_info_update").prop("disabled", true);
                    $("#basic_info_form").removeClass("was-invalid");
                    $(".invalid-feedback").remove();

                    // Move to next tab (Tab 3)
                    $("#tab2_nav a").addClass("active").removeClass("disabled");
                    $("#bootstrap-wizard-validation-tab2").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab3_nav a").removeClass("active").removeClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                } else {
                    toast("Something went wrong.", "error");
                    $("#basic_info_update").removeAttr("disabled");
                    return false;
                }
            },
            error: function () {
                $("#basic_info_update").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#basic_info_update").removeAttr("disabled");
            },
        });
    });


    $("#address_info_update").on("click", function () {
        var formData = new FormData($("#addr_info_form")[0]);
        var formUrl = baseUrl + "/company/update/address-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#address_info_update").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove();
                    $("#addr_info_form").addClass("was-invalid");


                    // Reset to tab 2 on error
                    $("#tab2_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab2").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab3_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    $.each(response.errors, function (key, value) {
                        let [prefix, index, field] = key.split('.'); // Get field name and index
                        let errorField = $(`[name='${prefix}`);
                        if (errorField.hasClass('select2-hidden-accessible')) {
                            errorField.next('.select2').after(`<div class="invalid-feedback">${value[0]}</div>`); // Show error message
                        } else {
                            errorField.after(`<div class="invalid-feedback">${value[0]}</div>`); // Show error message
                        }
                    });
                    return false;
                } else if (response.hasOwnProperty("unique")) {
                    toast(response.unique, 'error');
                } else {
                    if (response.status === 200) {
                        $("#address_info_update").prop("disabled", true);
                        $("#addr_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        // Move to next tab (Tab 3)
                        $("#tab3_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab3").addClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab2_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab2").removeClass("active");

                        // Keep tab 4 disabled
                        $("#tab4_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    } else {
                        toast("Something went wrong.", "error");
                        $("#address_info_update").prop("disabled", true);
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#address_info_update").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#address_info_update").removeAttr("disabled");
            },
        });
    });


    $('#tab3_nav a').on("click", function (event) {
        event.preventDefault();
        var formData = new FormData($("#addr_info_form")[0]);
        var formUrl = baseUrl + "/company/update/address-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#address_info_update").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove();
                    $("#addr_info_form").addClass("was-invalid");


                    $("#tab2_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab2").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab3_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    $.each(response.errors, function (key, value) {
                        let [prefix, index, field] = key.split('.');
                        let errorField = $(`[name='${prefix}`);
                        if (errorField.hasClass('select2-hidden-accessible')) {
                            errorField.next('.select2').after(`<div class="invalid-feedback">${value[0]}</div>`);
                        } else {
                            errorField.after(`<div class="invalid-feedback">${value[0]}</div>`);
                        }
                    });
                    return false;
                } else if (response.hasOwnProperty("unique")) {
                    toast(response.unique, 'error');
                } else {
                    if (response.status === 200) {
                        $("#address_info_update").prop("disabled", true);
                        $("#addr_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        $("#tab3_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab3").addClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab2_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab2").removeClass("active");

                        $("#tab4_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#address_info_update").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#address_info_update").removeAttr("disabled");
            },
        });
    });


    // Social Info Submit Handler
    $("#social_form_update_next").on("click", function () {
        var formData = new FormData($("#social_info_form")[0]);
        var formUrl = baseUrl + "/company/update/social-info";

        $(".is-invalid").removeClass("is-invalid");
        $(".invalid-feedback").remove();

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#social_form_update_next").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $("#social_info_form").addClass("was-invalid");

                    // Reset to tab 3 on error
                    $("#tab3_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab3").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab2_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab2").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    // Handle errors
                    if (response.errors.social_apps) {
                        const errorContainer = $('<div class="text-danger mb-3 text-center"></div>')
                            .text(response.errors.social_apps[0]);
                        $("#social_info_form .card-body").prepend(errorContainer);
                    }

                    $.each(response.errors, function (key, value) {
                        if (key === 'social_apps') return;

                        const match = key.match(/social_apps\.(\d+)\.enabled/);
                        if (match) {
                            const appId = match[1];
                            const checkbox = $(`[name="social_apps[${appId}][enabled]"]`);
                            checkbox.closest('.form-check').append(
                                `<div class="invalid-feedback d-block">${value[0]}</div>`
                            );
                            checkbox.addClass("is-invalid");
                        }
                    });

                    return false;
                } else {
                    if (response.status === 200) {
                        $("#social_form_update_next").prop("disabled", true);
                        $("#social_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        // Move to next tab (Tab 4)
                        $("#tab4_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").addClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab2_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab2").removeClass("active");

                        $("#tab3_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab3").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function (error) {
                toast("An error occurred. Please try again.", "error");
            },
            complete: function () {
                $("#social_form_update_next").removeAttr("disabled");
            },
        });
    });


    // Document form submission
    $("#documents_submit").on("click", function () {
        var formData = new FormData($("#document_form_update")[0]);
        var formUrl = baseUrl + "/company/update/document-info";

        // Clear previous errors
        $(".is-invalid").removeClass("is-invalid");
        $(".invalid-feedback").remove();

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#documents_submit").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $("#document_form_update").addClass("was-invalid");


                     if (response.errors.documents) {
                        const errorContainer = $('<div class="text-danger mb-3 text-center"></div>')
                            .text(response.errors.documents[0]);
                        $("#document_form_update .card-body").prepend(errorContainer);
                     }

                    $.each(response.errors, function (key, value) {
                        const parts = key.match(/documents\.(\d+)\.(\w+)/);
                        if (parts) {
                            const index = parts[1];
                            const field = parts[2];
                            const input = $(`[name="documents[${index}][${field}]"]`);

                            input.addClass("is-invalid");
                            input.after(`<div class="invalid-feedback">${value[0]}</div>`);
                        }
                    });
                } else if (response.status === 200) {
                    toast(response.msg, 'success');
                    setTimeout(() => {
                        window.location.href = baseUrl + "/company";
                    }, 1000);
                } else {
                    toast("Something went wrong.", "error");
                }
            },
            error: function (error) {
                toast("An error occurred. Please try again.", "error");
            },
            complete: function () {
                $("#documents_submit").removeAttr("disabled");
            }
        });
    });



    //previous button handling

    // For Previous Button - Handling
    $('#address_info_update_prev').on('click', function () {
        $("#tab1_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab1").addClass("active");

        $("#tab2_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab3_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab3").removeClass("active");

        $("#tab4_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");
    });



    $('#social_form_update_prev').on('click', function () {
        $("#tab2_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab2").addClass("active");

        $("#tab1_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab1").removeClass("active");

        $("#tab3_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab3").removeClass("active");

        $("#tab4_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");
    });


    // Previous button handler
    $('#document_update_prev').on('click', function () {
        $("#tab3_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab3").addClass("active");

        $("#tab1_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab1").removeClass("active");

        $("#tab2_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab4_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");
    });



});
