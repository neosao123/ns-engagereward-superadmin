@extends('layout.default.master', ['pageTitle' => __('index.integration_credentials')])
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .error {
      color: red;
    }
    .invalid-feedback{
        display:block !important;
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.social_platform_integration')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Company.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/company') }}" class="text-decoration-none text-dark">{{__('index.company')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.social_platform_integration')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Company.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('company') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>

<div class="col-lg-12">
    <!-- Loop through each social media platform -->
    @foreach($getSocialDetails as $socialMedia)
    <div class="card mb-3 social-media-card" data-social-id="{{ $socialMedia['id'] }}">
        <form class="social-media-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="company_id" value="{{ request('companyId') }}">
            <input type="hidden" name="social_media_id" value="{{ $socialMedia['id'] }}">

            <div class="card-header bg-light">
                <h5 class="mb-0">{{ $socialMedia['name'] }} {{ __('index.integration_credentials') }}</h5>
            </div>

            <div class="card-body">
                <div class="documents-repeater">
                    @php
                        $socialCredentials = array_filter($credentials, function($cred) use ($socialMedia) {
                            return isset($cred['social_media_id']) && $cred['social_media_id'] == $socialMedia['id'];
                        });
                        $credentialIndex = 0;
                    @endphp

                    @if(count($socialCredentials) > 0)
                        @foreach($socialCredentials as $index => $credential)
                        <div class="document-row mb-3 border-bottom pb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">{{ __('index.type') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control type"
                                           name="integration_credentials[{{ $credentialIndex }}][type]"
                                           value="{{ $credential['type'] ?? '' }}"
                                           placeholder="e.g. API key, Secret Key etc.">
                                    <input type="hidden"
                                           name="integration_credentials[{{ $credentialIndex }}][id]"
                                           value="{{ $credential['id'] ?? '' }}">
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label class="form-label">{{ __('index.value') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control value"
                                           name="integration_credentials[{{ $credentialIndex }}][value]"
                                           value="{{ $credential['value'] ?? '' }}">
                                </div>

                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-document">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @php $credentialIndex++; @endphp
                        @endforeach
                    @else
                        <!-- Initial empty row -->
                        <div class="document-row mb-3 border-bottom pb-3">
                            <div class="row">
                                <input type="hidden" name="integration_credentials[0][id]" value="#">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">{{ __('index.type') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control type"
                                           name="integration_credentials[0][type]"
                                           placeholder="e.g. API key, Secret Key etc.">
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label class="form-label">{{ __('index.value') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control value"
                                           name="integration_credentials[0][value]">
                                </div>

                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-document" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Add More Button for this social media -->
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-sm btn-primary add-integration-credentials">
                            <i class="fas fa-plus me-2"></i> {{ __('index.add') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light text-end">
                <button class="btn btn-primary submit-btn" type="button" data-social-id="{{ $socialMedia['id'] }}">
                    {{ __('index.save') }}
                </button>
                <button class="btn btn-dark" type="button" onclick="window.location.reload();">{{ __('index.reset') }}</button>
            </div>
        </form>
    </div>
    @endforeach
</div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
  </script>

   <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
 <script src="{{ asset('init/company/integration_credentials.js?v=' . time()) }}"></script>
@endpush
