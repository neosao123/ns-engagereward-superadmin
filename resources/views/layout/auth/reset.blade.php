@extends('layout.auth.master', ['pageTitle' => __('index.reset_password')])
@push('styles')
@endpush
@section('content')
    <main class="main" id="top">
        <div class="container" data-layout="container">

            <div class="row flex-center min-vh-100 py-6">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">

                    <div class="card">
                        <div class="card-body p-4 p-sm-5">
						    <a class="d-flex flex-center mb-4" href="#">
								<img class="d-block mx-auto mb-4  d-none" src="../../../assets/img/vverify-logo.png" alt="vverify-logo" width="120">
								<span class="font-sans-serif fw-bolder fs-1 d-inline-block">{{ config('app.name') }}</span>
							</a>
                            <div class="row flex-between-center mb-2">
                                <div class="col-auto">
                                    <h5>{{ __('index.reset_password')}}</h5>
                                </div>
                            </div>
                            <form method="POST" action="{{ url('/reset-password') }}" data-parsley-validate="">
                                @csrf
                                <div class="mb-3">
                                    <label>{{ __('index.enter_email_instructions')}}</label>
                                    <input class="form-control" type="email" placeholder="Email address" name="email" id="email" required data-parsley-required-message="Email is required." />
                                </div>
                                <div>
                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit" id="submit">Reset</button>
                                </div>
                                <div class="row flex-between-center">
                                    <div class="col-auto"><a class="fs--1" href="{{ url('/login') }}">{{ __('index.back_to_login')}}.</a></div>
                                </div>

                            </form>
                            @if (session('fail'))
                            <div class="my-alert alert alert-warning alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <strong>Opps!</strong> {{ session('fail') }}
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            @endif
                            @if (session('error'))
                            <div class="my-alert alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <strong>Opps!</strong> {{ session('error') }}
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            @endif
                            @if (session('message'))
                            <div class="my-alert alert alert-info alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    {{ session('message') }}
                                </div>
                            </div>
                            @endif
                            @if (session('success'))
                            <div class="my-alert alert alert-success alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
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
</script>
@endpush
