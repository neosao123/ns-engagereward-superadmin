$(function () {

		$('#change_plan').on('click', function () {
			$('#extend_subscription').hide(); // hide extend
			$('#change_plan_section').removeClass('d-none').show(); // show change
		});


		$('#cancel_change_plan').on('click', function () {
			$('#change_plan_section').hide(); // hide change
			$('#extend_subscription').show(); // show extend
		});

        $('#cancel_plan_btn').on('click', function () {
			window.location.reload();
		});

		flatpickr("#from_date", {
            dateFormat: "d-m-Y",
            onChange: calculateToDate
        });


		flatpickr("#to_date", {
            dateFormat: "d-m-Y"
        });


        const monthsInput = document.getElementById('subscription_months');
        const priceInput = document.getElementById('subscription_per_month_price');
        const totalInput = document.getElementById('subscription_total_price');
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');

        function calculateTotal() {
            const months = parseInt(monthsInput.value);
            const price = parseFloat(priceInput.value);
            if (!isNaN(months) && !isNaN(price)) {
                totalInput.value = (months * price).toFixed(2);
            } else {
                totalInput.value = '';
            }
        }

        function calculateToDate() {
			const fromDate = fromDateInput.value;
			const months = parseInt(monthsInput.value);
			if (fromDate && months) {
				// Convert from 'd-m-Y' to Date object
				const [day, month, year] = fromDate.split('-');
				const startDate = new Date(year, month - 1, day); // JS months are 0-based

				// Add months
				startDate.setMonth(startDate.getMonth() + months);

				// Format to d-m-Y
				const formattedDay = String(startDate.getDate()).padStart(2, '0');
				const formattedMonth = String(startDate.getMonth() + 1).padStart(2, '0');
				const formattedYear = startDate.getFullYear();

				const formattedDate = `${formattedDay}-${formattedMonth}-${formattedYear}`;
				toDateInput._flatpickr.setDate(formattedDate); // Safely update flatpickr input
			} else {
				toDateInput.value = '';
			}
		}

       /* monthsInput.addEventListener('input', () => {
            calculateTotal();
            calculateToDate();
        });*/

        //priceInput.addEventListener('input', calculateTotal);

	$(document).on('click', '#extend_plan_btn', function() {
		var subscriptionId = $(this).data('val');

		$.ajax({
			url: '/company/subscription-plan',
			method: 'GET',
			data: { id: subscriptionId },
			success: function(response) {
				if (response.success) {
					var purchase = response.data;

					// Fill normal inputs
					$('input').each(function() {
						var inputId = $(this).attr('id') || '';
						var labelText = $(this).prev('label').text().toLowerCase();

						if(inputId === 'from_date' || inputId === 'to_date') {
							// We'll set date with flatpickr below
							return;
						}

						if (labelText.includes('subscription title')) {
							$(this).val(purchase.subscription_title);
						} else if (labelText.includes('month') && !labelText.includes('per')) {
							$(this).val(purchase.subscription_months);
						} else if (labelText.includes('per month')) {
							$(this).val(purchase.subscription_per_month_price);
						} else if (labelText.includes('total price')) {
							$(this).val(purchase.subscription_total_price);
						}
					});



					// Show modal
					$('#documentModal').modal('show');
				} else {
					alert('Subscription data not found');
				}
			},
			error: function() {
				alert('Something went wrong');
			}
		});
	});


    function update_subscription(formData) {
        let formUrl = `${baseUrl}/company/update/subscription-plan/${id}`;

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#expand_plan").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
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
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-extend-subscription").removeClass("was-invalid");
                        $(".backend-error").remove();

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function () {
                toast("Something went wrong.", "error");
            },
            complete: function () {
                $("#expand_plan").prop("disabled", false);
            },
        });
    }

    // Submit handler
    $("#expand_plan").on("click", function () {
        if ($("#form-extend-subscription").valid()) {
            let formData = new FormData($("#form-extend-subscription")[0]);
            update_subscription(formData);
        }
    });
	$('#subscriptionModal').on('shown.bs.modal', function () {
		if (!$('.subscription').hasClass("select2-hidden-accessible")) {  // Initialize only once
			$('.subscription').select2({
				placeholder: 'Select Subscription',
				allowClear: true,
				//minimumInputLength: 1,
				dropdownParent: $('#subscriptionModal'),  // <--- Important for modal
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
					url: baseUrl + "/company/fetch/subscriptions-renew",
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
		}
	});

	$(document).on('click', '#change_plan_btn', function() {

            $('#subscriptionModal').modal('show');

	});
    function add_subscription(formData) {
        let formUrl = `${baseUrl}/company/add/subscription-plan/${id}`;

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#add_subscription_btn").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
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
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-subscription-add").removeClass("was-invalid");
                        $(".backend-error").remove();

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function () {
                toast("Something went wrong.", "error");
            },
            complete: function () {
                $("#add_subscription_btn").prop("disabled", false);
            },
        });
    }

    // Submit handler
    $("#add_subscription_btn").on("click", function () {
        if ($("#form-subscription-add").valid()) {
            let formData = new FormData($("#form-subscription-add")[0]);
            add_subscription(formData);
        }
    });



	  $("#suspend_plan_btn").on("click", function () {
		  var status=$(this).data("id");
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm !",
                content: "Do you want to suspend this plan?",
                theme: "modern",
                draggable: false,
                type: "red",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: baseUrl + "/company/suspend/subscription-plan/" + id,
                            type: "POST",
                            data: {
								'status':status,
                                '_token': csrfToken,
                            },
                            success: function (response) {
                                if (response.success) {
                                    toast("Subscription plan is suspened.", "success");
                                    setTimeout(() => {
											window.location.reload();
										}, 2000);
                                } else {
                                    toast(response.error || "An error occurred while Subscription plan suspened", "error");
                                }
                            },
                            error: function (xhr) {
                                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                    ? xhr.responseJSON.message
                                    : "An error occurred while processing your request.";
                                toast(errorMessage, "error");
                            }
                        });
                    },
                    cancel: function () { }
                }
            });
        });


		 $("#activate_plan_btn").on("click", function () {
		  var status=$(this).data("id");
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm !",
                content: "Do you want to active this plan?",
                theme: "modern",
                draggable: false,
                type: "red",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: baseUrl + "/company/suspend/subscription-plan/" + id,
                            type: "POST",
                            data: {
								'status':status,
                                '_token': csrfToken,
                            },
                            success: function (response) {
                                if (response.success) {
                                    toast("Subscription plan is active.", "success");
                                    setTimeout(() => {
											window.location.reload();
									}, 2000);
                                } else {
                                    toast(response.error || "An error occurred while Subscription plan active", "error");
                                }
                            },
                            error: function (xhr) {
                                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                    ? xhr.responseJSON.message
                                    : "An error occurred while processing your request.";
                                toast(errorMessage, "error");
                            }
                        });
                    },
                    cancel: function () { }
                }
            });
        });



});
