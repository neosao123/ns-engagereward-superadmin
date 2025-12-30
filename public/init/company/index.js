$(function () {


    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

    // Search/filter button click
    $('#search_filter').on('click', function (e) {
        var company_key = $("#company_key").val();
        var company_name = $("#company_name").val();
        var email = $("#email").val();
        var phone = $("#phone").val();

        getDataTable(company_key, company_name, email, phone);
    });

    // Reset filter button
    $("#reset_filter").click(function () {
        window.location.reload();
        var dataTable = $("#dt-company").DataTable();
        dataTable.search('').columns().search('').state.clear().draw();
    });

    // Excel download button
    $("#btnExcelDownload").on("click", function (e) {
        var company_key = $("#company_key").val();
        var company_name = $("#company_name").val();
        var email = $("#email").val();
        var phone = $("#phone").val();

        $.ajax({
            type: "get",
            url: baseUrl + "/company/exceldownload",
            data: {
                company_key: company_key,
                company_name: company_name,
                email: email,
                phone: phone
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'text/csv' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'company_details.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function () {
                alert("An error occurred while downloading the CSV file.");
            }
        });
    });

    // PDF download button
    $("#btnPdfDownload").on("click", function (e) {
        var company_key = $("#company_key").val();
        var company_name = $("#company_name").val();
        var email = $("#email").val();
        var phone = $("#phone").val();


        $.ajax({
            type: "get",
            url: baseUrl + "/company/pdfdownload",
            data: {
                export: 1,
                company_key: company_key,
                company_name: company_name,
                email: email,
                phone: phone
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'company_details.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function () {
                alert("An error occurred while downloading the PDF file.");
            }
        });
    });

    // Select2 for company_key
    $('#company_key').select2({
        placeholder: 'Select Company Key',
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
            url: baseUrl + "/company/fetch/company_key",
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

    // Select2 for company_name
    $('#company_name').select2({
        placeholder: 'Select Company Name',
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
            url: baseUrl + "/company/fetch/company_name",
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

    // Select2 for email
    $('#email').select2({
        placeholder: 'Select Email',
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
            url: baseUrl + "/company/fetch/email",
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

    // Select2 for phone
    $('#phone').select2({
        placeholder: 'Select Phone',
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
            url: baseUrl + "/company/fetch/phone",
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


    // Load datatable initially with empty filters
    getDataTable("", "", "", "");

    function operations() {
        // Delete company
        $("a.btn-delete").on("click", function () {
            const id = $(this).data("id");

            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Delete!",
                content: "Do you want to delete this company?",
                theme: "modern",
                draggable: false,
                type: "red",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: baseUrl + "/company/" + id,
                            type: "DELETE",
                            data: {
                                '_token': csrfToken,
                            },
                            success: function (response) {
                                if (response.success) {
                                    toast("Company deleted successfully.", "success");
                                    getDataTable();
                                } else {
                                    toast(response.error || "An error occurred while deleting the company.", "error");
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

       $(".btn-account-action").on("click", function () {
            const id = $(this).data("id");

            // Open the modal first
            $('#accountStatusModal').modal('show');

            // Reset status and reason fields
            $('#account_status').val('');
            $('#reason').val('');
            $('#reason').parent().hide();

            // Toggle reason field visibility based on status selection
            $('#account_status').on('change', function () {
                const selectedStatus = $(this).val();
                if (selectedStatus === 'suspended') {
                    $('#reason').parent().show(); // show reason field for "Cancelled"
                    $('#reason').val(''); // Clear the reason value
                } else{
                    $('#reason').parent().hide(); // hide reason field for "Completed"
                }
            });

            // Handle the Submit button click inside the modal
            $('#accountStatusModal .btn-primary').off('click').on('click', function () {

				const status = $('#account_status').val();
                const reason = $('#reason').val();

                // Ensure status is selected
                if (!status) {
                    toast("Please select a status.", "error");
                    return;
                }

                // Ensure reason is provided if status is "suspended"
                if (status === 'suspended' && !reason) {
                    toast("Please provide a reason for suspended.", "error");
                    return;
                }

                // Confirmation dialog before proceeding with the AJAX request
                $.confirm({
                    icon: "fa fa-warning",
                    title: "Confirm Status Change!",
					content: `Are you sure you want to ${status.toLowerCase()} the account status change?`,
                    theme: "modern",
                    draggable: false,
                    type: "blue",
                    typeAnimated: true,
                    buttons: {
                        confirm: function () {
                            // Proceed with the AJAX call after confirmation
                            $.ajax({
                                url: baseUrl + "/company/update-status/" + id,
                                type: "post",
                                data: {
                                    '_token': csrfToken,
                                    'status': status,
                                    'reason': reason

                                },
                                success: function (response) {
                                    console.log(response)
									if (response.status==200) {
                                        toast(response.message, "success");

                                        $('#accountStatusModal').modal('hide');
                                        setTimeout(() => {
											window.location.reload();
										}, 2000);
									} else {
                                        toast(response.message || "An error occurred while change the status of account.", "error");
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
                        cancel: function () {
                            // If the user cancels, nothing happens
                        }
                    }
                });
            });
        });

    }

    // DataTable initialization with server-side processing
    function getDataTable(company_key, company_name, email, phone) {
        $.fn.DataTable.ext.errMode = "none";

        if ($.fn.DataTable.isDataTable("#dt-company")) {
            $("#dt-company").DataTable().clear().destroy();
        }

        var dataTable = $("#dt-company").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/company/list",
                type: "GET",
                data: {
                    company_key: company_key,
                    company_name: company_name,
                    email: email,
                    phone: phone
                },
                complete: function (response) {
                    operations();
                },
            },
        });
    }
});
