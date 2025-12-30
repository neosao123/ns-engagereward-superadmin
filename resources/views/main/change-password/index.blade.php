@extends('layout.default.master', ['pageTitle' => __('index.change_password')])
@push('styles')
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
                        class="bg-200 dark__bg-1100 pe-3">{{ __('index.change_password') }}</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">

                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>

                        <li class="breadcrumb-item active" aria-current="page">{{ __('index.change_password') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="col-auto ms-2 align-items-center">
            <a href="{{ url('dashboard') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{ __('index.dashboard') }}</a>
        </div>

    </div>
    <div class="row g-3 mb-3 justify-content-center">
        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-8 col-sm-12 pe-lg-2">
            <div class="card">
                <form id="form-update-password" method="post" action="{{ url('change-password/update') }}"
                    enctype="multipart/form-data">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ __('index.change_password') }}</h5>
                    </div>
                    <div class="card-body pt-3 pb-2">
                        @csrf
                        <div class="mb-3 position-relative">
                            <label class="form-label">{{ __('index.old_password') }} : <span
                                    class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" name="old_password" id="old_password"
                                    value="{{ old('old_password') }}" placeholder="{{ __('index.enter_min_8_characters') }}">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#old_password">
                                    <i class="fas fa-eye-slash icon_attr"></i>
                                </button>
                                @error('old_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label">{{ __('index.new_password') }} : <span
                                    class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" name="new_password" id="new_password"
                                    value="{{ old('new_password') }}" placeholder="{{ __('index.enter_min_8_characters') }}">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#new_password">
                                    <i class="fas fa-eye-slash icon_attr"></i>
                                </button>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label">{{ __('index.confirm_password') }} : <span
                                    class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" name="password_confirmation"
                                    id="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="{{ __('index.enter_min_8_characters') }}">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#password_confirmation">
                                    <i class="fas fa-eye-slash icon_attr"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer float-end">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('init/profile/changepassword.js?v=' . time()) }}"></script>
@endpush
