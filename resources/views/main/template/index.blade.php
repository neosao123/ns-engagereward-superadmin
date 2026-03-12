@extends('layout.default.master', ['pageTitle' => 'Templates'])
@push('styles')
<link href="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
        <i class="fas fa-circle fa-stack-2x text-300"></i>
        <i class="fa-inverse fa-stack-1x text-primary fas fa-file-alt" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">Templates Master</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @if(isRolePermission(auth()->user()->role_id, 'Dashboard.View'))
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">Templates</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="table-responsive scrollbar">
            <table id="dt-templates" class="table table-hover">
                <thead>
                    <tr>
                        @if(isRolePermission(auth()->user()->role_id, 'Template.Edit') || isRolePermission(auth()->user()->role_id, 'Template.View'))
                        <th scope="col">Action</th>
                        @endif
                        <th scope="col">Title</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
<script src="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('init/template/index.js?v=' . time()) }}"></script>
@endpush
