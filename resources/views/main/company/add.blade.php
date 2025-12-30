@php
$pageTitle = __('index.add_company');
@endphp
@extends('layout.default.master')
@push('styles')
<link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}">
<link href="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<!-- Add these to your head section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<style>
  .error {
    color: red;
  }

  .invalid-feedback {
    display: block !important;
  }

  .theme-wizard .nav-link.active {
    color: #2c7be5;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: transparent !important;
  }

  .card-body {
    height: 550px;
    overflow: auto;
    scrollbar-width: thin;
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

  .iti__country-list {
    white-space: break-spaces ! important;
  }

  .iti {

    width: 100% ! important;
  }

  /* Photo Container Styles */
  .photo-container {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
    text-align: center;
    margin-bottom: 20px;
  }

  .photo-preview {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 10px;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* Form Section Styling */
  .form-section {
    margin-bottom: 25px;
  }

  .section-title {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
    margin-bottom: 20px;
    color: #3a7bd5;
    font-weight: 600;
  }

  /* Layout Adjustments */
  .form-content {
    display: flex;
    flex-wrap: wrap;
  }

  .form-fields {
    flex: 0 0 75%;
    max-width: 75%;
    padding-right: 15px;
  }

  .photo-section {
    flex: 0 0 25%;
    max-width: 25%;
    padding-left: 15px;
  }

  @media (max-width: 992px) {

    .form-fields,
    .photo-section {
      flex: 0 0 100%;
      max-width: 100%;
      padding: 0;
    }
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
    <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.add') }}</span><span
        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
        @endif
        @if(Auth::guard('admin')->user()->can('Company.List', 'admin'))
        <li class="breadcrumb-item"><a href="{{ url('/company') }}" class="text-decoration-none text-dark">{{__('index.company')}}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{__('index.add')}}</li>
      </ol>
    </nav>
  </div>
  @if(Auth::guard('admin')->user()->can('Company.List', 'admin'))
  <div class="col-auto ms-2 align-items-center">
    <a href="{{ url('company') }}" class="btn btn-outline-secondary btn-sm me-1 mb-1"><i class="fas fa-arrow-left me-1"></i>{{ __('index.back') }}</span>
    </a>
  </div>
  @endif
</div>
<div class="row">
  <div class="col-lg-12 col-md-12 h-100">
    <div class="card theme-wizard h-100 mb-1">
      <div class="card-header bg-light">
        <ul class="nav justify-content-between nav-wizard " id="myTabs">
          <li id="tab1_nav" class="nav-item m-0 p-0"><a class="nav-link active fw-semi-bold m-0 p-0" href="#bootstrap-wizard-validation-tab1" data-wizard-step="data-wizard-step"><span
                class="nav-item-circle-parent"><span class="nav-item-circle">1</span></span><span class="d-none d-md-block mt-1 fs--1">{{ __('index.basic_information') }}</span></a></li>
          <li id="tab2_nav" class="nav-item m-0 p-0"><a class="nav-link fw-semi-bold  m-0 p-0" href="#bootstrap-wizard-validation-tab2" data-wizard-step="data-wizard-step"><span
                class="nav-item-circle-parent"><span class="nav-item-circle">2</span></span><span class="d-none d-md-block mt-1 fs--1">{{ __('index.address') }}</span></a></li>
          <li id="tab3_nav" class="nav-item m-0 p-0"><a class="nav-link fw-semi-bold disabled m-0 p-0" href="#bootstrap-wizard-validation-tab3" data-wizard-step="data-wizard-step"><span
                class="nav-item-circle-parent"><span class="nav-item-circle">3</span></span><span class="d-none d-md-block mt-1 fs--1">{{ __('index.social_information') }}</span></a></li>

          <li id="tab4_nav" class="nav-item m-0 p-0"><a class="nav-link fw-semi-bold disabled m-0 p-0" href="#bootstrap-wizard-validation-tab4" data-wizard-step="data-wizard-step"><span
                class="nav-item-circle-parent"><span class="nav-item-circle">4</span></span><span class="d-none d-md-block mt-1 fs--1">{{ __('index.subscription_plan') }}</span></a></li>

          <li id="tab5_nav" class="nav-item m-0 p-0"><a class="nav-link fw-semi-bold disabled m-0 p-0" href="#bootstrap-wizard-validation-tab5" data-wizard-step="data-wizard-step"><span
                class="nav-item-circle-parent"><span class="nav-item-circle">5</span></span><span class="d-none d-md-block mt-1 fs--1">{{ __('index.document_information') }}</span></a></li>
        </ul>
      </div>

      <div class="tab-content">

        {{-- basic information --}}
        <div class="tab-pane active" role="tabpanel" aria-labelledby="bootstrap-wizard-validation-tab1" id="bootstrap-wizard-validation-tab1">
          @include('main.company.add.basic-information')
        </div>

        {{-- Address Form --}}
        <div class="tab-pane" role="tabpanel" aria-labelledby="bootstrap-wizard-validation-tab2" id="bootstrap-wizard-validation-tab2">
          @include('main.company.add.address-information')
        </div>

        <div class="tab-pane" role="tabpanel" aria-labelledby="bootstrap-wizard-validation-tab3" id="bootstrap-wizard-validation-tab3">
          @include('main.company.add.social-information')
        </div>

        <div class="tab-pane" role="tabpanel" aria-labelledby="bootstrap-wizard-validation-tab4" id="bootstrap-wizard-validation-tab4">
          @include('main.company.add.subscription-information')
        </div>

        <div class="tab-pane" role="tabpanel" aria-labelledby="bootstrap-wizard-validation-tab5" id="bootstrap-wizard-validation-tab5">
          @include('main.company.add.document-information')
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@push('scripts')

<!-- Then load the JS files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
<script>
  // Wait for the DOM to be fully loaded
  document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById("phone");
    const countryInput = document.getElementById("phone_country");

    const iti = window.intlTelInput(phoneInput, {
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
      preferredCountries: ['us', 'gb', 'ca', 'au', 'in', 'ae'],
      separateDialCode: true,
      initialCountry: "ae",
      geoIpLookup: function(callback) {
        fetch("https://ipapi.co/json")
          .then(res => res.json())
          .then(data => callback(data.country_code))
          .catch(() => callback('us'));
      }
    });

    // ✅ 1. Initialize hidden field immediately
    countryInput.value = iti.getSelectedCountryData().iso2;

    // Update hidden country field when country changes
    phoneInput.addEventListener('countrychange', function() {
      countryInput.value = iti.getSelectedCountryData().iso2;
    });


  });
  var baseUrl = "{{ url('/') }}";
  var csrfToken = "{{ csrf_token() }}";
  var companyDoc = "{{url('img/docs-placeholder.png')}}";
</script>
<script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
<script src="{{ asset('vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
<script src="{{ asset('vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('init/company/add.js?v=' . time()) }}"></script>
@endpush