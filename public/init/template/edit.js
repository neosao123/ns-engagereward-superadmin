$(document).ready(function() {
    $('#summernote_edit').summernote({
        placeholder: 'Enter template content here...',
        tabsize: 2,
        height: 300,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture']]
        ]
    });

    // Handle Form submission
    $('#templateEditForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#template_id').val();
        var formData = new FormData(this);
        
        // Remove old errors
        $('.error').text('');

        $.ajax({
            url: baseUrl + "/templates/" + id,
            type: "POST", // Laravel spoofing using _method=PUT in form
            data: formData,
            processData: false,
            contentType: false,
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
                    setTimeout(function() {
                        window.location.href = baseUrl + "/templates";
                    }, 1500);
                } else if (response.status == 'error') {
                    // Display validation errors
                    $.each(response.errors, function(key, value) {
                        $('#' + key + '_error').text(value[0]);
                    });
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });

    // Preview
    $('#btnPreviewEdit').on('click', function() {
        var content = $('#summernote_edit').summernote('code');
        
        $.ajax({
            url: baseUrl + "/templates/preview",
            type: "GET",
            data: { description: content },
            success: function(response) {
                $('#previewContent').html(response);
                $('#previewModal').modal('show');
            }
        });
    });
});
