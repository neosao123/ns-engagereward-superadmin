$(document).ready(function () {

	flatpickr("#from_date", {
            dateFormat: "d-m-Y",
            onChange: calculateToDate
        });


	flatpickr("#to_date", {
		dateFormat: "d-m-Y",
		allowInput: false
	});


	const monthsInput = document.getElementById('subscription_months');
        const priceInput = document.getElementById('subscription_per_month_price');
        const totalInput = document.getElementById('subscription_total_price');
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');

       function calculateTotal() {
			const months = parseInt(monthsInput.value);
			const price = parseFloat(priceInput.value);
			const discountType = $('#discount_type').val();
			const discountValue = parseFloat($('#discount_value').val());

			let total = 0;

			if (!isNaN(months) && !isNaN(price)) {
				total = months * price;

				if (!isNaN(discountValue) && discountValue > 0) {
					if (discountType === 'flat') {
						total -= discountValue;
					} else if (discountType === 'percentage') {
						total -= (total * discountValue / 100);
					}
				}

				if (total < 0) total = 0;

				totalInput.value = total.toFixed(2);
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


        monthsInput.addEventListener('input', () => {
            calculateTotal();
            calculateToDate();
        });

        priceInput.addEventListener('input', calculateTotal);

		$('#discount_type, #discount_value').on('change input', function () {
			calculateTotal();
		});



	//get social media app list
    $('.social_media').select2({
        placeholder: 'Select',
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
            url: baseUrl + "/subscription-plan/fetch/social-media-app",
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


	 // Initialize select2
    $('#currency').select2({
        placeholder: 'Select',
        allowClear: true,
        ajax: {
            url: baseUrl + "/subscription-plan/fetch/currency",
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


    function update_subscription(formData) {
        let formUrl = `${baseUrl}/subscription-plan/${id}`;

        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#subscription-update").prop("disabled", true);
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
                        $("#form-update-subscription").removeClass("was-invalid");
                        $(".backend-error").remove();

                        setTimeout(() => {
                            window.location.href = `${baseUrl}/subscription-plan`;
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
                $("#subscription-update").prop("disabled", false);
            },
        });
    }

    // Submit handler
    $("#subscription-update").on("click", function () {
        if ($("#form-update-subscription").valid()) {
            let formData = new FormData($("#form-update-subscription")[0]);
            update_subscription(formData);
        }
    });
});
