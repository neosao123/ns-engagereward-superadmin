$(document).ready(function () {
    getDataTable();
function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-social-media-app")) {
        $("#dt-social-media-app").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-social-media-app").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/social-media-apps/list",
            type: "GET",
            data: {
            },
            complete: function (response) {
                operations();
            },
        },
    });
}
function operations() {
        //delete user
	
		$("a.btn-delete").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm Delete!",
				content: "Do you want to delete this social media app?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/social-media-apps/" + id,
							type: "DELETE",
							data: {
								'_token': csrfToken,
							},
							success: function (response) {
								if (response.success) {
									toast("Social media app deleted successfully.", "success");
									getDataTable();
								} else {
									toast(response.error || "An error occurred while deleting the social media app.", "error");
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
});
