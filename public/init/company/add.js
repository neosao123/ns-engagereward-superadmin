
$(function () {

    let documentCounter = 1;

    // Initialize preview for existing rows
    function initFilePreview(input) {
        const previewContainer = $(input).siblings('.file-preview');
        const imgPreview = previewContainer.find('.img-preview');
        const pdfPreview = previewContainer.find('.pdf-preview');

        // Show placeholder initially
        imgPreview.attr('src', companyDoc).show();
        previewContainer.show();
        pdfPreview.hide();
    }

    // Initialize previews for existing rows
    $('.document-file').each(function () {
        initFilePreview(this);
    });

    // Add new document row
    $('#add-document').click(function () {
        const newRow = `
        <div class="document-row mb-3 border-bottom pb-3">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control document-type" name="documents[${documentCounter}][type]" placeholder="e.g. COI, PAN etc.">
                </div>

                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control document-number" name="documents[${documentCounter}][number]">
                </div>

                <div class="col-md-3 mb-2">
                    <input type="file" class="form-control document-file" name="documents[${documentCounter}][file]" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-preview mt-2">
                        <img class="img-preview img-thumbnail" src="${companyDoc}" style="max-height: 100px;">
                        <div class="pdf-preview" style="display: none;">
                            <i class="fas fa-file-pdf text-danger fa-3x"></i>
                            <p class="small mb-0"></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-1 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-document">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        `;

        $('#documents-repeater').append(newRow);
        documentCounter++;

        // Show remove buttons on all rows except first
        $('.document-row').each(function () {
            if ($(this).is(':first-child')) {
                $(this).find('.remove-document').hide();
            } else {
                $(this).find('.remove-document').show();
            }
        });
    });


    // Remove document row with confirmation (non-AJAX version)
    $(document).on('click', '.remove-document', function () {

        const row = $(this).closest('.document-row');
        const docType = row.find('input[name*="[type]"]').val() || '';

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
                        // Remove the row
                        row.remove();

                        // Reindex the array names
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
                        if ($('.document-row').length === 1) {
                            $('.remove-document').hide();
                        }

                        toast("Document removed.", "success");
                    }
                },
                cancel: function () {
                    // Do nothing on cancel
                }
            }
        });
    });

    // Remove document row
    /*$(document).on('click', '.remove-document', function() {
        $(this).closest('.document-row').remove();

        // Reindex the array names
        let index = 0;
        $('.document-row').each(function() {
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name').replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', name);
            });
            index++;
        });

        documentCounter = index;

        // Hide remove button if only one row left
        if ($('.document-row').length === 1) {
            $('.remove-document').hide();
        }
    });*/

    // File preview handler
    $(document).on('change', '.document-file', function () {
        const file = this.files[0];
        const previewContainer = $(this).siblings('.file-preview');
        const imgPreview = previewContainer.find('.img-preview');
        const pdfPreview = previewContainer.find('.pdf-preview');

        previewContainer.show();

        if (file) {
            if (file.type.match('image.*')) {
                // Image preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    imgPreview.attr('src', e.target.result).show();
                    pdfPreview.hide();
                }
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                // PDF preview
                pdfPreview.find('p').text(file.name);
                pdfPreview.show();
                imgPreview.hide();
            } else {
                // Other file types - show placeholder
                imgPreview.attr('src', '/assets/img/docs-placeholder.png').show();
                pdfPreview.hide();
            }
        } else {
            // No file selected - show placeholder
            imgPreview.attr('src', '/assets/img/docs-placeholder.png').show();
            pdfPreview.hide();
        }
    });


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
            $('#image_preview').addClass('d-none').hide();
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
    // Select2 - Country
    $('.country_code').select2({
        placeholder: 'Select Country',
        allowClear: true,
        // minimumInputLength: 1,  // removed
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


    // Select2 - subscription
    $('.subscription').select2({
        placeholder: 'Select Subscription',
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
            url: baseUrl + "/company/fetch/subscriptions",
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


    // Basic Info Submit Handler
    $("#basic_info").on("click", function () {
        var formData = new FormData($("#basic_info_form")[0]);
        var formUrl = baseUrl + "/company/add/basic-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#basic_info").prop("disabled", true);
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

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

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
                        $("#basic_info").prop("disabled", true);
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

                        $("#tab5_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#basic_info").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#basic_info").removeAttr("disabled");
            },
        });
    });

    // Tab 1 Click Handler
    $('#tab1_nav a').on("click", function (event) {
        $("#tab1_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab1").addClass("active");

        $("#tab2_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab3_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab3").removeClass("active");

        $("#tab4_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");

        $("#tab5_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab5").removeClass("active");
    });

    // Address Info Submit Handler
    $("#address_info").on("click", function () {
        var formData = new FormData($("#addr_info_form")[0]);
        var formUrl = baseUrl + "/company/add/address-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#address_info").prop("disabled", true);
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

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

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
                        $("#address_info").prop("disabled", true);
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

                        $("#tab5_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#address_info").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#address_info").removeAttr("disabled");
            },
        });
    });

    // Tab 2 Click Handler
    $('#tab2_nav a').on("click", function (event) {
        event.preventDefault();
        var formData = new FormData($("#basic_info_form")[0]);
        var formUrl = baseUrl + "/company/add/basic-info";

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#basic_info").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove();
                    $("#basic_info_form").addClass("was-invalid");

                    $("#tab1_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab1").addClass("active");

                    $("#tab2_nav a").removeClass("active").removeClass("disabled");
                    $("#bootstrap-wizard-validation-tab2").removeClass("active");

                    $("#tab3_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

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
                        $("#basic_info").prop("disabled", true);
                        $("#basic_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        $("#tab2_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab2").addClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab3_nav a").removeClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab3").removeClass("active");

                        $("#tab4_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").removeClass("active");

                        $("#tab5_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function () {
                $("#basic_info").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#basic_info").removeAttr("disabled");
            },
        });
    });

    // Social Info Submit Handler
    $("#social_form_add_next").on("click", function () {
        var formData = new FormData($("#social_form_add")[0]);
        var formUrl = baseUrl + "/company/add/social-info";

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
                $("#social_form_add_next").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $("#social_form_add").addClass("was-invalid");

                    // Reset to tab 3 on error
                    $("#tab3_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab3").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab2_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab2").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

                    // Handle errors
                    if (response.errors.social_apps) {
                        const errorContainer = $('<div class="text-danger mb-3 text-center"></div>')
                            .text(response.errors.social_apps[0]);
                        $("#social_form_add .card-body").prepend(errorContainer);
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
                        $("#social_form_add_next").prop("disabled", true);
                        $("#social_form_add").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        // Move to next tab (Tab 5)
                        $("#tab4_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").addClass("active");

                        // deactivate others
                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab2_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab2").removeClass("active");

                        $("#tab3_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab3").removeClass("active");

                        $("#tab5_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function (error) {
                toast("An error occurred. Please try again.", "error");
            },
            complete: function () {
                $("#social_form_add_next").removeAttr("disabled");
            },
        });
    });

    // Tab 3 Click Handler
    $('#tab3_nav a').on("click", function (event) {
        event.preventDefault();
        var formData = new FormData($("#addr_info_form")[0]);
        var formUrl = baseUrl + "/company/add/address-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#address_info").prop("disabled", true);
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

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

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
                        $("#address_info").prop("disabled", true);
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

                        $("#tab5_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#address_info").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#address_info").removeAttr("disabled");
            },
        });
    });


    // Subscription Info Submit Handler
    $("#sub_info").on("click", function () {
        var formData = new FormData($("#sub_info_form")[0]);
        var formUrl = baseUrl + "/company/add/sub-info";

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
                $("#sub_info").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $("#sub_info_form").addClass("was-invalid");

                    // Reset to tab 4 on error
                    $("#tab4_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab4").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab2_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab2").removeClass("active");

                    $("#tab3_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

                    // Show errors
                    $.each(response.errors, function (key, value) {
                        let errorField = $(`[name='${key}']`);
                        if (errorField.hasClass('select2-hidden-accessible')) {
                            errorField.next('.select2').after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                        } else {
                            errorField.addClass("is-invalid");
                            errorField.after(`<div class="invalid-feedback">${value[0]}</div>`);
                        }
                    });

                    return false;
                } else {
                    if (response.status === 200) {
                        $("#sub_info").prop("disabled", true);
                        $("#sub_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        // Move to final tab
                        $("#tab5_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").addClass("active");

                        $("#tab4_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab4").removeClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab2_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab2").removeClass("active");

                        $("#tab3_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab3").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#sub_info").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#sub_info").removeAttr("disabled");
            },
        });
    });


    // Tab 4 Click Handler
    $('#tab4_nav a').on("click", function (event) {
        event.preventDefault();
        var formData = new FormData($("#sub_info_form")[0]);
        var formUrl = baseUrl + "/company/add/sub-info";
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#sub_info").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove();
                    $("#sub_info_form").addClass("was-invalid");

                    $("#tab2_nav a").addClass("active");
                    $("#bootstrap-wizard-validation-tab2").addClass("active");

                    $("#tab1_nav a").removeClass("active");
                    $("#bootstrap-wizard-validation-tab1").removeClass("active");

                    $("#tab3_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab3").removeClass("active");

                    $("#tab4_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab4").removeClass("active");

                    $("#tab5_nav a").removeClass("active").addClass("disabled");
                    $("#bootstrap-wizard-validation-tab5").removeClass("active");

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
                        $("#sub_info").prop("disabled", true);
                        $("#sub_info_form").removeClass("was-invalid");
                        $(".invalid-feedback").remove();

                        $("#tab4_nav a").addClass("active").removeClass("disabled");
                        $("#bootstrap-wizard-validation-tab4").addClass("active");

                        $("#tab1_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab1").removeClass("active");

                        $("#tab2_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab2").removeClass("active");

                        $("#tab3_nav a").removeClass("active");
                        $("#bootstrap-wizard-validation-tab3").removeClass("active");

                        $("#tab5_nav a").removeClass("active").addClass("disabled");
                        $("#bootstrap-wizard-validation-tab5").removeClass("active");
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#sub_info").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#sub_info").removeAttr("disabled");
            },
        });
    });

    // Document form submission
    $("#documents_submit").on("click", function () {
        var formData = new FormData($("#document_form_add")[0]);
        var formUrl = baseUrl + "/company/add/document-info";

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
                    $("#document_form_add").addClass("was-invalid");


                     if (response.errors.documents) {
                        const errorContainer = $('<div class="text-danger mb-3 text-center"></div>')
                            .text(response.errors.documents[0]);
                        $("#document_form_add .card-body").prepend(errorContainer);
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


    // Previous Button Handlers
    $('#address_info_prev').on('click', function () {
        $("#tab1_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab1").addClass("active");

        $("#tab2_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab3_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab3").removeClass("active");

        $("#tab4_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");

        $("#tab5_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab5").removeClass("active");
    });

    $('#social_form_add_prev').on('click', function () {
        $("#tab2_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab2").addClass("active");

        $("#tab1_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab1").removeClass("active");

        $("#tab3_nav a").removeClass("active").removeClass("disabled");
        $("#bootstrap-wizard-validation-tab3").removeClass("active");

        $("#tab4_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");

        $("#tab5_nav a").removeClass("active").addClass("disabled");
        $("#bootstrap-wizard-validation-tab5").removeClass("active");
    });


    // Previous button handler
    $('#document_add_prev').on('click', function () {
        $("#tab3_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab3").addClass("active");

        $("#tab1_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab1").removeClass("active");

        $("#tab2_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab4_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");

        $("#tab5_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab5").removeClass("active");
    });


    $('#sub_info_prev').on('click', function () {
        $("#tab3_nav a").addClass("active");
        $("#bootstrap-wizard-validation-tab3").addClass("active");

        $("#tab1_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab1").removeClass("active");

        $("#tab2_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab2").removeClass("active");

        $("#tab4_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab4").removeClass("active");

        $("#tab5_nav a").removeClass("active");
        $("#bootstrap-wizard-validation-tab5").removeClass("active");
    });





});
