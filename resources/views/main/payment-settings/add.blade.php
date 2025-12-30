@php
    $pageTitle = $paymentSetting ? __('index.edit_payment_setting') : __('index.add_payment_setting');
@endphp

@extends('layout.default.master')

@push('styles')
<style>
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.create')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
					@if(Auth::guard('admin')->user()->can('PaymentSetting.Create', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/payment-setting') }}" class="text-decoration-none text-dark">{{__('index.payment_setting')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.create')}}</li>
                </ol>
            </nav>
        </div>
    </div>

</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>{{ $paymentSetting ? __('index.edit') : __('index.add') }}</h5>
                </div>
                <div class="card-body">
                    <form id="form-payment-setting" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $paymentSetting->id ?? '' }}">

                        <div class="row">
                            <!-- Payment Mode -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="payment_mode">{{ __('index.payment_mode') }} <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_mode" name="payment_mode">
                                    <option value="">{{ __('index.select_payment_mode') }}</option>
                                    <option value="0" {{ (isset($paymentSetting) && $paymentSetting->payment_mode == 0) ? 'selected' : '' }}>
                                        {{ __('index.test_mode') }}
                                    </option>
                                    <option value="1" {{ (isset($paymentSetting) && $paymentSetting->payment_mode == 1) ? 'selected' : '' }}>
                                        {{ __('index.live_mode') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Payment Gateway -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="payment_gateway">{{ __('index.payment_gateway') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="payment_gateway" type="text" name="payment_gateway"
                                       value="{{ $paymentSetting->payment_gateway ?? 'stripe' }}" placeholder="{{ __('index.enter_payment_gateway') }}">
                            </div>

                            <!-- Test Secret Key -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="test_secret_key">{{ __('index.test_secret_key') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="test_secret_key" type="text" name="test_secret_key"
                                       value="{{ $paymentSetting->test_secret_key ?? '' }}" placeholder="{{ __('index.enter_test_secret_key') }}">
                            </div>

                            <!-- Test Client ID -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="test_client_id">{{ __('index.test_client_id') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="test_client_id" type="text" name="test_client_id"
                                       value="{{ $paymentSetting->test_client_id ?? '' }}" placeholder="{{ __('index.enter_test_client_id') }}">
                            </div>

                            <!-- Live Secret Key -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="live_secret_key">{{ __('index.live_secret_key') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="live_secret_key" type="text" name="live_secret_key"
                                       value="{{ $paymentSetting->live_secret_key ?? '' }}" placeholder="{{ __('index.enter_live_secret_key') }}">
                            </div>

                            <!-- Live Client ID -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="live_client_id">{{ __('index.live_client_id') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="live_client_id" type="text" name="live_client_id"
                                       value="{{ $paymentSetting->live_client_id ?? '' }}" placeholder="{{ __('index.enter_live_client_id') }}">
                            </div>

                            <!-- Webhook Secret Key (Test) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="webhook_secret_key">{{ __('index.webhook_secret_key_test') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="webhook_secret_key" type="text" name="webhook_secret_key"
                                       value="{{ $paymentSetting->webhook_secret_key ?? '' }}" placeholder="{{ __('index.enter_webhook_secret_key') }}">
                            </div>

                            <!-- Webhook Secret Key (Live) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="webhook_secret_live_key">{{ __('index.webhook_secret_live_key') }} <span class="text-danger">*</span></label>
                                <input class="form-control" id="webhook_secret_live_key" type="text" name="webhook_secret_live_key"
                                       value="{{ $paymentSetting->webhook_secret_live_key ?? '' }}" placeholder="{{ __('index.enter_webhook_secret_live_key') }}">
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-primary" type="button" id="submit-payment-setting">
                                    {{ $paymentSetting ? __('index.update') : __('index.save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var baseUrl = "{{ url('/') }}";
    var id = "{{ $paymentSetting->id ?? '' }}";
</script>
<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('init/payment-setting/payment-setting.js') }}"></script>
@endpush
