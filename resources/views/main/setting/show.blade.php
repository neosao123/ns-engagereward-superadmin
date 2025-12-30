@php
  $pageTitle = __('index.view_setting');
@endphp

@extends('layout.default.master')

@push('styles')
<style>
  #image_preview {
    width: 125px;
    position: relative;
  }
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
      <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_setting') }}</span>
      <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
    </h5>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
        @endif
        @if(Auth::guard('admin')->user()->can('Setting.List', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/setting') }}" class="text-decoration-none text-dark">{{__('index.setting')}}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{ __('index.view') }}</li>
      </ol>
    </nav>
  </div>
  @if(Auth::guard('admin')->user()->can('Setting.List', 'admin'))
  <div class="col-auto ms-2 align-items-center">
    <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('setting') }}">
      <span class="px-2">{{ __('index.back') }}</span>
    </a>
  </div>
  @endif
</div>

<div class="col-lg-12">
  <div class="card mb-3">
    <form>
      <div class="card-header bg-light">
        <h5 class="mb-0">{{ __('index.view') }}</h5>
      </div>

      <div class="card-body">
        <div class="row">

          <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">{{ __('index.contact_email') }}</label>
            <input type="text" class="form-control" value="{{ $setting->contact_email }}" readonly />
          </div>

          <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">{{ __('index.contact_phone') }}</label>
            <input type="text" class="form-control" value="{{ $setting->contact_phone }}" readonly />
          </div>

          <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">{{ __('index.support_email') }}</label>
            <input type="text" class="form-control" value="{{ $setting->support_email }}" readonly />
          </div>

          <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">{{ __('index.support_contact') }}</label>
            <input type="text" class="form-control" value="{{ $setting->support_contact }}" readonly />
          </div>

          <!-- Logo Image -->
          <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
            <label class="form-label">{{ __('index.logo') }}</label>
            <div id="image_preview" class="mt-2 {{ isset($setting->logo_image) ? '' : 'd-none' }}">
              <img src="{{ isset($setting->logo_image) ? url('storage-bucket?path=' . $setting->logo_image) : '#' }}" alt="Logo Preview"
                class="img-fluid" style="max-width: 125px; height: 125px;" />
            </div>
          </div>

          <!-- Active Status -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" disabled {{ $setting->is_active == 1 ? 'checked' : '' }}>
              <label class="form-check-label">{{ __('index.active') }}</label>
            </div>
          </div>

        </div>
      </div>
    </form>
  </div>
</div>
@endsection
