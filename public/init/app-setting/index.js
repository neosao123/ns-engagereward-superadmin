
// Initialize DataTables
$(function () {
    var table = $('#dt-app-setting').DataTable({
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/app-settings/fetch-list",
        columns: [
            { data: 0, name: 'action', orderable: false, searchable: false },
            { data: 1, name: 'setting_name' },
            { data: 2, name: 'setting_value' },
            { data: 3, name: 'is_update_compulsory' },
            { data: 4, name: 'status' },
			{ data: 5, name: 'created_at' }
        ],
		 "order": [[5, "desc" ]] 
    });
});
