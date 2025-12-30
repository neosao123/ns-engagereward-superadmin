@php
  $pageTitle = __('index.edit_setting');
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
      <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_setting') }}</span>
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
	   <li class="breadcrumb-item active" aria-current="page">{{__('index.edit')}}</li>
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
    <form id="form-edit-setting" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="card-header bg-light">
        <h5 class="mb-0">{{ __('index.edit') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <input type="hidden" name="id" id="id" value="{{ $setting->id }}" />
          <input type="hidden" name="previous_logo_image" id="previous_logo_image" value="{{ $setting->logo_image }}" />

        <!-- Contact Email -->
        <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
          <label for="contact_email" class="form-label">{{ __('index.contact_email') }} <span class="text-danger">*</span></label>
          <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $setting->contact_email) }}">
        </div>
		
		   <!-- Contact Phone -->
        <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
          <label for="contact_phone" class="form-label">{{ __('index.contact_phone') }} <span class="text-danger">*</span></label>
          <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ old('contact_phone', $setting->contact_phone) }}">
        </div>
        <!-- Support Email -->
        <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
          <label for="support_email" class="form-label">{{ __('index.support_email') }} </label>
          <input type="email" name="support_email" id="support_email" class="form-control" value="{{ old('support_email', $setting->support_email) }}">
        </div>
		
     

        <!-- Support Contact -->
        <div class="mb-3 col-lg-4 col-md-6 col-sm-12">
          <label for="support_contact" class="form-label">{{ __('index.support_contact') }} </label>
          <input type="text" name="support_contact" id="support_contact" class="form-control" value="{{ old('support_contact', $setting->support_contact) }}">
        </div>

		<!-- Logo Image -->
		<div class="mb-3 col-lg-12 col-md-12 col-sm-12 position-relative">
		  <label for="logo_image" class="form-label">{{ __('index.logo') }} <span class="text-danger">*</span></label>
		  <p class="mb-0"><small class="form-label">{{ __('index.accept_format') }}: jpg, jpeg, png</small></p>
		  <input type="file" class="form-control" name="logo_image" id="logo_image" accept=".jpg, .jpeg, .png" />

		  <div id="logo_preview" class="mt-2 {{ isset($setting->logo_image) ? '' : 'd-none' }}">
			<img id="preview_img" data-id="{{ $setting->id }}"  src="{{ isset($setting->logo_image) ? url('storage-bucket?path=' . $setting->logo_image) : '#' }}" alt="Logo Preview"
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
            <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $setting->is_active == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('index.active') }}</label>
          </div>
        </div>
      </div>

      <div class="card-footer bg-light text-end">
        <button class="btn btn-primary" id="update-setting" type="button">{{ __('index.update') }}</button>
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
<script src="{{ asset('init/setting/edit.js?v=' . time()) }}"></script>
@endpush
