@extends('layout.default.master', ['pageTitle' => __('index.add_social_media_app')])
@push('styles')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.create')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
					@if(Auth::guard('admin')->user()->can('Social Platform.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/social-media-apps') }}" class="text-decoration-none text-dark">{{__('index.social_media_app')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.create')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Social Platform.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('social-media-apps') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<!--ADD USER-->
 <div class="col-lg-12">
    <div class="card mb-3">
      <form class="" id="form-add-social-media-app" method="POST" action="{{ url('social-media-apps') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.create')}}</h5>
        </div>
        <div class="card-body"> 
          <div class="row">
       
			<!--App Name -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.app_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="app_name" id="app_name" value="{{ old('app_name') }}" />
            </div>
	
             <!-- App Icon -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
              <label class="mb-0 form-label">{{ __('index.app_logo') }} <span class="text-danger">*</span> </label>
              <p class="mb-0">
                <small class="form-label">
                  {{ __('index.accept_format') }}
                  {{ implode(', ', [__('index.jpg'), __('index.jpeg'), __('index.png')]) }}
                </small>
              </p>
              <p><small class="form-label">{{ __('index.512x512') }}</small></p>
              
              <input type="file" class="form-control" name="app_logo" id="app_logo" accept=".jpg, .jpeg, .png" />
              <div id="image_preview" class="mt-2" style="display: none;">
                <img id="preview_img" src="#" alt="Image Preview" class="img-fluid" style="width: 125px; height: 125px;" />
                <button type="button" id="remove_image" class="btn btn-danger"
                  style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                  &times; <!-- This will display an 'X' -->
                </button>
              </div>
              <div id="error_message" class="text-danger mt-2" style="display: none;"></div>
            </div>					 
		  </div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" checked>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
           <button class="btn btn-primary" id="submit" type="submit">{{ __('index.save') }}</button>
           <button class="btn btn-dark" type="button" onclick="window.location.reload();">{{ __('index.reset') }} </button>
		</div>
      </form>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
  </script>

     <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
 <script src="{{ asset('init/social-media-app/add.js?v=' . time()) }}"></script>
@endpush