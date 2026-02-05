@extends('layout.auth.master', ['pageTitle' => 'Login'])
@push('styles')
@endpush
@section('content')
<main class="main" id="top">
    <div class="container-fluid">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>
        <div class="row min-vh-100 bg-100">
            <div class="col-6 d-none d-lg-block position-relative">
                <div class="bg-holder" style="background-image:url({{asset('img/generic/14.jpg')}});background-position: 50% 20%;"></div>
                <!--/.bg-holder-->
            </div>
            <div class="col-sm-10 col-md-6 px-sm-0 align-self-center mx-auto py-5">
                <div class="row justify-content-center g-0">
                    <div class="col-lg-9 col-xl-8 col-xxl-6">
                        <div class="card">
                            <div class="card-body p-4">
                                {{-- Logo --}}
								<div class="text-center mb-3">
									<img src="{{ asset('img/logo.jpg') }}" alt="Logo" style="max-width: 150px;">
								</div>
								<h3 class="text-center mb-4">Super Admin Login</h3>
                                 <form method="POST" action="{{ url('/login') }}" data-parsley-validate="">
                                   @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="split-login-email">Email address</label>
                                        <input class="form-control" id="split-login-email"id="email" type="email" name="email" required data-parsley-required-message="Email is required."  value="{{ Cookie::get('email') }}"/>
                                    </div>
									<div>
										<span class="text-danger">{{ $errors->first('email') }}</span>
									</div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="split-login-password">Password</label>
                                        </div>
                                         <div class="input-group">
                                                <input class="form-control" id="password" name="password" type="password"
                                                    required data-parsley-required-message="Password is required."
                                                    value="{{ Cookie::get('password') }}">
                                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                                    data-target="#password">
                                                    <i class="fas fa-eye-slash icon_attr"></i>
                                                </button>
                                            </div>

                                    </div>
									<div>
										<span class="text-danger">{{ $errors->first('password') }}</span>
									</div>
                                    <div class="row flex-between-center">
                                        <div class="col-auto">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="rememberme" name="rememberme" @if (Cookie::get('email')) checked @endif/>
                                                <label class="form-check-label mb-0" for="split-checkbox">Remember me</label>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <a class="fs-10" href="{{url('forgot-password')}}">Forgot Password?</a>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Log in</button>
                                    </div>
                                </form>
								 @if (session('fail'))
								  <div class="alert alert-warning alert-dismissible fade show" role="alert">
									<div class="me-5">
										{{ session('fail') }}
										<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>
								  </div>
								@endif

								@if (session('error'))
								  <div class="alert alert-danger alert-dismissible fade show" role="alert">
									<div class="me-5">
										{{ session('error') }}
										<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>
								  </div>
								@endif

								@if (session('message'))
								  <div class="alert alert-info alert-dismissible fade show" role="alert">
									<div class="me-5">
										{{ session('message') }}
										<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>
								  </div>
								@endif

								@if (session('success'))
								  <div class="alert alert-success alert-dismissible fade show" role="alert">
									<div class="me-5">
										{{ session('success') }}
										<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>
								  </div>
								@endif

                            </div>
                        </div>
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


              $(document).on('click', '.toggle-password', function() {
                const target = $(this).data('target');
                const input = $(target);
                const svg = $(this).find('svg');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    // Change eye-slash to eye
                    svg.attr('data-icon', 'eye');
                    svg.removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.attr('type', 'password');
                    // Change eye to eye-slash
                    svg.attr('data-icon', 'eye-slash');
                    svg.removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });

	});
</script>
@endpush
