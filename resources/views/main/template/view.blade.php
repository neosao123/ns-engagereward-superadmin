@extends('layout.default.master', ['pageTitle' => 'View Template'])

@section('content')
<div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
        <i class="fas fa-circle fa-stack-2x text-300"></i>
        <i class="fa-inverse fa-stack-1x text-primary fas fa-eye" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">View Template</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @if(isRolePermission(auth()->user()->role_id, 'Dashboard.View'))
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                @endif
                @if(isRolePermission(auth()->user()->role_id, 'Template.List'))
                <li class="breadcrumb-item"><a href="{{ url('/templates') }}" class="text-decoration-none text-dark">Templates</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('templates') }}" class="btn btn-outline-secondary btn-sm me-1 mb-1"><i class="fas fa-arrow-left me-1"></i>Back</a>
        @if(isRolePermission(auth()->user()->role_id, 'Template.Edit'))
        <a href="{{ url('templates/' . $template->id . '/edit') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1"><i class="fas fa-edit me-1"></i>Edit</a>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0">{{ $template->title }}</h5>
        @if($template->subtitle)
            <div class="text-500 fw-semi-bold">{{ $template->subtitle }}</div>
        @endif
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>Status:</strong> 
            @if($template->is_active)
                <span class="badge rounded-pill badge-soft-success">Active</span>
            @else
                <span class="badge rounded-pill badge-soft-danger">Inactive</span>
            @endif
        </div>
        <div class="border p-4 rounded bg-white">
            {!! $template->description !!}
        </div>
    </div>
</div>
@endsection
