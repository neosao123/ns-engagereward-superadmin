@extends('layout.default.master', ['pageTitle' => __('index.facebook_config')])
@push('styles')
@endpush
@section('content')
    <div class="d-flex mb-4 mt-1">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fab fa-facebook-f" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative">
                    <span class="bg-200 dark__bg-1100 pe-3">{{ __('settings.facebook_config') }}</span>
                    <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
                </h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                    class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                        @endif
                        <li class="breadcrumb-item">{{ __('settings.developer_app') }}</li>
                        <li class="breadcrumb-item active">{{ __('settings.facebook') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Success message --}}
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    {{-- Error messages --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-light d-flex">
                    <h5 class="mb-0">{{ __('settings.facebook_app_settings') }}</h5>
                    <a href="{{ url('configuration/settings') }}" class="ms-auto btn btn-sm btn-outline-dark">
                        <i class="far fa-edit"></i> {{ __('settings.settings') }}</a>
                </div>
                <div class="card-body row g-3">
                    <div class="mb-2">
                        <p class="mb-0">Please visit the <a href="https://developers.facebook.com" target="_blank">Facebook
                                Developer Site</a>
                            to create your application. You’ll be prompted to log in with your Facebook
                            account and provide details such as the App Name, Company Page, Website URL, Privacy Policy
                            URL,
                            Callback URL, and App Logo. Facebook may review and verify the submitted
                            information.
                            Make sure all URLs and details are accurate and valid, as incorrect or misleading
                            information
                            may lead to your app being permanently blocked.</p>
                        <p class="mb-0">
                            Make sure that all the details and URLs you enter are valid and accurate. Providing
                            incorrect or
                            misleading information may result in your app or Facebook account being permanently blocked.
                        </p>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <a class="btn btn-primary" href="{{ url('configuration/settings') }}">
                        <i class="fas fa-edit me-2"></i>{{ __('settings.change_settings') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <form id="form-app-keys" action="{{ url('config/facebook/update-keys') }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">{{ __('index.developer_app_keys') }}</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="mb-2">
                            <label for="app_id">App ID</label>
                            <div class="input-group">
                                <input type="password" id="app_id" name="app_id" class="form-control"
                                    value="{{ $config->app_id ?? '' }}" />
                                <button class="btn btn-secondary btn-sm btn-show-hidden" type="button"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Click here to view the entered APP ID">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="app_secret">App Secret</label>
                            <div class="input-group">
                                <input type="password" id="app_secret" name="app_secret"
                                    class="form-control" value="{{ $config->app_secret ?? '' }}" />
                                <button class="btn btn-secondary btn-sm btn-show-hidden" type="button"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Click here to show the entered Secret Key">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary btn-submit" data-form-id="form-app-keys" type="submit">
                            <i class="fas fa-save me-2"></i>{{ __('index.submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.btn-show-hidden').on('click', function() {
                var input = $(this).siblings('input');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).find('i').removeClass('far fa-eye').addClass('far fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    $(this).find('i').removeClass('far fa-eye-slash').addClass('far fa-eye');
                }
            });

            $('.btn-cpy').on('click', function() {
                var input = $(this).siblings('input');
                input.select();
                document.execCommand("copy");
                alert("Copied to clipboard: " + input.val());
            });
        });
    </script>
@endpush
