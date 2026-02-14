$(function () {

    getDataTable("", "");
    function operations() {
        //delete user

		$("a.btn-delete").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm Delete!",
				content: "Do you want to delete this subscription plan?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/subscription-plan/" + id,
							type: "DELETE",
							data: {
								'_token': csrfToken,
							},
							success: function (response) {
								if (response.success) {
									toast("Subscription plan deleted successfully.", "success");
									getDataTable();
								} else {
									toast(response.message || "An error occurred while deleting the subscription plan.", "error");
								}
							},
							error: function (xhr) {
								// Generic error handler for any server-side errors
								const errorMessage = xhr.responseJSON && xhr.responseJSON.message
									? xhr.responseJSON.message
									: "An error occurred while processing your request.";
								toast(errorMessage, "error");
							}
						});
					},
					cancel: function () {

					}
				}
			});
       });

    }
  //user list
    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-subscriptions")) {
            $("#dt-subscriptions").DataTable().clear().destroy();
        }
        var dataTable = $("#dt-subscriptions").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/subscription-plan/list",
                type: "GET",
                data: {

                },
                complete: function (response) {
                    operations();
                },
            },
        });
    }
});
