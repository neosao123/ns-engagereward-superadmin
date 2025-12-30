@extends('layout.auth.master', ['pageTitle' => __('index.verify')])
@push('styles')
<style>
    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    .input-group.is-invalid .form-control {
        border-color: #dc3545;
    }
</style>
@endpush
@section('content')
    <main class="main" id="top">
        <div class="container" data-layout="container">

            <div class="row flex-center min-vh-100 py-6">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">

                    <div class="card">
                        <div class="card-body p-4 p-sm-5">
                            <a class="d-flex flex-center mb-4" href="#">
                                <img class="d-block mx-auto mb-4 d-none" src="../../../assets/img/vverify-logo.png"
                                    alt="vverify-logo" width="120">
                                <span class="font-sans-serif fw-bolder fs-1">{{ config('app.name') }}</span>
                            </a>
                            <div id="altbx" class="text-center text-danger"></div>
                            <div class="row flex-between-center mb-2">
                                <div class="col-auto">
                                    <h5>{{ __('index.change_password') }}</h5>
                                </div>
                            </div>
                            <form method="POST" action="{{ url('/recovers-password') }}" data-parsley-validate="">
                                @csrf
                                <input type="hidden" name="token" value="{{ $result->reset_token }}" readonly>
                                
                                <div class="mb-3">
                                    <label class="form-label" for="password">{{ __('index.new_password') }}</label>
                                    <div class="input-group @error('password') is-invalid @enderror">
                                        <input class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               type="password" 
                                                
                                               placeholder="{{ __('index.enter_min_8_characters') }}">
                                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                            data-target="#password">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"
                                        for="password_confirmation">{{ __('index.confirm_password') }}</label>
                                    <div class="input-group @error('password_confirmation') is-invalid @enderror">
                                        <input class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               id="password_confirmation" 
                                               name="password_confirmation"
                                               type="password" 
                                              
                                               placeholder="{{ __('index.enter_min_8_characters') }}">
                                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                            data-target="#password_confirmation">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit"
                                        id="submit">Submit</button>
                                </div>
                                
                                <div class="row flex-between-center">
                                    <div class="col-auto">
                                        <a class="fs--1" href="{{ url('/login') }}">{{ __('index.back_to_login') }}</a>
                                    </div>
                                </div>

                            </form>
                            
                            @if (session('fail'))
                                <div class="my-alert alert alert-warning alert-dismissible fade show mt-3" role="alert">
                                    <div class="me-5">
                                        <strong>Oops!</strong> {{ session('fail') }}
                                        <button class="btn-close" type="button" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif
                            
                            @if (session('error'))
                                <div class="my-alert alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                    <div class="me-5">
                                        <strong>Oops!</strong> {{ session('error') }}
                                        <button class="btn-close" type="button" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif
                            
                            @if (session('message'))
                                <div class="my-alert alert alert-info alert-dismissible fade show mt-3" role="alert">
                                    <div class="me-5">
                                        <button class="btn-close" type="button" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                        {{ session('message') }}
                                    </div>
                                </div>
                            @endif
                            
                            @if (session('success'))
                                <div class="my-alert alert alert-success alert-dismissible fade show mt-3" role="alert">
                                    <div class="me-5">
                                        <button class="btn-close" type="button" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                        {{ session('success') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            'use strict';
            setTimeout(() => {
                $(".alert").remove();
            }, 5000);
        });

        $(document).on('click', '.toggle-password', function() {
            const target = $(this).data('target');
            const input = $(target);
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    </script>
    <script src="{{ asset('init/login/index.js') }}"></script>
@endpush