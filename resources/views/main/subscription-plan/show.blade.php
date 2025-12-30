@extends('layout.default.master', ['pageTitle' => __('index.view_subscription')])

@push('styles')
<link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .form-label {
        font-weight: 600;
    }
    input[readonly], select[disabled] {
        background-color: #f5f5f5 !important;
        cursor: not-allowed;
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.view_subscription')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Subscription.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/subscription-plan') }}" class="text-decoration-none text-dark">{{__('index.subscriptions')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Subscription.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('subscription-plan') }}" class="btn btn-outline-secondary btn-sm me-1 mb-1">
            <i class="fas fa-arrow-left me-1"></i> {{__('index.back')}}
        </a>
    </div>
    @endif
</div>
<div class="col-lg-12">
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">{{ __('index.view_subscription') }}</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <!-- Title -->
                <div class="col-md-8">
                    <label class="form-label">{{ __('index.subscription_title') }}</label>
                    <input type="text" class="form-control" value="{{ $subscription->subscription_title }}" readonly>
                </div>

				<div class="col-md-6 d-none">
					<label class="form-label">{{ __('index.social_media') }}</label>
					<input type="text" class="form-control"
						   value="{{ $allSocialMediaApps->whereIn('id', $selectedSocialMediaIds)->pluck('app_name')->implode(', ') }}"
						   readonly>
				</div>
                <!-- Months -->
                <div class="col-md-4">
                    <label class="form-label">{{ __('index.months') }}</label>
                    <input type="number" class="form-control" value="{{ $subscription->subscription_months }}" readonly>
                </div>

				<div class="col-md-4">
                    <label class="form-label">{{ __('index.currency') }}</label>
                    <input type="text" class="form-control" value="{{$subscription->currency_code }}"readonly>
                </div>

                <!-- Per Month Price -->
                <div class="col-md-4">
                    <label class="form-label">{{ __('index.per_month_price') }}</label>
                    <input type="text" class="form-control" value="{{ number_format($subscription->subscription_per_month_price, 2) }}" readonly>
                </div>


				 <div class="col-md-4">
					<label>{{ __('index.discount_type') }}</label>
					<select name="discount_type" id="discount_type" class="form-control" disabled>
						<option value="">{{ __('index.select_discount_type') }}</option>
						<option value="flat" {{ $subscription->discount_type === 'flat' ? 'selected' : '' }}>Flat</option>
						<option value="percentage" {{ $subscription->discount_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
					</select>

				</div>

				<!-- Discount Value -->
				<div class="col-md-4">
					<label>{{ __('index.discount_value') }}</label>
					<input type="number" name="discount_value" id="discount_value" class="form-control" value="{{ $subscription->discount_value }}" disabled>

				</div>

                <!-- Total Price -->
                <div class="col-md-4">
                    <label class="form-label">{{ __('index.total_price') }}</label>
                    <input type="text" class="form-control" value="{{ number_format($subscription->subscription_total_price, 2) }}" readonly>
                </div>

                <!-- From Date -->
                <div class="col-md-4">
                    <label class="form-label">{{ __('index.from_date') }}</label>
                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($subscription->from_date)->format('d-m-Y') }}" readonly>
                </div>

                <!-- To Date -->
                <div class="col-md-4">
                    <label class="form-label">{{ __('index.to_date') }}</label>
                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($subscription->to_date)->format('d-m-Y') }}" readonly>
                </div>

                <!-- Status -->
                <div class="col-md-4">
                    <label class="form-label">{{ __('index.status') }}</label>
                    <input type="text" class="form-control" value="{{ $subscription->is_active ? __('index.active') : __('index.inactive') }}" readonly>
                </div>
            </div>
        </div>


    </div>
</div>
@endsection
