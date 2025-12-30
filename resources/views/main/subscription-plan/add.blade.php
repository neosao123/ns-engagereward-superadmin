@extends('layout.default.master', ['pageTitle' => __('index.add_subscription')])
@push('styles')
<link href="{{ asset('vendors/select2/select2.min.css') }}" rel="stylesheet" />
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

    .is-invalid ~ .iti__flag-container {
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.add')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Subscription.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/subscription-plan') }}" class="text-decoration-none text-dark">{{__('index.subscriptions')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.add')}}</li>
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
     <form id="form-add-subscription" method="POST" enctype="multipart/form-data">
	  <div class="card mb-3">
            @csrf
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('index.add_subscription') }}</h5>
            </div>
            <div class="card-body">

                <div class="row g-3">
                 <div class="col-md-6">
                    <label>{{ __('index.subscription_title') }} <span class="text-danger">*</span></label>
                    <input type="text" name="subscription_title" class="form-control" value="{{ old('subscription_title') }}">
                    @error('subscription_title')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

				<div class="form-group mb-3 d-none">
				   	<label class="form-label" for="social_media">{{ __('index.social_media') }} <span class="text-danger">*</span></label>
					<select class="form-control select2 custom-select social_media" id="social_media" name="social_media[]" style="width:100%" multiple>

					</select>
				</div>

					<div class="col-md-3">
                        <label>{{ __('index.months') }} <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="subscription_months" id="subscription_months" class="form-control" value="{{ old('subscription_months') }}">
                        @error('subscription_months')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

					<div class="col-md-3">
					     <label for="currency" class="form-label">
							{{ __('index.currency') }} <span class="text-danger">*</span>
						</label>

						<select name="currency"  class="form-control select2" style="width:100%">
							<option value="USD ($)">USD ($)</option>
						</select>
                        @error('currency')
							<div class="text-danger mt-1">{{ $message }}</div>
						@enderror
					</div>

                    <div class="col-md-3">
						<label for="subscription_per_month_price" class="form-label">
							{{ __('index.per_month_price') }} <span class="text-danger">*</span>
						</label>
                         <input type="number" step="0.01" name="subscription_per_month_price" id="subscription_per_month_price" class="form-control" placeholder="0.00" value="{{ old('subscription_per_month_price') }}">


						@error('subscription_per_month_price')
							<div class="text-danger mt-1">{{ $message }}</div>
						@enderror
					</div>


                    <div class="col-md-3">
						<label>{{ __('index.discount_type') }} <span class="text-danger">*</span></label>
						<select name="discount_type" id="discount_type" class="form-select">
							<option value="">-- Select --</option>
							<option value="flat">Flat</option>
							<option value="percentage">Percentage</option>
						</select>
						  @error('discount_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
					</div>




                    <!-- Discount Value -->
					<div class="col-md-3">
						<label>{{ __('index.discount_value') }} <span class="text-danger">*</span></label>
						<input type="number" name="discount_value" id="discount_value" class="form-control" min="0" step="0.01">
					    @error('discount_value')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
					</div>
					<div class="col-md-3">
                        <label>{{ __('index.total_price') }}</label>
                        <input type="text" name="subscription_total_price" id="subscription_total_price" class="form-control" readonly>
                    </div>

                    <div class="col-md-3">
                        <label>{{ __('index.from_date') }} <span class="text-danger">*</span></label>
                        <input type="text" name="from_date" id="from_date" class="form-control flatpickr" autocomplete="off">
                        @error('from_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label>{{ __('index.to_date') }}</label>
                        <input type="text" name="to_date" id="to_date" class="form-control">
                         @error('to_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
					</div>
                 </div>

            </div>
            <div class="card-footer bg-light text-end">
                <button class="btn btn-primary" type="submit" id="submit">
                    <i class="fas fa-save me-2"></i>{{ __('index.save') }}
                </button>
                <button class="btn btn-secondary" type="button" onclick="window.location.reload();">
                    <i class="fas fa-undo me-2"></i>{{ __('index.reset') }}
                </button>
            </div>

         </div>
	 </form>
</div>
@endsection
@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    var baseUrl = "{{ url('/') }}"
  </script>
<script src="{{ asset('vendors/select2/select2.min.js') }}"></script>

<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('init/subscription-plan/add.js?v=' . time()) }}"></script>
@endpush
