@extends('layout.default.master', ['pageTitle' => 'Add Template'])

@push('styles')
<!-- Summernote CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
<style>
    .error { color: red; }
    .note-editor { margin-bottom: 20px; }
</style>
@endpush

@section('content')
<div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
        <i class="fas fa-circle fa-stack-2x text-300"></i>
        <i class="fa-inverse fa-stack-1x text-primary fas fa-plus" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">Add Template</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @if(isRolePermission(auth()->user()->role_id, 'Dashboard.View'))
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                @endif
                @if(isRolePermission(auth()->user()->role_id, 'Template.List'))
                <li class="breadcrumb-item"><a href="{{ url('/templates') }}" class="text-decoration-none text-dark">Templates</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">Add</li>
            </ol>
        </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('templates') }}" class="btn btn-outline-secondary btn-sm me-1 mb-1"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-body">
                <form id="templateForm" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input class="form-control" id="title" name="title" type="text" placeholder="Template Title">
                        <span class="error" id="title_error"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="subtitle">Subtitle (Optional)</label>
                        <input class="form-control" id="subtitle" name="subtitle" type="text" placeholder="Template Subtitle">
                        <span class="error" id="subtitle_error"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                      
                        <textarea id="summernote" name="description"></textarea>
                        <span class="error" id="description_error"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-primary" type="submit" id="btnSubmit">Save</button>
                        <button type="button" class="btn btn-info" id="btnPreview">Preview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Template Preview</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="previewContent">
                <!-- Preview will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";
</script>
<!-- Summernote JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script src="{{ asset('init/template/add.js?v=' . time()) }}"></script>
@endpush
