@extends('layout.default.master', ['pageTitle' => __('index.edit_subscription')])
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
        <i class="fa-inverse fa-stack-1x text-primary fas fa-user-edit" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.edit_subscription')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Subscription.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/subscription-plan') }}" class="text-decoration-none text-dark">{{__('index.subscriptions')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.edit')}}</li>
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
    <form id="form-update-subscription" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('index.edit_subscription') }}</h5>
            </div>
           <div class="card-body">
                <div class="row g-3">
                    <!-- Title -->
                    <div class="col-md-6">
                        <label class="form-label">{{ __('index.subscription_title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="subscription_title" class="form-control" value="{{ $subscription->subscription_title }}">
                        @error('subscription_title') <span class="backend-error">{{ $message }}</span> @enderror
                    </div>



				<div class="form-group mb-3 d-none">
					<label class="form-label" for="social_media">{{ __('index.social_media') }} <span class="text-danger">*</span></label>
					<select class="form-control select2 custom-select social_media" id="social_media" name="social_media[]" style="width:100%" multiple>
						@foreach ($allSocialMediaApps as $app)
							<option value="{{ $app->id }}" {{ in_array($app->id, $selectedSocialMediaIds) ? 'selected' : '' }}>
								{{ $app->app_name }}
							</option>
						@endforeach
					</select>
				</div>


                    <!-- Months -->
                    <div class="col-md-3">
                        <label class="form-label">{{ __('index.months') }} <span class="text-danger">*</span></label>
                        <input type="number" name="subscription_months" id="subscription_months" class="form-control" value="{{ $subscription->subscription_months }}">
                        @error('subscription_months') <span class="backend-error">{{ $message }}</span> @enderror
                    </div>


					<div class="col-md-3">
					     <label for="currency" class="form-label">
							{{ __('index.currency') }} <span class="text-danger">*</span>
						</label>

						<select name="currency" class="form-control select2 custom-select" style="width:100%">
							<option value="USD ($)">USD ($)</option>

						</select>
                        @error('currency')
							<div class="text-danger mt-1">{{ $message }}</div>
						@enderror
					</div>

                    <!-- Per Month Price -->
                    <div class="col-md-3">
                        <label class="form-label">{{ __('index.per_month_price') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="subscription_per_month_price" id="subscription_per_month_price" class="form-control" value="{{ $subscription->subscription_per_month_price }}">
                        @error('subscription_per_month_price') <span class="backend-error">{{ $message }}</span> @enderror
                    </div>


					<!-- Discount Type -->
					<div class="col-md-3">
						<label class="form-label">{{ __('index.discount_type') }}</label>
						<select name="discount_type" id="discount_type" class="form-select">
							<option value="">{{ __('index.select_discount_type') }}</option>
							<option value="flat" {{ $subscription->discount_type === 'flat' ? 'selected' : '' }}>Flat</option>
							<option value="percentage" {{ $subscription->discount_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
						</select>
						@error('discount_type') <span class="backend-error">{{ $message }}</span> @enderror
					</div>

					<!-- Discount Value -->
					<div class="col-md-3">
						<label class="form-label">{{ __('index.discount_value') }} <span class="text-danger">*</span></label>
						<input type="number"  name="discount_value" id="discount_value" class="form-control" value="{{ $subscription->discount_value }}">
						@error('discount_value') <span class="backend-error">{{ $message }}</span> @enderror
					</div>

                    <!-- Total Price (readonly) -->
                    <div class="col-md-3">
                        <label class="form-label">{{ __('index.total_price') }}</label>
                        <input type="text" name="subscription_total_price" id="subscription_total_price" class="form-control" readonly value="{{ $subscription->subscription_total_price }}">
                    </div>

                    <!-- From Date -->
                    <div class="col-md-3">
                        <label class="form-label">{{ __('index.from_date') }} <span class="text-danger">*</span></label>
                        <input type="text" name="from_date" id="from_date" class="form-control flatpickr" value="{{ \Carbon\Carbon::parse($subscription->from_date)->format('d-m-Y') }}">
                        @error('from_date') <span class="backend-error">{{ $message }}</span> @enderror
                    </div>

                    <!-- To Date -->
                    <div class="col-md-3">
                        <label class="form-label">{{ __('index.to_date') }}</label>
                        <input type="text" name="to_date" id="to_date" class="form-control" readonly value="{{ \Carbon\Carbon::parse($subscription->to_date)->format('d-m-Y') }}">
                         @error('to_date')
                            <span class="backend-error">{{ $message }}</span>
                        @enderror
					</div>

                    <!-- Active Checkbox -->
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $subscription->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ __('index.active') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light text-end">
                <button class="btn btn-primary" type="button" id="subscription-update">
                    <i class="fas fa-save me-2"></i>{{ __('index.update') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('vendors/select2/select2.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr("#from_date", {
            dateFormat: "d-m-Y",
            onChange: calculateToDate
        });

        const monthsInput = document.getElementById('subscription_months');
        const priceInput = document.getElementById('subscription_per_month_price');
        const totalInput = document.getElementById('subscription_total_price');
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');

        function calculateTotal() {
            const months = parseInt(monthsInput.value);
            const price = parseFloat(priceInput.value);
            if (!isNaN(months) && !isNaN(price)) {
                totalInput.value = (months * price).toFixed(2);
            } else {
                totalInput.value = '';
            }
        }


        function calculateToDate() {
			const fromDate = fromDateInput.value;
			const months = parseInt(monthsInput.value);
			if (fromDate && months) {
				// Convert from 'd-m-Y' to Date object
				const [day, month, year] = fromDate.split('-');
				const startDate = new Date(year, month - 1, day); // JS months are 0-based

				// Add months
				startDate.setMonth(startDate.getMonth() + months);

				// Format to d-m-Y
				const formattedDay = String(startDate.getDate()).padStart(2, '0');
				const formattedMonth = String(startDate.getMonth() + 1).padStart(2, '0');
				const formattedYear = startDate.getFullYear();

				const formattedDate = `${formattedDay}-${formattedMonth}-${formattedYear}`;
				toDateInput._flatpickr.setDate(formattedDate); // Safely update flatpickr input
			} else {
				toDateInput.value = '';
			}
		}


        monthsInput.addEventListener('input', () => {
            calculateTotal();
            calculateToDate();
        });

        priceInput.addEventListener('input', calculateTotal);
    });
</script>

<script>
    const id = "{{ $subscription->id }}";
    const baseUrl = "{{ url('/') }}";
</script>

<script src="{{ asset('init/subscription-plan/edit.js?v=' . time()) }}"></script>
@endpush
