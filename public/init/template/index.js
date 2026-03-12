$(document).ready(function() {
    var table = $('#dt-templates').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        stateSave: true,
        ajax: {
            url: baseUrl + "/templates/list",
            type: "GET"
        },
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // Delete Template
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        $.confirm({
            title: 'Confirm!',
            content: 'Are you sure you want to delete this template?',
            buttons: {
                confirm: {
                    text: 'Delete',
                    btnClass: 'btn-danger',
                    action: function() {
                        $.ajax({
                            url: baseUrl + "/templates/" + id,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    Toastify({
                                        text: response.message,
                                        duration: 3000,
                                        style: {
                                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                                        }
                                    }).showToast();
                                    table.ajax.reload();
                                } else {
                                    Toastify({
                                        text: response.message,
                                        duration: 3000,
                                        style: {
                                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                                        }
                                    }).showToast();
                                }
                            }
                        });
                    }
                },
                cancel: function() {}
            }
        });
    });
});
