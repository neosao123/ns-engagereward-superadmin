@extends('layout.default.master', ['pageTitle' => __('index.profile_update')])
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
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
            white-space: break-spaces ! important;
        }

        .iti {

            width: 100% ! important;
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
                <h5 class="mb-0 text-primary position-relative"><span
                        class="bg-200 dark__bg-1100 pe-3">{{ __('index.profile') }}</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                    class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ __('index.profile_update') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
            <div class="col-auto ms-2 align-items-center">
                <a href="{{ url('dashboard') }}"
                    class="btn btn-falcon-primary btn-sm me-1 mb-1">{{ __('index.dashboard') }}</a>
            </div>
        @endif
    </div>
    <div class="row g-3 mb-3 justify-content-center">
        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-8 col-sm-12 pe-lg-2">
            <div class="card">
                <form id="form-update-user" method="post" enctype="multipart/form-data">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ __('index.profile') }}</h5>
                    </div>
                    <div class="card-body pt-3 pb-2">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('index.first_name') }} : <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="first_name"
                                        value="{{ $details->first_name }}" maxlength="100" />
                                    @error('first_name')
                                        <div class="error py-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('index.last_name') }}: <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="last_name"
                                        value="{{ $details->last_name }}" maxlength="100" />
                                    @error('last_name')
                                        <div class="error py-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('index.email') }} : <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email" value="{{ $details->email }}"
                                        maxlength="100" />
                                    @error('email')
                                        <div class="error py-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @php
                                use libphonenumber\PhoneNumberUtil;
                                use libphonenumber\NumberParseException;
                                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                                $parsed = $phoneUtil->parse($details->phone, $details->phone_country);
                                $number = $phoneUtil->format($parsed, \libphonenumber\PhoneNumberFormat::NATIONAL);
                            @endphp
                            <div class="col-12">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('index.phone') }} : <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="hidden" name="phone_country" id="phone_country"
                                        value="{{ $details->phone_country }}" />
                                    <input class="form-control" type="tel" name="phone" id="phone"
                                        value="{{ $details->phone }}"
                                        oninput="this.value = this.value.replace(/\D/g, '')" />
                                    @error('phone')
                                        <div class="error py-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row gx-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('index.avatar') }}</label>
                                    <input type="file" id="file" class="form-control " name="avatar"
                                        accept=".jpg, .jpeg, .png">
                                </div>

                                <p style="margin-top: 5px; font-size: 0.875rem; color: #666;">
                                    <strong>Note:</strong> The uploaded image must be in <strong>JPG, JPEG, or PNG</strong>
                                    format,
                                    should have a <strong>1:1 (square) aspect ratio</strong> (e.g.,
                                    <strong>500×500</strong>, 100×100, 512×512), and must not exceed
                                    <strong>2 MB</strong> in size.
                                </p>
                            </div>
                        </div>
                        <div class="row gx-3">
                            <div class="col-12">
                                <img class="img-radius" id="showImage"
                                    src="{{ $details->avatar
                                        ? url('storage-bucket?path=' . $details->avatar . '&t=' . time())
                                        : asset('/assets/img/user/default-user.png') }}"
                                    height="80" width="80" />
                                @if ($details->avatar)
                                    <a href="{{ url('profile/delete/avatar') }}" class="mx-3 text-danger">
                                        <span class="fas fa-trash-alt"></span>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="card-footer float-end">
                        <button type="button" class="btn btn-primary"
                            id="user-update">{{ __('index.profile_update') }}</button>
                    </div>
                </form>
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

            // Initialize with existing values
            const iti = window.intlTelInput(phoneInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                preferredCountries: ['us', 'gb', 'ca', 'au', 'in', 'ae'],
                separateDialCode: true,
                initialCountry: "ae",
                nationalMode: true, // Show national number without country code
                autoPlaceholder: "off"
            });

            // Set initial value if exists
            if (phoneInput.value) {
                iti.setNumber(phoneInput.value);
            }

            // Update hidden country field when country changes
            phoneInput.addEventListener('countrychange', function() {
                countryInput.value = iti.getSelectedCountryData().iso2;
            });
        });
    </script>
    <script>
        var baseUrl = "{{ url('/') }}"
        var id = '{{ $details->id }}';
    </script>

    <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('init/profile/index.js?v=' . time()) }}"></script>
@endpush
