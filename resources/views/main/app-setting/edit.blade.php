@php
  $pageTitle = 'App Setting';
@endphp
@extends('layout.default.master')
@push('styles')
<style>
  .error { color: red; }
</style>
@endpush

@section('content')
<div class="d-flex mb-4 mt-1">
  <span class="fa-stack me-2 ms-n1">
    <i class="fas fa-circle fa-stack-2x text-300"></i>
    <i class="fa-inverse fa-stack-1x text-primary fas fa-cogs" data-fa-transform="shrink-2"></i>
  </span>
  <div class="col">
    <h5 class="mb-0 text-primary position-relative">
      <span class="bg-200 dark__bg-1100 pe-3">Edit App Setting</span>
      <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
    </h5>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
	   @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
       @endif     
	   <li class="breadcrumb-item"><a href="{{ url('/app-settings/list') }}" class="text-decoration-none text-dark">App Setting</a></li>
	   <li class="breadcrumb-item active" aria-current="page">{{__('index.edit')}}</li>
      </ol>
    </nav>
  </div>
  <div class="col-auto ms-2 align-items-center">
    <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('app-settings/list') }}">
      <span class="px-2">{{ __('index.back') }}</span>
    </a>
  </div>
</div>

<div class="col-lg-12">
  <div class="card mb-3">
    <form id="form-edit-app-setting" method="POST">
      @csrf
      @method('POST')
      <div class="card-header bg-light">
        <h5 class="mb-0">{{ __('index.edit') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <input type="hidden" name="id" id="id" value="{{ $setting->id }}" />

        <!-- Setting Name -->
        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
          <label for="setting_name" class="form-label">Setting Name</label>
          <input type="text" name="setting_name" id="setting_name" class="form-control" value="{{ $setting->setting_name }}" readonly>
        </div>
		
		   <!-- Setting Value -->
        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
          <label for="setting_value" class="form-label">Version <span class="text-danger">*</span></label>
          <input type="text" name="setting_value" id="setting_value" class="form-control" value="{{ old('setting_value', $setting->setting_value) }}">
        </div>

        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_update_compulsory" name="is_update_compulsory" {{ $setting->is_update_compulsory == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_update_compulsory">Is Update Compulsory</label>
            </div>
        </div>

        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ $setting->is_active == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('index.active') }}</label>
            </div>
        </div>
        
       </div>

      </div>

      <div class="card-footer bg-light text-end">
        <button class="btn btn-primary" id="update-app-setting" type="button">{{ __('index.update') }}</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var baseUrl = "{{ url('/') }}"
  var csrfToken = "{{ csrf_token() }}"
  var id = "{{ $setting->id }}"
</script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('init/app-setting/edit.js?v=' . time()) }}"></script>
@endpush
