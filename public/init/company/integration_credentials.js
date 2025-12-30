$(function () {
    // Add new credential row
    $('.social-media-card').each(function() {
        const $card = $(this);
        const socialId = $card.data('social-id');
        let credentialCounter = $card.find('.document-row').length;

        // Add new credential row for this social media
        $card.find('.add-integration-credentials').click(function() {
            const newRow = `
            <div class="document-row mb-3 border-bottom pb-3">
                <div class="row">
                    <input type="hidden" name="integration_credentials[${credentialCounter}][id]" value="#">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <input type="text" class="form-control type"
                               name="integration_credentials[${credentialCounter}][type]"
                               placeholder="e.g. API key, Secret Key etc.">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control value"
                               name="integration_credentials[${credentialCounter}][value]">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-document">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;

            $card.find('.documents-repeater').append(newRow);
            credentialCounter++;
            updateRemoveButtons($card);
        });
    });

    // Remove credential row with confirmation
    $(document).on('click', '.remove-document', function() {
        const row = $(this).closest('.document-row');
        const credentialId = row.find('input[name*="[id]"]').val();
        const credentialType = row.find('input[name*="[type]"]').val() || 'credential';
        const $card = $(this).closest('.social-media-card');

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: `Are you sure you want to delete this ${credentialType} credential?`,
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: {
                    text: "Delete",
                    btnClass: "btn-danger",
                    action: function() {
                        if (credentialId && credentialId !== '#') {
                            $.ajax({
                                url: baseUrl + "/company/integration-credentials/" + credentialId,
                                type: "DELETE",
                                data: {
                                    '_token': $('meta[name="csrf_token"]').attr('content')
                                },
                                beforeSend: function() {
                                    row.find('.remove-document').html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                                },
                                success: function(response) {
                                    if (response.success) {
                                        row.remove();
                                        toast("Credential deleted successfully.", "success");
                                        reindexCredentialRows($card);
                                    } else {
                                        toast(response.message || "Failed to delete credential.", "error");
                                        row.find('.remove-document').html('<i class="fas fa-trash"></i>').prop('disabled', false);
                                    }
                                },
                                error: function(xhr) {
                                    const errorMessage = xhr.responseJSON?.message || "An error occurred while deleting the credential.";
                                    toast(errorMessage, "error");
                                    row.find('.remove-document').html('<i class="fas fa-trash"></i>').prop('disabled', false);
                                }
                            });
                        } else {
                            row.remove();
                            toast("Credential removed.", "success");
                            reindexCredentialRows($card);
                        }
                    }
                },
                cancel: function() {
                    // Do nothing on cancel
                }
            }
        });
    });

    // Reindex credential rows for a specific card
    function reindexCredentialRows($card) {
        let index = 0;
        $card.find('.document-row').each(function() {
            $(this).find('input').each(function() {
                const name = $(this).attr('name')
                    .replace(/integration_credentials\[\d+\]/, `integration_credentials[${index}]`);
                $(this).attr('name', name);
            });
            index++;
        });
        updateRemoveButtons($card);
    }

    // Update remove buttons visibility
    function updateRemoveButtons($card) {
        const $rows = $card.find('.document-row');
        const $removeButtons = $card.find('.remove-document');

        if ($rows.length === 1) {
            $removeButtons.hide();
        } else {
            $removeButtons.show();
        }
    }

	// Handle form submission for each social media
	$(document).on('click', '.submit-btn', function() {
		const $btn = $(this);
		const $form = $btn.closest('.social-media-form');
		const $card = $btn.closest('.social-media-card');
		const formData = new FormData($form[0]);
		const formUrl = baseUrl + "/company/store-integration-credentials";

		// Clear previous errors for this specific form only
		//$card.find(".is-invalid").removeClass("is-invalid");
		$card.find(".invalid-feedback").remove(); // Remove existing error messages

		$.ajax({
			type: "POST",
			url: formUrl,
			data: formData,
			dataType: "JSON",
			processData: false,
			contentType: false,
			beforeSend: function() {
				$btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
			},
			success: function(response) {
				if (response.hasOwnProperty("errors")) {
					// Process each error
					$.each(response.errors, function(key, value) {
						console.log("Processing error:", key, value); // Debugging

						// Handle different error key formats
						if (key.includes('integration_credentials')) {
							// Try multiple formats to match the error keys
							let matches = key.match(/integration_credentials\.(\d+)\.(\w+)/) ||
										  key.match(/integration_credentials\[(\d+)\]\[(\w+)\]/);

							if (matches) {
								const index = matches[1];
								const field = matches[2];
								const selector = `[name="integration_credentials[${index}][${field}]"]`;
								const $input = $card.find(selector);

								if ($input.length) {
									//$input.addClass("is-invalid");
									// Create error message div if it doesn't exist
									if ($input.next('.invalid-feedback').length === 0) {
										$input.after(`<div class="invalid-feedback">${value[0]}</div>`);
									} else {
										$input.next('.invalid-feedback').text(value[0]);
									}
								} else {
									console.warn("Input not found for selector:", selector);
								}
							}
						} else {
							// Handle non-nested errors
							const $input = $card.find(`[name="${key}"]`);
							if ($input.length) {
								//$input.addClass("is-invalid");
								if ($input.next('.invalid-feedback').length === 0) {
									$input.after(`<div class="invalid-feedback">${value[0]}</div>`);
								} else {
									$input.next('.invalid-feedback').text(value[0]);
								}
							}
						}
					});
				} else if (response.status === 200) {
					toast(response.msg, 'success');
					setTimeout(() => {
						window.location.reload();
					}, 1000);
				} else {
					toast(response.msg || "Something went wrong.", "error");
				}
			},
			error: function(xhr) {
				let errorMessage = "An error occurred. Please try again.";
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMessage = xhr.responseJSON.message;
				} else if (xhr.responseJSON && xhr.responseJSON.msg) {
					errorMessage = xhr.responseJSON.msg;
				}
				toast(errorMessage, "error");
			},
			complete: function() {
				$btn.prop("disabled", false).html('Save');
			}
		});
	});

    // Initialize remove buttons visibility
    $('.social-media-card').each(function() {
        updateRemoveButtons($(this));
    });
});
