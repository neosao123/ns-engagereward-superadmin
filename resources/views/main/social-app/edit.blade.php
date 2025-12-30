@php
  $pageTitle = __('index.edit_social_media_app');
@endphp
@extends('layout.default.master')
@push('styles')
<style>
  .error { color: red; }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: transparent !important;
  }

  #remove_logo {
    position: absolute;
    top: 5px;
    right: 5px;
    border: none;
    background: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    z-index: 10;
  }

  #logo_preview {
    width: 125px;
    position: relative;
  }
</style>
@endpush

@section('content')
<div class="d-flex mb-4 mt-1">
  <span class="fa-stack me-2 ms-n1">
    <i class="fas fa-circle fa-stack-2x text-300"></i>
    <i class="fa-inverse fa-stack-1x text-primary fas fa-share-alt" data-fa-transform="shrink-2"></i>
  </span>
  <div class="col">
    <h5 class="mb-0 text-primary position-relative">
      <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_social_media_app') }}</span>
      <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
    </h5>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
	   @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
       @endif     
       @if(Auth::guard('admin')->user()->can('Social Platform.List', 'admin'))	   
	   <li class="breadcrumb-item"><a href="{{ url('/social-media-apps') }}" class="text-decoration-none text-dark">{{__('index.social_media_app')}}</a></li>
       @endif       
	   <li class="breadcrumb-item active" aria-current="page">{{__('index.edit')}}</li>
      </ol>
    </nav>
  </div>
  @if(Auth::guard('admin')->user()->can('Social Platform.List', 'admin'))
  <div class="col-auto ms-2 align-items-center">
    <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('social-media-apps') }}">
      <span class="px-2">{{ __('index.back') }}</span>
    </a>
  </div>
  @endif
</div>

<div class="col-lg-12">
  <div class="card mb-3">
    <form id="form-edit-social-media-app" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="card-header bg-light">
        <h5 class="mb-0">{{ __('index.edit') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <input type="hidden" name="id" id="id" value="{{ $app->id }}" />
          <input type="hidden" name="previous_app_logo" id="previous_app_logo" value="{{ $app->app_logo }}" />

          <!-- App Name -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.app_name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="app_name" id="app_name" value="{{ old('app_name', $app->app_name) }}"/>
          </div>

          <!-- App Logo -->
          <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
            <label class="mb-0 form-label">{{ __('index.app_logo') }} <span class="text-danger">*</span></label>
            <p class="mb-0">
              <small class="form-label">
                {{ __('index.accept_format') }}
                {{ implode(', ', [__('index.jpg'), __('index.jpeg'), __('index.png')]) }}
              </small>
            </p>
            <p><small class="form-label">{{ __('index.512x512') }}</small></p>
            <input type="file" class="form-control" name="app_logo" id="app_logo" accept=".jpg, .jpeg, .png" />

            <div id="logo_preview" class="mt-2 {{ isset($app->app_logo) ? '' : 'd-none' }}">
              <img id="preview_img" data-id="{{ $app->id }}" src="{{ isset($app->app_logo) ? url('storage-bucket?path=' . $app->app_logo) : '#' }}" alt="Logo Preview"
                class="img-fluid" style="max-width: 125px; height: 125px;" />
              <button type="button" id="remove_image" class="btn btn-danger"
                style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                &times;
              </button>
            </div>

            <div id="error_message" class="text-danger mt-2" style="display: none;"></div>
          </div>
        </div>

        <!-- Active Status -->
        <div class="col-lg-6 col-md-6 col-sm-12">
          <div class="form-check">
            <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $app->is_active == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('index.active') }}</label>
          </div>
        </div>
      </div>

      <div class="card-footer bg-light text-end">
        <button class="btn btn-primary" id="update-app" type="button">{{ __('index.update') }}</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var baseUrl = "{{ url('/') }}"
  var csrfToken = "{{ csrf_token() }}"
  var id = "{{ $app->id }}"
</script>
   <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('init/social-media-app/edit.js?v=' . time()) }}"></script>
@endpush
