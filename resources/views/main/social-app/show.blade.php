@php
  $pageTitle = __('index.view_social_media_app');
@endphp
@extends('layout.default.master')
@push('styles')
 
  <style>
    .error {
      color: red;
    }

    #remove_image {
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
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_social_media_app') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
        @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
		<li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
		@endif
		@if(Auth::guard('admin')->user()->can('Social Platform.List', 'admin'))
		<li class="breadcrumb-item"><a href="{{ url('/social-media-apps') }}" class="text-decoration-none text-dark">{{__('index.social_media_app')}}</a></li>
		@endif
		<li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
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
      <form >
       
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.view') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <input type="hidden" name="id" id="id" value="{{ $app->id }}" />
            
            <!-- App Name -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.app_name') }}</label>
              <input type="text" class="form-control" name="app_name" id="app_name" value="{{ old('app_name', $app->app_name) }}" readonly />
            </div>

			<!-- App Icon -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
              <label class="mb-0 form-label">{{ __('index.app_logo') }}</label>
              
              <!-- Add d-none class if no image is available -->
              <div id="image_preview" class="mt-2 {{ isset($app->app_logo) ? '' : 'd-none' }}">
                <img id="preview_img" data-id="{{ $app->id }}" src="{{ isset($app->app_logo) ? url('storage-bucket?path=' . $app->app_logo) : '#' }}" alt="Image Preview"
                  class="img-fluid" style="max-width: 125px; height: 125px;" />

              </div>
            </div>
		  </div>
          <!-- Active Status -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="form-check">
              <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $app->is_active == 1 ? 'checked' : '' }} disabled >
              <label class="form-check-label" for="is_active">
                {{ __('index.active') }} 
              </label>
            </div>
          </div>
        </div>
       
      </form>


    </div>
  </div>
@endsection
@push('scripts')
 
@endpush
