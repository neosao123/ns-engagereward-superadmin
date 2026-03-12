@extends('layout.default.master', ['pageTitle' => 'Edit Template'])

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
        <i class="fa-inverse fa-stack-1x text-primary fas fa-edit" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">Edit Template</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @if(isRolePermission(auth()->user()->role_id, 'Dashboard.View'))
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                @endif
                @if(isRolePermission(auth()->user()->role_id, 'Template.List'))
                <li class="breadcrumb-item"><a href="{{ url('/templates') }}" class="text-decoration-none text-dark">Templates</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
                <form id="templateEditForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="template_id" value="{{ $template->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input class="form-control" id="title" name="title" type="text" value="{{ $template->title }}" placeholder="Template Title">
                        <span class="error" id="title_error"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="subtitle">Subtitle (Optional)</label>
                        <input class="form-control" id="subtitle" name="subtitle" type="text" value="{{ $template->subtitle }}" placeholder="Template Subtitle">
                        <span class="error" id="subtitle_error"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <div class="text-danger small mb-1">Note: Do not remove placeholders (words wrapped in # like #name#) or buttons, as they are required for dynamic data when creating a company.</div>
                        <textarea id="summernote_edit" name="description">{!! $template->description !!}</textarea>
                        <span class="error" id="description_error"></span>
                    </div>

                    <input type="hidden" name="is_active" value="1">

                    <div class="mt-4">
                        <button class="btn btn-primary" type="submit" id="btnUpdate">Update</button>
                        <button type="button" class="btn btn-info" id="btnPreviewEdit">Preview</button>
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
<script src="{{ asset('init/template/edit.js?v=' . time()) }}"></script>
@endpush
