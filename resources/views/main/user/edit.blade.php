@extends('layout.default.master', ['pageTitle' => __('index.edit_user')])
@push('styles')
    <link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <style>
        tr.group,
        tr.group:hover {
            background-color: #ddd !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: transparent !important;
        }

        .backend-error {
            width: 100%;
            margin-top: 0.25rem;
            font-size: 75%;
            color: #e63757;
        }

        .iti__country-list {
            white-space: break-spaces !important;
        }

        .iti {
            width: 100% !important;
        }

        .is-invalid~.iti__flag-container {
            padding-right: calc(1px + 1.5rem);
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
            <i class="fa-inverse fa-stack-1x text-primary fas fa-user-edit" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative"><span
                        class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_user') }}</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                    class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                        @endif
                        @if (Auth::guard('admin')->user()->can('User.List', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/users') }}"
                                    class="text-decoration-none text-dark">{{ __('index.users') }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ __('index.edit') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if (Auth::guard('admin')->user()->can('User.List', 'admin'))
            <div class="col-auto ms-2 align-items-center">
                <a href="{{ url('users') }}" class="btn btn-outline-secondary btn-sm me-1 mb-1">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('index.back') }}
                </a>
            </div>
        @endif
    </div>

    <div class="col-lg-12">
        <form id="form-update-user" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ __('index.edit_user') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-content">
                        <!-- Left Column - Form Fields (75%) -->
                        <div class="form-fields">
                            <div class="form-section">
                                <h6 class="section-title">{{ __('index.basic_information') }}</h6>
                                <div class="row g-3">
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <!-- Role -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.role') }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control select2" name="role" id="role">
                                            <option value="{{ $user->role_id }}">{{ $user->role->name ?? '' }}</option>
                                        </select>
                                        @error('role')
                                            <span class="text-danger backend-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Username -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.username') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="username" id="username"
                                            value="{{ $user->username }}" />
                                        @error('username')
                                            <span class="text-danger backend-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- First Name -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.first_name') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name" id="first_name"
                                            value="{{ $user->first_name }}" />
                                        @error('first_name')
                                            <span class="text-danger backend-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Last Name -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.last_name') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="last_name" id="last_name"
                                            value="{{ $user->last_name }}" />
                                        @error('last_name')
                                            <span class="text-danger backend-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.email') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" id="email"
                                            value="{{ $user->email }}" />
                                        @error('email')
                                            <span class="text-danger backend-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @php
                                        use libphonenumber\PhoneNumberUtil;
                                        use libphonenumber\NumberParseException;
                                        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                                        $parsed = $phoneUtil->parse($user->phone, $user->phone_country);
                                        $number = $phoneUtil->format(
                                            $parsed,
                                            \libphonenumber\PhoneNumberFormat::NATIONAL,
                                        );
                                    @endphp
                                    <!-- Phone Number -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.phone_number') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="hidden" name="phone_country" id="phone_country"
                                            value="{{ $user->phone_country }}">
                                        <input type="tel" class="form-control" name="phone_number" id="phone_number"
                                            value="{{ $number }}"
                                            oninput="this.value = this.value.replace(/\D/g, '')" />
                                        @error('phone_number')
                                            <span class="text-danger backend-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Password -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.password') }}</label>
                                        <div class="input-group has-validation">
                                            <input type="password" class="form-control" name="password" id="password"
                                                value="{{ old('new_password') }}" placeholder="{{ __('index.enter_min_8_characters') }}">
                                            <button type="button" class="btn btn-outline-secondary toggle-password"
                                                data-target="#password">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                            @error('password')
                                                <div class="text-danger backend-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Password Confirmation -->
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">{{ __('index.password_confirmation') }}</label>
                                        <div class="input-group has-validation">
                                            <input type="password" class="form-control" name="password_confirmation"
                                                id="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="{{ __('index.enter_min_8_characters') }}">
                                            <button type="button" class="btn btn-outline-secondary toggle-password"
                                                data-target="#password_confirmation">
                                                <i class="fas fa-eye-slash icon_attr"></i>
                                            </button>
                                            @error('password_confirmation')
                                                <div class="text-danger backend-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Active Checkbox -->
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_active" id="is_active"
                                                type="checkbox" value="1"
                                                {{ $user->is_active == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('index.active') }}
                                            </label>
                                        </div>
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
                                    <label class="form-label">{{ __('index.avatar') }}</label>
                                    <input type="file" id="file"
                                        class="form-control @error('avatar') is-invalid @enderror" name="avatar"
                                        accept=".jpg,.jpeg,.png">
                                    @error('avatar')
                                        <span class="text-danger backend-error">{{ $message }}</span>
                                    @enderror
                                </div>
                                <p style="margin-top: 5px; font-size: 0.875rem; color: #666;">
                                    <strong>Note:</strong> The uploaded image must be in <strong>JPG, JPEG, or PNG</strong>
                                    format, should have a
                                    <strong>1:1 (square) aspect ratio</strong> (e.g., <strong>500×500</strong>, 100×100,
                                    512×512), and must not exceed
                                    <strong>2 MB</strong> in size.
                                </p>
                                @if ($user->avatar)
                                    <a href="{{ url('users/delete/avatar/' . $user->id) }}" class="text-danger">
                                        <i class="fas fa-trash-alt me-1"></i> {{ __('index.delete_avatar') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button class="btn btn-primary" type="button" id="user-update">
                        <i class="fas fa-save me-2"></i>{{ __('index.update') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- Then load the JS files -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

    <script>
        // Image Preview Functionality
        document.getElementById('file').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('showImage').src = event.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Phone Input Initialization
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById("phone_number");
            const countryInput = document.getElementById("phone_country");

            const initialCountry = countryInput.value.toLowerCase() || "auto";
            const initialNumber = phoneInput.value || "";

            const iti = window.intlTelInput(phoneInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                preferredCountries: ['us', 'gb', 'ca', 'au', 'in', 'ae'],
                separateDialCode: true,
                initialCountry: initialCountry,
                nationalMode: false,
                autoPlaceholder: "off",
            });

            if (initialNumber) {
                const countryData = iti.getSelectedCountryData();
                const fullNumber = '+' + countryData.dialCode + initialNumber;
                iti.setNumber(fullNumber);
            }

            phoneInput.addEventListener('countrychange', function() {
                countryInput.value = iti.getSelectedCountryData().iso2;
            });
        });
    </script>
    <script>
        var id = "{{ $user->id }}";
        var baseUrl = "{{ url('/') }}";
    </script>
    <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('init/user/edit.js?v=' . time()) }}"></script>
@endpush
