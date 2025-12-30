@extends('layout.default.master', ['pageTitle' => __('index.view_user')])
@push('styles')
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
<style>
    tr.group,
    tr.group:hover {
        background-color: #ddd !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: transparent !important;
    }
    .error {
        color: red;
    }
    .iti__country-list {
        white-space: break-spaces !important;
    }
    
    .iti {
        width: 100% !important;
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        <i class="fa-inverse fa-stack-1x text-primary fas fa-user" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.view_user')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('User.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/users') }}" class="text-decoration-none text-dark">{{__('index.users')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('User.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('users') }}" class="btn btn-outline-secondary btn-sm me-1 mb-1">
            <i class="fas fa-arrow-left me-1"></i> {{__('index.back')}}
        </a>
    </div>
    @endif
</div>

<div class="col-lg-12">
    <div class="card mb-3">
        <form>
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('index.user_details') }}</h5>
            </div>
            <div class="card-body">
                <div class="form-content">
                    <!-- Left Column - Form Fields (75%) -->
                    <div class="form-fields">
                        <div class="row g-3">
                            <!-- Role -->
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{ __('index.role') }}</label>
                                <input type="text" class="form-control" name="role" id="role" value="{{$user->role->name??"" }}" readonly />
                            </div>
                            
                            <!-- User Name -->
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{__('index.username')}}</label>
                                <input type="text" class="form-control" name="username" id="username" value="{{$user->username }}" readonly />
                            </div>
                            
                            <!-- First Name -->
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{__('index.first_name')}}</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" value="{{$user->first_name }}" readonly />
                            </div>
                            
                            <!-- Last Name -->
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{__('index.last_name')}}</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" value="{{ $user->last_name }}" readonly />
                            </div>
                            
                            <!-- Email -->
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{__('index.email')}}</label>
                                <input type="email" class="form-control" name="email" id="email" value="{{ $user->email }}" readonly />
                            </div>
                            
                            @php
                                use libphonenumber\PhoneNumberUtil;
                                use libphonenumber\NumberParseException;
                                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                                $parsed = $phoneUtil->parse($user->phone, $user->phone_country );
                                $number=$phoneUtil->format($parsed, \libphonenumber\PhoneNumberFormat::NATIONAL);
                            @endphp
                            <!-- Phone Number -->
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{__('index.phone_number')}}</label>
                                <input type="hidden" name="phone_country" id="phone_country" value="{{ $user->phone_country }}">
                                <input type="tel" class="form-control" name="phone_number" id="phone_number" value="{{ $number }}" readonly />
                            </div>
                            
                            <!-- Active Status -->
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $user->is_active == 1 ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('index.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Photo Section (25%) -->
                    <div class="photo-section">
                        <div class="photo-container">
                            <img class="photo-preview" id="showImage" 
     src="{{ $user->avatar 
            ? url('storage-bucket?path=' . $user->avatar . '&t=' . time()) 
            : asset('/img/user/default-user.png') }}" />
                            <div class="mb-3">
                                <label class="form-label">{{__('index.avatar')}}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Then load the JS files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

<script>
    // Initialize phone input in read-only mode
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById("phone_number");
        const countryInput = document.getElementById("phone_country");
        
        const initialCountry = countryInput.value.toLowerCase() || "auto";
        const initialNumber = phoneInput.value || "";
        
        const iti = window.intlTelInput(phoneInput, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
            preferredCountries: ['us', 'gb', 'ca', 'au', 'in'],
            separateDialCode: true,
            initialCountry: initialCountry,
            nationalMode: false,
            autoPlaceholder: "off",
            readonly: true
        });

        if (initialNumber) {
            const countryData = iti.getSelectedCountryData();
            const fullNumber = '+' + countryData.dialCode + initialNumber;
            iti.setNumber(fullNumber);
        }
    });
</script>
@endpush